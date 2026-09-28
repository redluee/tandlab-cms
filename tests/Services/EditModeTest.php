<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\EditMode;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * EditMode::$on is process-global static state, dus deze test draait
 * bewust in een apart proces om andere tests niet te beïnvloeden.
 */
#[RunTestsInSeparateProcesses]
final class EditModeTest extends TestCase
{
    public function testOffByDefault(): void
    {
        $this->assertFalse(EditMode::on());
    }

    public function testEnableTurnsItOn(): void
    {
        EditMode::enable();

        $this->assertTrue(EditMode::on());
    }
}
