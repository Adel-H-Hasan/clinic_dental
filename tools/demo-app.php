<?php
// Isolated, loopback-only demo runtime. Never loaded by public/index.php.
$runtimePath=__DIR__.'/../output/demo-local/runtime.json';
if(!is_file($runtimePath))throw new RuntimeException('Run tools/demo-setup.php first.');
$runtime=json_decode(file_get_contents($runtimePath),true,512,JSON_THROW_ON_ERROR);
if(!preg_match('/^taqweem_demo_[a-f0-9]{12}$/',$runtime['database']))throw new RuntimeException('Unsafe demo database');
$settings=['APP_ENV'=>'local','APP_DEBUG'=>'false','APP_KEY'=>$runtime['key'],'APP_URL'=>'http://127.0.0.1:8200',
 'APP_CONFIG_CACHE'=>__DIR__.'/../output/demo-local/config.disabled.php',
 'DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_DATABASE'=>$runtime['database'],
 'DB_USERNAME'=>'root','DB_PASSWORD'=>'','DB_URL'=>'','SESSION_DRIVER'=>'file','CACHE_STORE'=>'file',
 'MAIL_MAILER'=>'array','QUEUE_CONNECTION'=>'sync','LOG_CHANNEL'=>'single'];
foreach($settings as $key=>$value){putenv($key.'='.$value);$_ENV[$key]=$value;$_SERVER[$key]=$value;}
require_once __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['app.locale'=>'ar','database.default'=>'mysql','database.connections.mysql.database'=>$runtime['database'],
 'database.connections.mysql.host'=>'127.0.0.1','database.connections.mysql.port'=>3306,
 'database.connections.mysql.username'=>'root','database.connections.mysql.password'=>'','database.connections.mysql.url'=>null,
 'session.files'=>__DIR__.'/../output/demo-local/sessions','cache.stores.file.path'=>__DIR__.'/../output/demo-local/cache',
 'logging.channels.single.path'=>__DIR__.'/../output/demo-local/application.log',
 'mail.default'=>'array','services.medassist_ai.token'=>null,'services.gemini.key'=>null,'services.gemini.demo_review'=>false]);
app()->setLocale('ar');
// Explicitly authorized bypass for newly created fake accounts in this database only.
app('events')->listen(Illuminate\Auth\Events\Registered::class,function($event)use($runtime){
 if($event->user->getConnection()->getDatabaseName()!==$runtime['database'])throw new RuntimeException('Demo boundary violation');
 $event->user->markEmailAsVerified();
});
Illuminate\Support\Facades\Route::get('/email/verify',function(){
 return redirect()->route('login')->with('status',app()->getLocale()==='ar'
  ? 'حساب الاختبار جاهز. يمكنك تسجيل الدخول دون تحقق بريدي.'
  : 'Your demo account is ready. Sign in without email verification.');
})->name('verification.notice');
$app['router']->getRoutes()->refreshNameLookups();
return $app;
