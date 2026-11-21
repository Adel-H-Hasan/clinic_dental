# SDXL engine (from supplied dental-smile-ai notebook)

This replaces the heuristic OpenCV whitening engine with SDXL inpainting + MediaPipe mouth landmarks.
The upstream Jupyter notebook and its MIT license are preserved under `upstream/`.

## Windows 11

Use a supported Python version such as **3.11 or 3.12** for PyTorch/MediaPipe compatibility (Python 3.15 may not work).

1. In `clinic_dental/medassist-ai-engine`: `py -3.11 -m venv .venv`
2. ` .\.venv\Scripts\python.exe -m pip install -r requirements.txt`
3. Download MediaPipe model from `https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task` and save as `app/face_landmarker.task`.
4. Set **the same** random `MEDASSIST_AI_TOKEN` in Laravel `.env`; `run.ps1` reads it and exports it for Python.
5. `powershell -ExecutionPolicy Bypass -File .\run.ps1`
6. Test `http://127.0.0.1:8001/health`.

On first generation, Hugging Face downloads several GB of SDXL model weights; internet and sufficient disk/RAM/VRAM are required. CPU-only generation can be extremely slow and may exhaust RAM. NVIDIA GPU strongly recommended. Weights are NOT bundled. If the model repository requires access or acceptance, configure Hugging Face access separately.

The model changes a masked mouth region and does not provide medically accurate predictions; tooth geometry and identity preservation are not guaranteed.
