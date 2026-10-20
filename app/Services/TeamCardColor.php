<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class TeamCardColor
{
    public const COLORS = ['white', 'grey', 'green'];

    public static function random(): string
    {
        return self::COLORS[random_int(0, count(self::COLORS) - 1)];
    }

    public static function validate(mixed $value): string
    {
        if (!in_array($value, self::COLORS, true)) {
            throw new RuntimeException('Kies wit, grijs of groen als kaartkleur.');
        }
        return $value;
    }
}
