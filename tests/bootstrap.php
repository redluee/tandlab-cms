<?php

declare(strict_types=1);

// Laadt alleen de autoloader en de globale helpers uit app/bootstrap.php,
// zonder sessie te starten of de database te verbinden — tests bepalen zelf
// welke staat (sessie, filesystem) ze nodig hebben.

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('rich')) {
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
}

if (!function_exists('config_get')) {
    function config_get(): array
    {
        static $config;
        if ($config === null) {
            $config = [
                'uploads' => [
                    'path' => sys_get_temp_dir() . '/tandlab-test-uploads',
                    'url' => '/uploads',
                ],
            ];
        }
        return $config;
    }
}
