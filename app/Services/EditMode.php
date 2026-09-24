<?php

declare(strict_types=1);

namespace App\Services;

class EditMode
{
    private static bool $on = false;

    public static function enable(): void
    {
        self::$on = true;
    }

    public static function on(): bool
    {
        return self::$on;
    }
}
