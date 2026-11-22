<?php
/**
 * Back up and optionally delete ONLY the isolated QA/Demo MySQL databases
 * created by older Codex tools. Never touches the real `taqweem` database.
 *
 * Dry run: php tools/cleanup-local-test-databases.php
 * Execute: php tools/cleanup-local-test-databases.php --execute --confirm=DELETE-OLD-TAQWEEM-TEST-DBS
 */
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (!app()->environment('local') || config('database.default') !== 'mysql') {
    fwrite(STDERR, "Refusing to run outside a local MySQL environment.\n");
    exit(1);
}
$config = config('database.connections.mysql');
if (($config['database'] ?? null) !== 'taqweem') {
    fwrite(STDERR, "Refusing to run unless active database is exactly taqweem.\n");
    exit(1);
}
$host = (string) ($config['host'] ?? '127.0.0.1');
$port = (int) ($config['port'] ?? 3306);
$username = (string) ($config['username'] ?? 'root');
$password = (string) ($config['password'] ?? '');
$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$all = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
$targets = array_values(array_filter($all, fn ($db) => preg_match('/^taqweem_(?:qa|demo)_[a-f0-9]{12}$/D', (string) $db)));
sort($targets);
if (!$targets) {
    echo "No old QA/Demo databases match the restricted naming pattern. Nothing to delete.\n";
    exit(0);
}
echo "Existing old QA/Demo database(s):\n";
foreach ($targets as $name) echo "  - {$name}\n";
echo "The active taqweem database is never included.\n";

$execute = in_array('--execute', $argv, true);
$confirmed = in_array('--confirm=DELETE-OLD-TAQWEEM-TEST-DBS', $argv, true);
if (!$execute || !$confirmed) {
    echo "DRY RUN: no databases changed. To back up and remove exactly these,\n";
    echo "run with --execute --confirm=DELETE-OLD-TAQWEEM-TEST-DBS\n";
    exit(0);
}

$binary = getenv('MYSQLDUMP_BIN') ?: (PHP_OS_FAMILY === 'Windows'
    ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe' : 'mysqldump');
$stamp = date('Ymd-His');
$backups = __DIR__.'/../storage/app/taqweem-local-db-backups/'.$stamp;
if (!is_dir($backups) && !mkdir($backups, 0700, true) && !is_dir($backups)) {
    throw new RuntimeException('Cannot create private backup directory.');
}

// Credential file, not --password on the process command line. Deleted in finally.
$clientFile = tempnam(sys_get_temp_dir(), 'tq_mysql_');
if (!$clientFile) throw new RuntimeException('Cannot create temporary MySQL client file.');
$quote = static fn (string $value): string => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '', ''], $value).'"';
try {
    file_put_contents($clientFile, "[client]\n".
        'user='.$quote($username)."\n".
        'password='.$quote($password)."\n".
        'host='.$quote($host)."\n".
        'port='.$port."\n");
    @chmod($clientFile, 0600);
    foreach ($targets as $db) {
        $backup = $backups.'/'.$db.'.sql';
        $process = new Process([
            $binary,
            '--defaults-extra-file='.$clientFile,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--hex-blob',
            '--result-file='.$backup,
            '--databases',
            $db,
        ]);
        $process->setTimeout(180);
        $process->run();
        if (!$process->isSuccessful() || !is_file($backup) || filesize($backup) < 256) {
            // Never delete a database without a verified, nonempty dump.
            throw new RuntimeException('Backup failed for '.$db.'. No deletion attempted for that database.');
        }
        $pdo->exec('DROP DATABASE `'.$db.'`');
        echo "Backed up and deleted {$db}. Private backup: {$backup}\n";
    }
} finally {
    @unlink($clientFile);
}
echo "Completed. Original taqweem database and environment files untouched.\n";
