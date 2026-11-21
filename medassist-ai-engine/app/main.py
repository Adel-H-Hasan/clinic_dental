
"""MedAssist SDXL inpainting API, adapted from supplied dental-smile-ai notebook.
Not a clinically validated dental outcome predictor.
"""

import io
import os
import hmac
import threading

from functools import lru_cache
from typing import Literal

import numpy as np

from PIL import Image, ImageOps, UnidentifiedImageError

from fastapi import (
    FastAPI,
    UploadFile,
    File,
    Form,
    Header,
    HTTPException,
    Depends,
)

from fastapi.responses import Response


app = FastAPI(
    title="MedAssist SDXL Smile API",
    version="1.0",
    docs_url=None,
    redoc_url=None,
)

MAX_BYTES = 8 * 1024 * 1024

MODEL_ID = os.getenv(
    "MEDASSIST_MODEL_ID",
    "diffusers/stable-diffusion-xl-1.0-inpainting-0.1",
)

MODEL_LOCK = threading.Lock()


TREATMENTS = {
    "whitening": (
        "naturally whiter teeth, realistic dental whitening, "
        "preserve original tooth geometry"
    ),
    "veneers": (
        "realistic porcelain veneers, natural tooth proportions, "
        "cosmetic dental veneers"
    ),
    "straightening": (
        "evenly aligned natural teeth, subtle orthodontic correction"
    ),
    "comprehensive": (
        "natural white straight teeth, balanced attractive smile, "
        "cosmetic dentistry"
    ),
}


LIPS = [
    61, 185, 40, 39, 37, 0,
    267, 269, 270, 409, 291,
    375, 321, 405, 314, 17,
    84, 181, 91, 146,
]

# Inner lip boundary: mouth opening, not outer lips.
INNER_LIPS = [
    78, 95, 88, 178, 87, 14,
    317, 402, 318, 324, 308,
    415, 310, 311, 312, 13,
    82, 81, 80, 191,
]


# ==========================================================
# Authentication
# ==========================================================

def authorize(x_ai_token: str | None = Header(default=None)):
    expected = os.getenv("MEDASSIST_AI_TOKEN", "")

    if not expected or expected.startswith("REPLACE_"):
        raise HTTPException(
            status_code=503,
            detail="Set MEDASSIST_AI_TOKEN in Python environment",
        )

    if not x_ai_token or not hmac.compare_digest(
        expected,
        x_ai_token,
    ):
        raise HTTPException(
            status_code=401,
            detail="Invalid AI token",
        )


# ==========================================================
# MediaPipe Face Detector
# ==========================================================

@lru_cache(maxsize=1)
def detector():
    import mediapipe as mp

    from mediapipe.tasks import python
    from mediapipe.tasks.python import vision

    path = os.getenv(
        "MEDASSIST_FACE_MODEL",
        os.path.join(
            os.path.dirname(__file__),
            "face_landmarker.task",
        ),
    )

    if not os.path.isfile(path):
        raise RuntimeError(
            "Missing face_landmarker.task; "
            "download the MediaPipe model as described in README"
        )

    opts = vision.FaceLandmarkerOptions(
        base_options=python.BaseOptions(
            model_asset_path=path,
        ),
        num_faces=1,
    )

    return vision.FaceLandmarker.create_from_options(opts)


# ==========================================================
# SDXL FP16 Pipeline
# ==========================================================

@lru_cache(maxsize=1)
def pipeline():
    import torch

    from diffusers import StableDiffusionXLInpaintPipeline

    device = (
        "cuda"
        if torch.cuda.is_available()
        else "cpu"
    )

    dtype = (
        torch.float16
        if device == "cuda"
        else torch.float32
    )

    # Load the already downloaded model.
    # FP16 is selected on CUDA.
    # No new downloads are allowed.
    pipe = StableDiffusionXLInpaintPipeline.from_pretrained(
        MODEL_ID,
        torch_dtype=dtype,
        variant="fp16" if device == "cuda" else None,
        use_safetensors=True,
        local_files_only=True,
    )

    # Reduce GPU memory usage.
    if device == "cuda":
        pipe.enable_model_cpu_offload()
    else:
        pipe.to("cpu")

    return pipe, device


# ==========================================================
# Mouth Mask
# ==========================================================


