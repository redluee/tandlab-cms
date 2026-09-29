<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$ini = [];
$iniPath = $root . '/config/config.ini';
if (is_file($iniPath)) {
    $ini = parse_ini_file($iniPath) ?: [];
}

if (!function_exists('cfg_env')) {
    function cfg_env(array $ini, string $key, ?string $default = null): ?string
    {
        $env = getenv($key);
        if ($env !== false && $env !== '') {
            return $env;
        }
        if (isset($ini[$key]) && $ini[$key] !== '') {
            return (string) $ini[$key];
        }
        return $default;
    }
}

$driver = cfg_env($ini, 'DB_DRIVER', 'sqlite');

$uploadsPath = cfg_env($ini, 'UPLOADS_PATH', 'storage/uploads');
if ($uploadsPath[0] !== '/') {
    $uploadsPath = $root . '/' . $uploadsPath;
}

return [
    'app' => [
        'root' => $root,
        'debug' => cfg_env($ini, 'APP_DEBUG', '0') === '1',
    ],
    'db' => [
        'driver' => $driver,
        'sqlite_path' => cfg_env($ini, 'DB_SQLITE_PATH', $root . '/storage/database.sqlite'),
        'host' => cfg_env($ini, 'DB_HOST', 'localhost'),
        'name' => cfg_env($ini, 'DB_NAME', 'tandlab'),
        'user' => cfg_env($ini, 'DB_USER', 'root'),
        'pass' => cfg_env($ini, 'DB_PASS', ''),
        'charset' => cfg_env($ini, 'DB_CHARSET', 'utf8mb4'),
    ],
    'uploads' => [
        'path' => $uploadsPath,
        'url' => '/uploads',
    ],
];
