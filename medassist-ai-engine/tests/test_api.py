import io
import os
import sys
import unittest
from contextlib import nullcontext
from types import SimpleNamespace
from unittest.mock import patch

from fastapi.testclient import TestClient
from fastapi import HTTPException
from PIL import Image, ImageDraw
from app import main


class ApiTests(unittest.TestCase):
    def setUp(self):
        self.client = TestClient(main.app)
        self.environment = patch.dict(os.environ, {"MEDASSIST_AI_TOKEN": "synthetic-test-token"})
        self.environment.start()
        self.addCleanup(self.environment.stop)

    def photo(self, size=(160, 100)):
        data = io.BytesIO()
        Image.new("RGB", size, (180, 180, 180)).save(data, "PNG")
        return data.getvalue()

    def post(self, data=None, token="synthetic-test-token"):
        return self.client.post("/api/v1/simulate", headers={"X-AI-Token": token},
                                files={"file": ("synthetic.png", data or self.photo(), "image/png")},
                                data={"mode": "mouth", "treatment": "whitening", "strength": "natural"})

    def test_health_does_not_claim_loaded_model(self):
        with patch.object(main, "pipeline") as pipeline:
            pipeline.cache_info.return_value = SimpleNamespace(currsize=0)
            response = self.client.get("/health")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["status"], "degraded")
        self.assertFalse(response.json()["model_loaded"])

    def test_token_rejection_and_missing_configuration(self):
        self.assertEqual(self.post(token="invalid").status_code, 401)
        with patch.dict(os.environ, {"MEDASSIST_AI_TOKEN": ""}):
            self.assertEqual(self.post().status_code, 503)

    def test_invalid_images_and_size_limits(self):
        self.assertEqual(self.post(b"invalid").status_code, 422)
        self.assertEqual(self.post(self.photo((10, 10))).status_code, 422)
        with patch.object(main, "MAX_BYTES", 16):
            self.assertEqual(self.post().status_code, 413)

    def test_missing_detector_does_not_disclose_internal_details(self):
        with patch.object(main, "mouth_mask", side_effect=RuntimeError("SYNTHETIC_SECRET_PATH")):
            response = self.post()
        self.assertEqual(response.status_code, 503)
        self.assertNotIn("SYNTHETIC_SECRET_PATH", response.text)

    def test_no_face_is_a_validation_error(self):
        with patch.object(main, "mouth_mask", side_effect=HTTPException(422, "No face detected")):
            self.assertEqual(self.post().status_code, 422)

    def test_busy_model_rejects_without_blocking_health(self):
        main.MODEL_LOCK.acquire()
        try:
            self.assertEqual(self.post().status_code, 503)
            self.assertEqual(self.client.get("/health").status_code, 200)
        finally:
            main.MODEL_LOCK.release()

    def test_mock_pipeline_preserves_unmasked_pixels_and_returns_png(self):
        def mask(image):
            result = Image.new("L", image.size, 0)
            ImageDraw.Draw(result).rectangle((50, 30, 110, 70), fill=255)
            return result

        def pipe(**kwargs):
            return SimpleNamespace(images=[Image.new("RGB", (512, 512), (255, 255, 255))])

        with patch.object(main, "mouth_mask", mask), patch.object(main, "pipeline", return_value=(pipe, "cpu")), \
                patch.dict(sys.modules, {"torch": SimpleNamespace(inference_mode=lambda: nullcontext())}):
            response = self.post()
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.headers["content-type"], "image/png")
        self.assertEqual(response.headers["cache-control"], "no-store")
        result = Image.open(io.BytesIO(response.content))
        self.assertEqual(result.size, (160, 100))
        self.assertEqual(result.getpixel((0, 0)), (180, 180, 180))
        self.assertEqual(result.getpixel((80, 50)), (255, 255, 255))


if __name__ == "__main__":
    unittest.main()
