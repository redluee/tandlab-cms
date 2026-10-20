<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\TeamCardColor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TeamCardColorTest extends TestCase
{
    public function testAllowedColors(): void
    {
        foreach (['white', 'grey', 'green'] as $color) {
            $this->assertSame($color, TeamCardColor::validate($color));
        }
        $this->assertContains(TeamCardColor::random(), TeamCardColor::COLORS);
    }

    #[DataProvider('invalidColors')]
    public function testRejectsInvalidColors(mixed $value): void
    {
        $this->expectException(RuntimeException::class);
        TeamCardColor::validate($value);
    }

    public static function invalidColors(): array
    {
        return [
            ['red'], ['#78c39c'], ['WHITE'], [''], [null], [1], [true], [['green']],
            ['green" onclick="alert(1)'],
        ];
    }
}
