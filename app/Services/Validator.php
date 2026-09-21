<?php

declare(strict_types=1);

namespace App\Services;

class Validator
{
    public static function required(?string $value): bool
    {
        return trim((string) $value) !== '';
    }

    public static function email(?string $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function maxLength(?string $value, int $max): bool
    {
        return mb_strlen((string) $value) <= $max;
    }

    public static function clean(?string $value): string
    {
        return trim((string) $value);
    }
}
