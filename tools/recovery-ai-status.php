<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$url=(string)config('services.medassist_ai.url');
$host=parse_url($url,PHP_URL_HOST);
$status=['gemini_key_present'=>(bool)config('services.gemini.key'),
 'gemini_enabled'=>(bool)config('services.gemini.demo_review'),
 'smile_token_present'=>(bool)config('services.medassist_ai.token'),
 'smile_url_configured'=>(bool)$url,'smile_url_local'=>in_array($host,['127.0.0.1','localhost','::1']),
 'gd_loaded'=>extension_loaded('gd'),'python_health'=>'not_checked'];
if($status['smile_url_local']){
 try{$response=Illuminate\Support\Facades\Http::connectTimeout(2)->timeout(3)->get(rtrim($url,'/').'/health');
 $status['python_health']=$response->status();$status['model_loaded']=$response->json('model_loaded')===true;}
 catch(Throwable $e){$status['python_health']='connection_failed';}
}
echo json_encode($status,JSON_PRETTY_PRINT).PHP_EOL;
