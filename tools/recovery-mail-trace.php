<?php
// One authorized diagnostic message. Persist only counters/booleans, never message content.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dir=__DIR__.'/../output/qa-local';
$plain=in_array('--plain',$argv,true);
$marker=$dir.($plain?'/recovery-mail-plain.json':'/recovery-mail-trace.json');
if(is_file($marker)) { echo "التجربة مسجلة مسبقاً؛ لن يُعاد الإرسال.\n"; exit(1); }
$runtime=json_decode(file_get_contents($dir.'/runtime.json'),true);
$prior=json_decode(file_get_contents($dir.'/smtp-attempt.json'),true);
if(!preg_match('/^taqweem_qa_[a-f0-9]{12}$/',$runtime['database']))exit(2);
config(['app.url'=>'http://127.0.0.1:8199','app.key'=>$runtime['key'],'database.default'=>'mysql',
 'database.connections.mysql.database'=>$runtime['database'],'database.connections.mysql.host'=>'127.0.0.1',
 'database.connections.mysql.port'=>3306,'database.connections.mysql.username'=>'root',
 'database.connections.mysql.password'=>'','database.connections.mysql.url'=>null]);
Illuminate\Support\Facades\DB::purge('mysql');
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8199');
$user=App\Models\User::findOrFail($prior['user_id']);
if($plain){
 Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(fn($recipient,$url)=>(new Illuminate\Notifications\Messages\MailMessage())
  ->subject('Verify Email Address')->view(['text'=>'mail.verification-plain'],['url'=>$url]));
}
$trace=['registered'=>0,'notification_sending'=>0,'mail_sending'=>0,'mail_sent'=>0,'notification_sent'=>0,
 'recipient_matches'=>false,'smtp_accepted'=>false,'unverified'=>!$user->hasVerifiedEmail(),
 'mailer'=>config('mail.default'),'queued'=>false];
app('events')->listen(Illuminate\Auth\Events\Registered::class,function()use(&$trace){$trace['registered']++;});
app('events')->listen(Illuminate\Notifications\Events\NotificationSending::class,function($e)use(&$trace){if($e->channel==='mail')$trace['notification_sending']++;});
app('events')->listen(Illuminate\Mail\Events\MessageSending::class,function($e)use(&$trace,$user){
 $trace['mail_sending']++;
 $to=$e->message->getTo();$trace['recipient_matches']=count($to)===1&&hash_equals($user->email,$to[0]->getAddress());
 if(!$trace['recipient_matches'])return false;
 $trace['has_html']=$e->message->getHtmlBody()!==null;
 $trace['has_text']=$e->message->getTextBody()!==null;
 $trace['subject_present']=(bool)$e->message->getSubject();
});
app('events')->listen(Illuminate\Mail\Events\MessageSent::class,function($e)use(&$trace){
 $trace['mail_sent']++;
 $debug=$e->sent->getSymfonySentMessage()->getDebug();
 $trace['smtp_accepted']=(bool)preg_match('/250[ -]2\.0\.0\s+OK/i',$debug);
 $trace['message_id_present']=(bool)$e->sent->getMessageId();
});
app('events')->listen(Illuminate\Notifications\Events\NotificationSent::class,function($e)use(&$trace){if($e->channel==='mail')$trace['notification_sent']++;});
file_put_contents($marker,json_encode(['attempt_started'=>true]));
try {event(new Illuminate\Auth\Events\Registered($user));}
catch(Throwable $e){$trace['exception_type']=get_class($e);}
file_put_contents($marker,json_encode($trace,JSON_PRETTY_PRINT));
echo json_encode($trace,JSON_PRETTY_PRINT).PHP_EOL;
