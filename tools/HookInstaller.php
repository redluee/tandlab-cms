<?php

declare(strict_types=1);

namespace Tools;

class HookInstaller
{
    public static function install(): void
    {
        $root = dirname(__DIR__);
        $gitDir = $root . '/.git';
        if (!is_dir($gitDir)) {
            return;
        }

        $hooksDir = $gitDir . '/hooks';
        if (!is_dir($hooksDir)) {
            mkdir($hooksDir, 0775, true);
        }

        $source = $root . '/tools/pre-commit';
        $target = $hooksDir . '/pre-commit';

        copy($source, $target);
        chmod($target, 0755);

        fwrite(STDOUT, "Git pre-commit hook geïnstalleerd (composer check).\n");
    }
}
