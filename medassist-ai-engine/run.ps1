$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
$root=Split-Path $PSScriptRoot -Parent
$line=Get-Content (Join-Path $root '.env') | Where-Object { $_ -match '^MEDASSIST_AI_TOKEN=' } | Select-Object -Last 1
if (-not $line) { throw 'Missing MEDASSIST_AI_TOKEN in Laravel .env' }
$env:MEDASSIST_AI_TOKEN=($line -split '=',2)[1].Trim().Trim('"',"'")
if ($env:MEDASSIST_AI_TOKEN -like 'REPLACE_*' -or !$env:MEDASSIST_AI_TOKEN) { throw 'Set a real random token in Laravel .env first' }
if (Test-Path '.venv\Scripts\python.exe') { & '.venv\Scripts\python.exe' -m uvicorn app.main:app --host 127.0.0.1 --port 8001 }
else { py -m uvicorn app.main:app --host 127.0.0.1 --port 8001 }
