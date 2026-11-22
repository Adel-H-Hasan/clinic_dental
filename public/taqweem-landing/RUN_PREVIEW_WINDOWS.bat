@echo off
setlocal
cd /d "%~dp0"
set "PHP_PATH=C:\xampp\php\php.exe"
if exist "%PHP_PATH%" goto serve
where php >nul 2>nul
if errorlevel 1 (
  echo PHP not found. Edit PHP_PATH in this .bat to point to your local php.exe.
  pause
  exit /b 1
)
set "PHP_PATH=php"
:serve
start "" "http://127.0.0.1:8000/"
echo.
echo TAQWEEM demo starting on http://127.0.0.1:8000/
echo Keep this window open while browsing. Ctrl+C to stop.
echo.
"%PHP_PATH%" -S 127.0.0.1:8000 router.php
pause
