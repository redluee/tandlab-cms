<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;

class ErrorHandler
{
    private static bool $debug = false;
    private static string $logFile = '';

    public static function register(bool $debug, string $logDir): void
    {
        self::$debug = $debug;
        self::$logFile = rtrim($logDir, '/') . '/php-error.log';

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        if (is_dir($logDir) || @mkdir($logDir, 0775, true)) {
            ini_set('error_log', self::$logFile);
        }

        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(Throwable $e): void
    {
        error_log(sprintf(
            "Uncaught %s: %s in %s:%d\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        self::render($e);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }
        self::render(null);
    }

    private static function render(?Throwable $e): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(500);
            $wantsJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
                || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
            if ($wantsJson) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Er is een serverfout opgetreden.']);
                return;
            }
            header('Content-Type: text/html; charset=UTF-8');
        }

        $debugDetail = self::$debug && $e !== null ? $e::class . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() : null;
        require __DIR__ . '/../views/public/500.php';
    }
}
