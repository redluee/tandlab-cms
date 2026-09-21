<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Database;

class Setting
{
    public static function all(): array
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->query('SELECT setting_key, value FROM site_settings');
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['value'];
        }
        return $settings;
    }

    public static function get(string $key, string $default = ''): string
    {
        $pdo = Database::connect(config_get()['db']);
        $stmt = $pdo->prepare('SELECT value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public static function set(string $key, string $value): void
    {
        $pdo = Database::connect(config_get()['db']);
        if (Database::driver() === 'mysql') {
            $stmt = $pdo->prepare(
                'INSERT INTO site_settings (setting_key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
            );
            $stmt->execute([$key, $value]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO site_settings (setting_key, value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET value = excluded.value'
            );
            $stmt->execute([$key, $value]);
        }
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set($key, (string) $value);
        }
    }
}
