<?php

declare(strict_types=1);

namespace App\Services;

class SecurityHeaders
{
    /** @return array<string, string> */
    public static function headers(bool $https): array
    {
        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];
        if ($https) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }
        return $headers;
    }

    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        foreach (self::headers($https) as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
