<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config = require __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

\App\Db\Database::connect($config['db']);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function config_get(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

function edit(string $ref, string $type = 'text'): string
{
    if (!\App\Services\EditMode::on()) {
        return '';
    }
    return 'data-edit="' . e($ref) . '" data-edit-type="' . e($type) . '"';
}

function rich(?string $value, string $type = 'richtext'): string
{
    $value = (string) $value;
    if ($value === '') {
        return '';
    }
    if (!str_contains($value, '<')) {
        return nl2br(e($value));
    }
    return \App\Services\HtmlSanitizer::clean($value, $type);
}

return $config;
