<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ImageService
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function storeUpload(array $file, string $subdir = '', int $maxWidth = 1600): string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Ongeldige upload.');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Bestand is te groot.');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Bestandstype niet toegestaan.');
        }

        return self::processFile($file['tmp_name'], $mime, $maxWidth);
    }

    public static function storeFromPath(string $path, string $subdir = '', int $maxWidth = 1600): string
    {
        $mime = mime_content_type($path);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Bestandstype niet toegestaan: ' . $path);
        }

        return self::processFile($path, $mime, $maxWidth);
    }

    private static function processFile(string $sourcePath, string $mime, int $maxWidth): string
    {
        $image = self::createFromMime($sourcePath, $mime);
        if ($image === false) {
            throw new RuntimeException('Afbeelding kon niet worden gelezen.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        $config = config_get();
        $uploadsRoot = $config['uploads']['path'];
        if (!is_dir($uploadsRoot)) {
            mkdir($uploadsRoot, 0775, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.webp';
        $destination = $uploadsRoot . '/' . $filename;

        imagewebp($image, $destination, 82);

        return $filename;
    }

    /** @return \GdImage|false */
    private static function createFromMime(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif' => imagecreatefromgif($path),
            default => false,
        };
    }
}
