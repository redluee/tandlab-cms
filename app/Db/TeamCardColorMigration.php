<?php

declare(strict_types=1);

namespace App\Db;

use App\Services\TeamCardColor;
use PDO;

class TeamCardColorMigration
{
    public static function up(PDO $pdo): void
    {
        $columns = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
            ? $pdo->query('SHOW COLUMNS FROM team_members')->fetchAll(PDO::FETCH_COLUMN)
            : array_column($pdo->query('PRAGMA table_info(team_members)')->fetchAll(PDO::FETCH_ASSOC), 'name');

        if (!in_array('card_color', $columns, true)) {
            $pdo->exec('ALTER TABLE team_members ADD COLUMN card_color VARCHAR(5)');
        }

        $ids = $pdo->query('SELECT id FROM team_members WHERE card_color IS NULL')->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare('UPDATE team_members SET card_color = ? WHERE id = ? AND card_color IS NULL');
        foreach ($ids as $id) {
            $stmt->execute([TeamCardColor::random(), $id]);
        }
    }
}
