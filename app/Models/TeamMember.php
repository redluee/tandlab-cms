<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class TeamMember
{
    public static function allActive(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM team_members WHERE active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function allForAdmin(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT * FROM team_members ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT * FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare(
            'INSERT INTO team_members (name, role, bio, photo_path, sort_order, active) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['role'] ?? null,
            $data['bio'] ?? null,
            $data['photo_path'] ?? null,
            $data['sort_order'] ?? self::nextSortOrder(),
            $data['active'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connect(config_get()['db']);
        $fields = ['name = ?', 'role = ?', 'bio = ?', 'active = ?'];
        $params = [$data['name'], $data['role'] ?? null, $data['bio'] ?? null, $data['active'] ?? 1];

        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $params[] = $data['sort_order'];
        }

        if (!empty($data['photo_path'])) {
            $fields[] = 'photo_path = ?';
            $params[] = $data['photo_path'];
        }

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE team_members SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('DELETE FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function nextSortOrder(): int
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM team_members');
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param int[] $orderedIds Team member IDs in the desired display order.
     */
    public static function reorder(array $orderedIds): void
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('UPDATE team_members SET sort_order = ? WHERE id = ?');

        foreach (array_values($orderedIds) as $position => $id) {
            $stmt->execute([$position, (int) $id]);
        }
    }

    private const WHITELISTED_FIELDS = ['name', 'role', 'bio', 'photo_path', 'active'];

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
        $stmt = $pdo->prepare('UPDATE team_members SET ' . implode(', ', $set) . ' WHERE id = ?');
        $stmt->execute($params);
    }
}
