<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\Csrf;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    #[Before]
    protected function startSession(): void
    {
        $_SESSION = [];
    }

    #[After]
    protected function clearSession(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsGeneratedAndStable(): void
    {
        $token = Csrf::token();

        $this->assertNotSame('', $token);
        $this->assertSame($token, Csrf::token());
    }

    public function testFieldContainsEscapedToken(): void
    {
        $field = Csrf::field();

        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString(Csrf::token(), $field);
    }

    public function testValidateAcceptsMatchingToken(): void
    {
        $token = Csrf::token();

        $this->assertTrue(Csrf::validate($token));
    }

    public function testValidateRejectsWrongToken(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::validate('onjuiste-waarde'));
    }

    public function testValidateRejectsNullToken(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::validate(null));
    }

    public function testValidateRejectsWhenNoTokenInSession(): void
    {
        $this->assertFalse(Csrf::validate('iets'));
    }
}
