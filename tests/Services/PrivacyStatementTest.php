<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\PrivacyStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PrivacyStatementTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/privacy-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/public/assets/docs', 0755, true);
        file_put_contents(PrivacyStatement::currentPath($this->root), '%PDF-old');
    }

    protected function tearDown(): void
    {
        foreach ([PrivacyStatement::currentPath($this->root), PrivacyStatement::previousPath($this->root)] as $f) {
            @unlink($f);
        }
    }

    private function upload(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tmp, $content);

        return ['tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => strlen($content)];
    }

    public function testReplaceKeepsPreviousVersion(): void
    {
        PrivacyStatement::replace($this->upload('%PDF-new'), $this->root);

        $this->assertSame('%PDF-new', file_get_contents(PrivacyStatement::currentPath($this->root)));
        $this->assertSame('%PDF-old', file_get_contents(PrivacyStatement::previousPath($this->root)));
    }

    public function testRejectsNonPdf(): void
    {
        $this->expectException(RuntimeException::class);
        PrivacyStatement::replace($this->upload('<?php echo 1;'), $this->root);
    }

    public function testRestoreSwapsVersions(): void
    {
        PrivacyStatement::replace($this->upload('%PDF-new'), $this->root);
        PrivacyStatement::restorePrevious($this->root);

        $this->assertSame('%PDF-old', file_get_contents(PrivacyStatement::currentPath($this->root)));
        $this->assertSame('%PDF-new', file_get_contents(PrivacyStatement::previousPath($this->root)));
    }
}
