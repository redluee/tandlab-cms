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

    private const WHITELISTED_FIELDS = ['title', 'body', 'image_path', 'alt', 'active'];

    public static function updateFields(int $id, array $fields): void
    {
        $set = [];
        $params = [];
        foreach (self::WHITELISTED_FIELDS as $field) {
            if (array_key_exists($field, $fields)) {
                $set[] = $field . ' = ?';
                $params[] = $fields[$field];
            }
        }
        if (empty($set)) {
            return;
        }
        $params[] = $id;
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('UPDATE tandwerk SET ' . implode(', ', $set) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function nextSortOrder(): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM tandwerk');
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param int[] $orderedIds Tandwerk IDs in the desired display order.
     */
    public static function reorder(array $orderedIds): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('UPDATE tandwerk SET sort_order = ? WHERE id = ?');

        foreach (array_values($orderedIds) as $position => $id) {
            $stmt->execute([$position, (int) $id]);
        }
    }
}
