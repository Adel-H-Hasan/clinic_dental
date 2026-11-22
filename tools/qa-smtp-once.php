<?php
// Explicitly authorized test recipient arrives on stdin; no credentials are printed.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dir = __DIR__.'/../output/qa-local';
$previous = is_file($dir.'/smtp-attempt.json') ? json_decode(file_get_contents($dir.'/smtp-attempt.json'), true) : null;
if ($previous && ($previous['accepted'] || !in_array('--retry-failed', $argv, true))) { fwrite(STDERR, "تم تنفيذ محاولة SMTP مسبقاً؛ لن تتكرر تلقائياً.\n"); exit(1); }
$recipient = trim(stream_get_contents(STDIN));
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) exit(2);
$runtime = json_decode(file_get_contents($dir.'/runtime.json'), true, 512, JSON_THROW_ON_ERROR);
if (!preg_match('/^taqweem_qa_[a-f0-9]{12}$/', $runtime['database'])) exit(3);
config(['app.url'=>'http://127.0.0.1:8199','app.key'=>$runtime['key'],
    'database.default'=>'mysql','database.connections.mysql.database'=>$runtime['database'],
    'database.connections.mysql.host'=>'127.0.0.1','database.connections.mysql.port'=>3306,
    'database.connections.mysql.username'=>'root','database.connections.mysql.password'=>'',
    'database.connections.mysql.url'=>null,'cache.default'=>'array','session.driver'=>'array']);
Illuminate\Support\Facades\DB::purge('mysql');
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8199');
$user = $previous ? App\Models\User::findOrFail($previous['user_id']) : App\Models\User::factory()->unverified()->create(['email'=>$recipient,'name'=>'اختبار التحقق المصرح به','role'=>'patient']);
file_put_contents($dir.'/smtp-attempt.json', json_encode(['user_id'=>$user->id,'attempted'=>true,'accepted'=>false]));
try {
    event(new Illuminate\Auth\Events\Registered($user));
    file_put_contents($dir.'/smtp-attempt.json', json_encode(['user_id'=>$user->id,'attempted'=>true,'accepted'=>true]));
    echo "قبل ناقل SMTP رسالة التحقق. تأكيد وصولها إلى الوارد يحتاج فحص المستلم.\n";
} catch (Throwable $exception) {
    echo 'فشل الإرسال؛ نوع الخطأ فقط: '.get_class($exception).PHP_EOL;
    $message = strtolower($exception->getMessage());
    echo json_encode(['network_restricted'=>str_contains($message, 'permission') || str_contains($message, 'forbidden') || str_contains($message, 'access permissions'),
        'connection_failed'=>str_contains($message, 'connection') || str_contains($message, 'connect'),
        'authentication_failed'=>str_contains($message, 'authenticate') || str_contains($message, 'credentials'),
        'tls_failed'=>str_contains($message, 'certificate') || str_contains($message, 'crypto')], JSON_PRETTY_PRINT).PHP_EOL;
    exit(1);
}