def mouth_mask(image):
    import mediapipe as mp

    from PIL import ImageDraw, ImageFilter

    image_array = np.ascontiguousarray(
        np.array(image)
    )

    result = detector().detect(
        mp.Image(
            image_format=mp.ImageFormat.SRGB,
            data=image_array,
        )
    )

    if not result.face_landmarks:
        raise HTTPException(
            status_code=422,
            detail=(
                "No face detected. "
                "Use a clear frontal smile photo."
            ),
        )

    landmarks = result.face_landmarks[0]
    w, h = image.size

    pts = [
        (
            max(0, min(w - 1, round(landmarks[i].x * w))),
            max(0, min(h - 1, round(landmarks[i].y * h))),
        )
        for i in INNER_LIPS
    ]

    mask = Image.new("L", (w, h), 0)

    ImageDraw.Draw(mask).polygon(
        pts,
        fill=255,
    )

    # Slightly soften the inner boundary without
    # extending the mask across the outer lips.
    blur_radius = max(1, round(min(w, h) * 0.001))

    mask = mask.filter(
        ImageFilter.GaussianBlur(radius=blur_radius)
    )

    return mask



# ==========================================================
# Image Validation
# ==========================================================

async def read_image(file):
    raw = await file.read(MAX_BYTES + 1)

    if len(raw) > MAX_BYTES:
        raise HTTPException(
            status_code=413,
            detail="Image too large",
        )

    try:
        with Image.open(io.BytesIO(raw)) as im:

            if im.format not in (
                "JPEG",
                "PNG",
                "WEBP",
            ):
                raise ValueError(
                    "Unsupported image type"
                )

            im = ImageOps.exif_transpose(im)

            if (
                im.width < 160
                or im.height < 100
                or im.width * im.height > 16_000_000
            ):
                raise ValueError(
                    "Unsupported image dimensions"
                )

            return im.convert("RGB")

    except (
        OSError,
        ValueError,
        UnidentifiedImageError,
        Image.DecompressionBombError,
    ) as exc:

        raise HTTPException(
            status_code=422,
            detail="Invalid image",
        ) from exc


# ==========================================================
# Health Endpoint
# ==========================================================

@app.get("/health")
def health():
    return {
        "status": "ok",
        "engine": "sdxl-inpainting",
        "model_loaded": (
            pipeline.cache_info().currsize > 0
        ),
    }


# ==========================================================
# Capabilities Endpoint
# ==========================================================

@app.get("/capabilities")
def capabilities():
    return {
        "engine": "sdxl-inpainting",
        "treatments": list(TREATMENTS),
        "clinical_accuracy": "not validated",
        "model": MODEL_ID,
    }


# ==========================================================
# AI Smile Simulation Endpoint
# ==========================================================

@app.post(
    "/api/v1/simulate",
    dependencies=[Depends(authorize)],
)
async def simulate(
    file: UploadFile = File(...),

    mode: Literal[
        "face",
        "mouth",
    ] = Form("face"),

    treatment: Literal[
        "whitening",
        "veneers",
        "straightening",
        "comprehensive",
    ] = Form("whitening"),

    strength: Literal[
        "natural",
        "moderate",
        "bright",
    ] = Form("moderate"),
):

    image = await read_image(file)

    try:
        mask = mouth_mask(image)

    except RuntimeError as exc:
        raise HTTPException(
            status_code=503,
            detail=str(exc),
        ) from exc

    # Resize for SDXL inference.
    image_small = image.resize(
        (512, 512),
        Image.Resampling.LANCZOS,
    )

    mask_small = mask.resize(
        (512, 512),
        Image.Resampling.LANCZOS,
    )

    try:
        with MODEL_LOCK:

            pipe, device = pipeline()

            import torch

            with torch.inference_mode():

                result = pipe(
                    prompt=(
                        TREATMENTS[treatment]
                        + ", photorealistic teeth, "
                        "preserve face identity, "
                        "natural lighting"
                    ),

                    negative_prompt=(
                        "deformed face, "
                        "extra teeth, "
                        "missing teeth, "
                        "uncanny, "
                        "cartoon, "
                        "distorted gums, "
                        "blurry"
                    ),

                    image=image_small,

                    mask_image=mask_small,

                    strength={
                        "natural": 0.60,
                        "moderate": 0.75,
                        "bright": 0.90,
                    }[strength],

                    guidance_scale=7.5,

                    num_inference_steps=int(
                        os.getenv(
                            "MEDASSIST_INFERENCE_STEPS",
                            "20",
                        )
                    ),

                ).images[0]

    except Exception as exc:

        # Do not expose internal errors to clients.
        raise HTTPException(
            status_code=503,
            detail=(
                "Model unavailable or generation failed; "
                "check Python logs and GPU memory"
            ),
        ) from exc

    # Restore original image dimensions.
    result = result.resize(
        image.size,
        Image.Resampling.LANCZOS,
    )

    # Preserve pixels outside the mouth mask.
    composed = Image.composite(
        result,
        image,
        mask,
    )

    out = io.BytesIO()

    composed.save(
        out,
        format="PNG",
    )

    return Response(
        content=out.getvalue(),
        media_type="image/png",
        headers={
            "Cache-Control": "no-store",
            "X-Model-Version": "sdxl-inpainting",
            "X-Content-Type-Options": "nosniff",
        },
    )
