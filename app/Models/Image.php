<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class Image
{
    public static function create(string $filename, string $displayName): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare(
            'INSERT INTO images (filename, display_name) VALUES (?, ?)'
        );
        $stmt->execute([$filename, $displayName]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM images WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findByFilename(string $filename): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM images WHERE filename = ?');
        $stmt->execute([$filename]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM images ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, list<string>> bestandsnaam => pagina's (home|tand|team)
     */
    public static function pageUsage(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $usage = [];
        $add = static function (?string $filename, string $page) use (&$usage): void {
            if ($filename === null || $filename === '') {
                return;
            }
            if (!in_array($page, $usage[$filename] ?? [], true)) {
                $usage[$filename][] = $page;
            }
        };

        $settings = $pdo->query(
            "SELECT setting_key, value FROM site_settings WHERE setting_key IN ('hero_slides', 'hero_slide_1', 'hero_slide_2')"
        )->fetchAll();
        $byKey = array_column($settings, 'value', 'setting_key');
        $slides = json_decode($byKey['hero_slides'] ?? '[]', true);
        if (!is_array($slides) || empty($slides)) {
            $slides = [$byKey['hero_slide_1'] ?? '', $byKey['hero_slide_2'] ?? ''];
        }
        foreach ($slides as $slide) {
            $add(is_string($slide) ? $slide : null, 'home');
        }

        foreach ($pdo->query('SELECT image_path FROM tandwerk')->fetchAll() as $row) {
            $add($row['image_path'], 'tand');
        }
        foreach ($pdo->query('SELECT photo_path FROM team_members')->fetchAll() as $row) {
            $add($row['photo_path'], 'team');
        }

        return $usage;
    }

    public static function isProtected(string $filename): bool
    {
        return in_array($filename, ['logo.webp', 'bghero.webp'], true) || str_starts_with($filename, 'branding/');
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connect(config_get()['db']);
        $updates = [];
        $params = [];

        if (isset($data['display_name'])) {
            $updates[] = 'display_name = ?';
            $params[] = $data['display_name'];
        }

        if (empty($updates)) {
            return;
        }

        $params[] = $id;
        $sql = 'UPDATE images SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM images WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function deleteByFilename(string $filename): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM images WHERE filename = ?');
        $stmt->execute([$filename]);
    }
}
