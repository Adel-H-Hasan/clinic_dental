$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$archivePath = Join-Path $root 'output/TAQWEEM_Laravel_Review_2026-11-21.zip'
if (Test-Path -LiteralPath $archivePath) { throw 'Archive already exists; choose a new version rather than overwrite it.' }
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$roots = @('app','config','routes','resources','lang','database/factories','database/migrations','database/seeders','tests','tools','docs','public/css','public/js','public/images','public/fonts','public/taqweem-landing','public/taqweem-integration','public/build','medassist-ai-engine/app')
$files = @()
foreach ($relative in $roots) { $files += Get-ChildItem -LiteralPath (Join-Path $root $relative) -File -Recurse }
foreach ($relative in @('artisan','composer.json','composer.lock','package.json','package-lock.json','phpunit.xml','vite.config.js','README.md','CHANGELOG.md','.gitignore','.gitattributes','.editorconfig','bootstrap/app.php','bootstrap/providers.php','public/index.php','public/.htaccess','public/favicon.ico','public/robots.txt','medassist-ai-engine/README.md','medassist-ai-engine/requirements.txt','medassist-ai-engine/run.ps1','medassist-ai-engine/upstream/LICENSE')) { $p=Join-Path $root $relative; if(Test-Path -LiteralPath $p){$files+=Get-Item -LiteralPath $p} }
# Only explicitly selected, synthetic QA evidence; never runtime, sessions, logs or image uploads.
$evidence=@('navigation-results.json','browser-results-en.json','browser-results-ar.json','cinematic-results.json','landing-integrity.json','responsive-matrix.json','final-navigation-phpunit.txt','final-navigation-phpunit-direct.txt','landing-unit-tests.txt','syntax-check.txt','error-locale-results.json','service-availability.json')
foreach($name in $evidence){$files+=Get-Item -LiteralPath (Join-Path $root "output/qa-evidence/$name")}
$files+=Get-ChildItem -LiteralPath (Join-Path $root 'output/qa-evidence') -Filter 'navigation-*.png' -File
$manifest=@()
$zip=[IO.Compression.ZipFile]::Open($archivePath,[IO.Compression.ZipArchiveMode]::Create)
try {
 foreach($file in ($files|Sort-Object FullName -Unique)) {
  $relative=[IO.Path]::GetRelativePath($root,$file.FullName).Replace('\','/')
  if($relative -match '(^|/)(\.env[^/]*|\.git|vendor|node_modules|__pycache__|\.venv)(/|$)|\.(log|sqlite|sql|pyc|ipynb|pem|key)$'){continue}
  if($file.Attributes -band [IO.FileAttributes]::ReparsePoint){throw "Unexpected link: $relative"}
  if($file.Extension -in @('.php','.py','.js','.mjs','.json','.md','.txt','.ps1')){
   $content=[IO.File]::ReadAllText($file.FullName)
   if($content -match '(hf_[A-Za-z0-9]{25,}|sk-[A-Za-z0-9_-]{30,}|AIza[A-Za-z0-9_-]{30,}|-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----)'){throw "Review potential secret in $relative"}
  }
  [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip,$file.FullName,'clinic_dental/'+$relative,[IO.Compression.CompressionLevel]::Optimal)|Out-Null
  $manifest+=[pscustomobject]@{path=$relative;sha256=(Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash}
 }
 $example=@'
APP_NAME=TAQWEEM
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taqweem_review
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
LOG_CHANNEL=single
MEDASSIST_AI_URL=http://127.0.0.1:8001
MEDASSIST_AI_TOKEN=
GEMINI_API_KEY=
'@
 $entry=$zip.CreateEntry('clinic_dental/.env.example');$writer=[IO.StreamWriter]::new($entry.Open());$writer.Write($example);$writer.Dispose()
 foreach($folder in @('bootstrap/cache','storage/app/private','storage/app/public','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs')){$entry=$zip.CreateEntry("clinic_dental/$folder/.gitignore");$writer=[IO.StreamWriter]::new($entry.Open());$writer.Write("*`n!.gitignore`n");$writer.Dispose()}
 $entry=$zip.CreateEntry('clinic_dental/docs/taqweem/ARCHIVE_MANIFEST.json');$writer=[IO.StreamWriter]::new($entry.Open());$writer.Write(($manifest|ConvertTo-Json -Depth 4));$writer.Dispose()
} finally {$zip.Dispose()}
$verify=[IO.Compression.ZipFile]::OpenRead($archivePath)
try{foreach($item in $manifest){$entry=$verify.GetEntry('clinic_dental/'+$item.path);$stream=$entry.Open();$hash=[Security.Cryptography.SHA256]::Create();try{$actual=[Convert]::ToHexString($hash.ComputeHash($stream));if($actual -ne $item.sha256){throw "Archive mismatch: $($item.path)"}}finally{$stream.Dispose();$hash.Dispose()}}
 Write-Output ('Verified files: '+$manifest.Count)
}finally{$verify.Dispose()}
Get-FileHash -LiteralPath $archivePath -Algorithm SHA256 | Format-List
