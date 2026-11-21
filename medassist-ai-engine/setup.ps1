
$ErrorActionPreference = "Stop"

Set-Location $PSScriptRoot

Write-Host "MedAssist AI - Environment Setup" -ForegroundColor Cyan

# Verify Python 3.11
py -3.11 --version

if ($LASTEXITCODE -ne 0) {
    throw "Python 3.11 is required."
}

# Create virtual environment
if (-not (Test-Path ".venv\Scripts\python.exe")) {
    py -3.11 -m venv .venv
}

$python = ".\.venv\Scripts\python.exe"

# Upgrade pip
& $python -m pip install --upgrade pip

if ($LASTEXITCODE -ne 0) {
    throw "pip upgrade failed."
}

# Install dependencies
& $python -m pip install -r requirements.txt --extra-index-url https://download.pytorch.org/whl/cu121

if ($LASTEXITCODE -ne 0) {
    throw "Dependency installation failed."
}

Write-Host "MedAssist AI setup completed!" -ForegroundColor Green
