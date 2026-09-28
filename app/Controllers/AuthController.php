<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth;
use App\Services\Csrf;
use App\Services\LoginThrottle;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            exit;
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../views/admin/login.php';
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            $_SESSION['login_error'] = 'Ongeldig verzoek, probeer opnieuw.';
            header('Location: /admin/login');
            exit;
        }

        $ip = LoginThrottle::ip();
        if (LoginThrottle::isBlocked($ip)) {
            $_SESSION['login_error'] = 'Te veel mislukte pogingen. Probeer het over ' . LoginThrottle::minutesLeft($ip) . ' minuten opnieuw.';
            header('Location: /admin/login');
            exit;
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($username, $password)) {
            LoginThrottle::clear($ip);
            header('Location: /admin');
            exit;
        }

        LoginThrottle::recordFailure($ip);
        $_SESSION['login_error'] = 'Onjuiste gebruikersnaam of wachtwoord.';
        header('Location: /admin/login');
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            echo 'Ongeldig verzoek (CSRF).';
            exit;
        }
        Auth::logout();
        header('Location: /admin/login');
        exit;
    }
}
