<?php
// Read configuration only: no database connection, notification, or SMTP request.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$mailer = config('mail.default');
echo json_encode([
    'mailer' => $mailer,
    'transport' => config("mail.mailers.$mailer.transport"),
    'smtp_host_configured' => (bool) config('mail.mailers.smtp.host'),
    'smtp_username_configured' => (bool) config('mail.mailers.smtp.username'),
    'smtp_password_configured' => (bool) config('mail.mailers.smtp.password'),
    'smtp_url_override_present' => (bool) config('mail.mailers.smtp.url'),
    'from_address_configured' => (bool) config('mail.from.address'),
    'configuration_cached' => $app->configurationIsCached(),
    'rate_limit_cache_store' => config('cache.default'),
    'registered_listener_count' => count(app('events')->getListeners(Illuminate\Auth\Events\Registered::class)),
    'verification_is_queued' => is_subclass_of(Illuminate\Auth\Notifications\VerifyEmail::class, Illuminate\Contracts\Queue\ShouldQueue::class),
], JSON_PRETTY_PRINT).PHP_EOL;
