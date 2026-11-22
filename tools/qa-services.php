<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$url=parse_url((string)config('services.medassist_ai.url'));$host=$url['host']??'';$port=$url['port']??80;
$reachable=false;if(in_array($host,['127.0.0.1','localhost','::1'],true)){$s=@fsockopen($host,$port,$errno,$errstr,2);if($s){$reachable=true;fclose($s);}}
echo json_encode(['smile_token_configured'=>(bool)config('services.medassist_ai.token'),'smile_local_service_reachable'=>$reachable,'gemini_key_configured'=>(bool)config('services.gemini.key'),'gemini_review_enabled'=>(bool)config('services.gemini.demo_review')],JSON_PRETTY_PRINT).PHP_EOL;
