<?php
// Test harness only. Never reads or writes the team's configured database.
$runtime = json_decode(file_get_contents(__DIR__.'/../output/qa-local/runtime.json'), true, 512, JSON_THROW_ON_ERROR);
if (!preg_match('/^taqweem_qa_[a-f0-9]{12}$/', $runtime['database'])) throw new RuntimeException('Unsafe QA database name');
$settings = ['APP_ENV'=>'local','APP_DEBUG'=>'false','APP_KEY'=>$runtime['key'],'APP_URL'=>'http://127.0.0.1:8199','DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_DATABASE'=>$runtime['database'],'DB_USERNAME'=>'root','DB_PASSWORD'=>'','DB_URL'=>'','SESSION_DRIVER'=>'file','CACHE_STORE'=>'array','MAIL_MAILER'=>'log','QUEUE_CONNECTION'=>'sync','LOG_CHANNEL'=>'single'];
foreach($settings as $key=>$value){putenv($key.'='.$value);$_ENV[$key]=$value;$_SERVER[$key]=$value;}
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database'=>$runtime['database'],'database.connections.mysql.username'=>'root','database.connections.mysql.password'=>'','database.connections.mysql.url'=>null,'session.files'=>__DIR__.'/../output/qa-local/sessions','logging.channels.single.path'=>__DIR__.'/../output/qa-local/application.log','services.medassist_ai.token'=>null,'services.gemini.key'=>null]);
return $app;
