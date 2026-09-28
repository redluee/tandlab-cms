<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\SecurityHeaders;
use PHPUnit\Framework\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function testHstsOnlyOverHttps(): void
    {
        $this->assertArrayNotHasKey('Strict-Transport-Security', SecurityHeaders::headers(false));
        $this->assertArrayHasKey('Strict-Transport-Security', SecurityHeaders::headers(true));
    }

    public function testFramingAndSniffingAreBlocked(): void
    {
        $headers = SecurityHeaders::headers(false);
        $this->assertSame('SAMEORIGIN', $headers['X-Frame-Options']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertStringContainsString('frame-ancestors', $headers['Content-Security-Policy']);
    }
}
