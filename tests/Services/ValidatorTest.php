<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    #[DataProvider('requiredCases')]
    public function testRequired(?string $value, bool $expected): void
    {
        $this->assertSame($expected, Validator::required($value));
    }

    public static function requiredCases(): array
    {
        return [
            'null' => [null, false],
            'empty string' => ['', false],
            'whitespace only' => ["  \n\t", false],
            'value' => ['Tandlab', true],
        ];
    }

    #[DataProvider('emailCases')]
    public function testEmail(?string $value, bool $expected): void
    {
        $this->assertSame($expected, Validator::email($value));
    }

    public static function emailCases(): array
    {
        return [
            'valid' => ['info@tandlab.nl', true],
            'missing at' => ['infotandlab.nl', false],
            'null' => [null, false],
            'empty' => ['', false],
        ];
    }

    public function testMaxLength(): void
    {
        $this->assertTrue(Validator::maxLength('abcde', 5));
        $this->assertFalse(Validator::maxLength('abcdef', 5));
        $this->assertTrue(Validator::maxLength(null, 0));
    }

    public function testMaxLengthUsesMultibyteLength(): void
    {
        $this->assertTrue(Validator::maxLength('café', 4));
    }

    public function testCleanTrimsValue(): void
    {
        $this->assertSame('Tandlab', Validator::clean("  Tandlab\n"));
        $this->assertSame('', Validator::clean(null));
    }
}
