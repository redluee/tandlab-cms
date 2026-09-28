<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Db\Database;

$config = config_get();
$root = $config['app']['root'];
$pdo = Database::connect($config['db']);

$keep = 14;
$backupDir = getenv('BACKUP_DIR') ?: $root . '/storage/backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0770, true)) {
    fwrite(STDERR, "Kan {$backupDir} niet aanmaken.\n");
    exit(1);
}

$stamp = date('Ymd-His');
$dbFile = "{$backupDir}/database-{$stamp}." . (Database::driver() === 'mysql' ? 'sql' : 'sqlite');

if (Database::driver() === 'mysql') {
    $db = $config['db'];
    $cmd = sprintf(
        'mysqldump --single-transaction --host=%s --user=%s %s > %s',
        escapeshellarg($db['host']),
        escapeshellarg($db['user']),
        escapeshellarg($db['name']),
        escapeshellarg($dbFile)
    );
    putenv('MYSQL_PWD=' . $db['pass']);
    passthru($cmd, $status);
    if ($status !== 0) {
        fwrite(STDERR, "mysqldump mislukt.\n");
        exit(1);
    }
} else {
    $pdo->exec('VACUUM INTO ' . $pdo->quote($dbFile));
}

$archive = "{$backupDir}/bestanden-{$stamp}.tar";
$tar = new PharData($archive);
foreach (['uploads', 'privacy'] as $dir) {
    if (is_dir("{$root}/storage/{$dir}")) {
        $tar->buildFromDirectory("{$root}/storage", '#^' . preg_quote("{$root}/storage/{$dir}", '#') . '#');
    }
}
$tar->compress(Phar::GZ);
unset($tar);
unlink($archive);

foreach (['database-*', 'bestanden-*'] as $pattern) {
    $files = glob("{$backupDir}/{$pattern}") ?: [];
    rsort($files);
    foreach (array_slice($files, $keep) as $old) {
        unlink($old);
    }
}

echo "Back-up klaar in {$backupDir} ({$stamp}).\n";
