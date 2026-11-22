<?php
$app=require __DIR__.'/qa-app.php';
$prior=json_decode(file_get_contents(__DIR__.'/../output/qa-local/smtp-attempt.json'),true);
$user=App\Models\User::findOrFail($prior['user_id']);
echo json_encode(['authorized_test_account_verified'=>$user->hasVerifiedEmail()]).PHP_EOL;
