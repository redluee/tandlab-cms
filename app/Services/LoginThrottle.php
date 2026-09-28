<?php

declare(strict_types=1);

namespace App\Services;

use App\Db\Database;

class LoginThrottle
{
    public const MAX_ATTEMPTS = 5;
    public const WINDOW_SECONDS = 900;

    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    public static function isBlocked(string $ip, ?int $now = null): bool
    {
        return self::failures($ip, $now ?? time()) >= self::MAX_ATTEMPTS;
    }

    public static function recordFailure(string $ip, ?int $now = null): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)');
        $stmt->execute([$ip, $now ?? time()]);
        $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')
            ->execute([($now ?? time()) - self::WINDOW_SECONDS]);
    }

    public static function clear(string $ip): void
    {
        $pdo = Database::connect(config_get()['db']);
        $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
    }

    public static function minutesLeft(string $ip, ?int $now = null): int
    {
        $now ??= time();
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT MIN(attempted_at) FROM login_attempts WHERE ip = ? AND attempted_at > ?');
        $stmt->execute([$ip, $now - self::WINDOW_SECONDS]);
        $oldest = (int) $stmt->fetchColumn();
        return max(1, (int) ceil(($oldest + self::WINDOW_SECONDS - $now) / 60));
    }

    private static function failures(string $ip, int $now): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?');
        $stmt->execute([$ip, $now - self::WINDOW_SECONDS]);
        return (int) $stmt->fetchColumn();
    }
}
