<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\ImageService;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImageServiceTest extends TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        $this->uploadDir = config_get()['uploads']['path'];
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    #[After]
    protected function cleanUploads(): void
    {
        foreach (glob($this->uploadDir . '/*') ?: [] as $file) {
            unlink($file);
        }
    }

    public function testStoreFromPathConvertsToWebp(): void
    {
        $source = $this->createPngFixture(40, 20);

        $filename = ImageService::storeFromPath($source);

        $this->assertStringEndsWith('.webp', $filename);
        $destination = $this->uploadDir . '/' . $filename;
        $this->assertFileExists($destination);
        $this->assertSame('image/webp', mime_content_type($destination));

        unlink($source);
    }

    public function testStoreFromPathResizesWhenWiderThanMax(): void
    {
        $source = $this->createPngFixture(200, 100);

        $filename = ImageService::storeFromPath($source, '', 100);

        $info = getimagesize($this->uploadDir . '/' . $filename);
        $this->assertSame(100, $info[0]);
        $this->assertSame(50, $info[1]);

        unlink($source);
    }

    public function testStoreFromPathRejectsDisallowedType(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'tandlab-test-');
        file_put_contents($source, '<?php echo "niet een afbeelding"; ?>');

        $this->expectException(RuntimeException::class);

        try {
            ImageService::storeFromPath($source);
        } finally {
            unlink($source);
        }
    }

    private function createPngFixture(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tandlab-test-') . '.png';
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 100, 200, 150));
        imagepng($image, $path);
        return $path;
    }
}
