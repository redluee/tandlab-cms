<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class Tandwerk
{
    public static function allActive(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM tandwerk WHERE active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function allForAdmin(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM tandwerk ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM tandwerk WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare(
            'INSERT INTO tandwerk (title, body, image_path, alt, sort_order, active) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'],
            $data['body'],
            $data['image_path'] ?? null,
            $data['alt'] ?? null,
            $data['sort_order'] ?? 0,
            $data['active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connect(config_get()['db']);
        $fields = ['title = ?', 'body = ?', 'alt = ?', 'sort_order = ?', 'active = ?'];
        $params = [$data['title'], $data['body'], $data['alt'] ?? null, $data['sort_order'] ?? 0, $data['active'] ?? 1];

        if (!empty($data['image_path'])) {
            $fields[] = 'image_path = ?';
            $params[] = $data['image_path'];
        }

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE tandwerk SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function findByImagePath(string $imagePath): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM tandwerk WHERE image_path = ?');
        $stmt->execute([$imagePath]);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM tandwerk WHERE id = ?');
        $stmt->execute([$id]);
    }
}
