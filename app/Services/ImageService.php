<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ImageService
{
    private const MAX_BYTES = 50 * 1024 * 1024;
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function maxUploadBytes(): int
    {
        $limits = [self::MAX_BYTES];
        $uploadLimit = ini_parse_quantity((string) ini_get('upload_max_filesize'));
        $postLimit = ini_parse_quantity((string) ini_get('post_max_size'));
        if ($uploadLimit > 0) {
            $limits[] = $uploadLimit;
        }
        if ($postLimit > 0) {
            $limits[] = max(0, $postLimit - 64 * 1024);
        }
        return min($limits);
    }

    public static function uploadLimitMessage(): string
    {
        $megabytes = floor(self::maxUploadBytes() / 1024 / 1024 * 10) / 10;
        return 'Bestand is te groot. Upload een afbeelding van maximaal '
            . str_replace('.', ',', (string) $megabytes) . ' MB.';
    }

    public static function storeUpload(array $file, string $subdir = '', int $maxWidth = 1600): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_OK;
        if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            throw new RuntimeException(self::uploadLimitMessage());
        }
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Geen bestand geselecteerd.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Uploaden is mislukt. Probeer het opnieuw.');
        }
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Ongeldige upload.');
        }
        if (($file['size'] ?? 0) > self::maxUploadBytes()) {
            throw new RuntimeException(self::uploadLimitMessage());
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
