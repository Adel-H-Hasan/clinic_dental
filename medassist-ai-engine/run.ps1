param([switch]$Diagnostics)
$ErrorActionPreference='Stop'
$originalToken=$env:MEDASSIST_AI_TOKEN
Push-Location $PSScriptRoot
try {
    if ($Diagnostics) {
        # Health/validation service only; generation remains disabled.
        $env:MEDASSIST_AI_TOKEN=''
    } elseif (!$env:MEDASSIST_AI_TOKEN) {
        $root=Split-Path $PSScriptRoot -Parent
        $line=Get-Content (Join-Path $root '.env') | Where-Object { $_ -match '^MEDASSIST_AI_TOKEN=' } | Select-Object -Last 1
        if ($line) { $env:MEDASSIST_AI_TOKEN=($line -split '=',2)[1].Trim().Trim('"',"'") }
    }
    if (!$Diagnostics -and (!$env:MEDASSIST_AI_TOKEN -or $env:MEDASSIST_AI_TOKEN -like 'REPLACE_*')) {
        throw 'AI token is unavailable. Supply it securely in the process environment, or use -Diagnostics. No environment file was changed.'
    }
    if (!(Test-Path '.venv\Scripts\python.exe')) { throw 'Local Python environment is missing; see README.md.' }
    & '.venv\Scripts\python.exe' -B -m uvicorn app.main:app --host 127.0.0.1 --port 8001 --no-access-log
} finally {
    $env:MEDASSIST_AI_TOKEN=$originalToken
    Pop-Location
}
