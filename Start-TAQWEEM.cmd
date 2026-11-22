@echo off
setlocal
cd /d "%~dp0"
if not exist ".env" (
  echo ERROR: Your existing .env was not found. Do NOT replace it with a template.
  pause
  exit /b 1
)
echo Starting TAQWEEM using the configured database. No migrations or seeders will run automatically.
echo Local website: http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause
