<?php

declare(strict_types=1);

namespace App\Services;

use App\Db\Database;
use PDO;

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $username;

        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /admin/login');
            exit;
        }
    }
}
