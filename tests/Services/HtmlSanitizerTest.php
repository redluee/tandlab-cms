<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    public function testEmptyInputReturnsEmptyString(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(''));
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }

    public function testAllowedTagsArePreserved(): void
    {
        $result = HtmlSanitizer::clean('<p>Tekst met <strong>nadruk</strong>.</p>');

        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<strong>nadruk</strong>', $result);
    }

    public function testScriptTagIsStrippedToInertText(): void
    {
        // <script> staat niet op de allowlist, dus de tag wordt verwijderd
        // net als elke andere niet-toegestane tag: de tekst erbinnen blijft
        // over als platte (niet-uitvoerbare) tekst.
        $result = HtmlSanitizer::clean('<p>Veilig</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('</script>', $result);
    }

    public function testDisallowedTagIsUnwrappedKeepingChildren(): void
    {
        $result = HtmlSanitizer::clean('<div><p>Binnen een div</p></div>');

        $this->assertStringNotContainsString('<div', $result);
        $this->assertStringContainsString('<p>Binnen een div</p>', $result);
    }

    public function testJavascriptHrefIsStripped(): void
    {
        $result = HtmlSanitizer::clean('<p><a href="javascript:alert(1)">klik</a></p>');

        $this->assertStringNotContainsString('javascript:', $result);
    }

    public function testHttpsHrefIsKeptWithSafeAttributes(): void
    {
        $result = HtmlSanitizer::clean('<p><a href="https://example.com">link</a></p>');

        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('rel="noopener"', $result);
        $this->assertStringContainsString('target="_blank"', $result);
    }

    public function testDisallowedAttributesAreStripped(): void
    {
        $result = HtmlSanitizer::clean('<p onclick="alert(1)" style="color:red">tekst</p>');

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('style', $result);
    }

    public function testTextTypeUsesStricterAllowlist(): void
    {
        $result = HtmlSanitizer::clean('<p>Alleen <em>nadruk</em></p>', 'text');

        $this->assertStringNotContainsString('<p>', $result);
        $this->assertStringContainsString('<em>nadruk</em>', $result);
    }
}
