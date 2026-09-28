<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class PrivacyStatement
{
    public const PUBLIC_URL = '/assets/docs/privacystatement.pdf';
    private const MAX_BYTES = 10 * 1024 * 1024;

    public static function currentPath(?string $root = null): string
    {
        return ($root ?? dirname(__DIR__, 2)) . '/public/assets/docs/privacystatement.pdf';
    }

    public static function previousPath(?string $root = null): string
    {
        return ($root ?? dirname(__DIR__, 2)) . '/storage/privacy/privacystatement-vorige.pdf';
    }

    public static function pathFor(string $version, ?string $root = null): ?string
    {
        $path = match ($version) {
            'huidig' => self::currentPath($root),
            'vorige' => self::previousPath($root),
            default => null,
        };

        return $path !== null && is_file($path) ? $path : null;
    }

    /** @param array{tmp_name?: string, error?: int, size?: int} $upload */
    public static function replace(array $upload, ?string $root = null): void
    {
        $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Geen bestand geselecteerd.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Uploaden mislukt.');
        }

        $tmp = (string) ($upload['tmp_name'] ?? '');
        if (($upload['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Het PDF-bestand mag maximaal 10 MB zijn.');
        }
        if (!self::isPdf($tmp)) {
            throw new RuntimeException('Alleen PDF-bestanden zijn toegestaan.');
        }

        $current = self::currentPath($root);
        $previous = self::previousPath($root);
        self::ensureDir(dirname($current));
        self::ensureDir(dirname($previous));

        $staged = $current . '.new';
        if (!move_uploaded_file($tmp, $staged) && !(PHP_SAPI === 'cli' && copy($tmp, $staged))) {
            throw new RuntimeException('Opslaan van het bestand is mislukt.');
        }

        if (is_file($current)) {
            rename($current, $previous);
        }
        rename($staged, $current);
    }

    public static function restorePrevious(?string $root = null): void
    {
        $current = self::currentPath($root);
        $previous = self::previousPath($root);
        if (!is_file($previous)) {
            throw new RuntimeException('Er is geen vorige versie beschikbaar.');
        }

        $swap = $current . '.swap';
        if (is_file($current)) {
            rename($current, $swap);
        }
        rename($previous, $current);
        if (is_file($swap)) {
            rename($swap, $previous);
        }
    }

    private static function isPdf(string $path): bool
    {
        $handle = $path !== '' && is_file($path) ? fopen($path, 'rb') : false;
        if ($handle === false) {
            return false;
        }
        $head = fread($handle, 5);
        fclose($handle);

        return $head === '%PDF-';
    }

    private static function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Map kon niet worden aangemaakt.');
        }
    }
}
