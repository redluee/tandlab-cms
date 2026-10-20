<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$address = $argv[1] ?? 'localhost:8000';
$command = [
    PHP_BINARY,
    '-d',
    'upload_max_filesize=50M',
    '-d',
    'post_max_size=60M',
    '-S',
    $address,
    '-t',
    $root . '/public',
    $root . '/public/router.php',
];

passthru(implode(' ', array_map('escapeshellarg', $command)), $status);
exit($status);
