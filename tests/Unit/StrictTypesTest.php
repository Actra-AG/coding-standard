<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\tests\Unit;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class StrictTypesTest extends TestCase
{
    // Tracked directories of the whitelist .gitignore; the root files are scanned separately
    private const array DIRECTORIES = ['bin', 'config', 'src', 'templates', 'tests'];

    public function testEveryPhpFileDeclaresStrictTypes(): void
    {
        $root = dirname(path: __DIR__, levels: 2);
        $paths = glob(pattern: $root . '/{,.}*.php', flags: GLOB_BRACE);
        $paths = $paths === false ? [] : $paths;
        foreach (StrictTypesTest::DIRECTORIES as $directory) {
            $files = new RecursiveIteratorIterator(
                iterator: new RecursiveDirectoryIterator(
                    directory: $root . '/' . $directory,
                    flags: FilesystemIterator::SKIP_DOTS,
                ),
            );
            foreach ($files as $file) {
                if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                    $paths[] = $file->getPathname();
                }
            }
        }
        $missing = [];
        foreach ($paths as $path) {
            $code = file_get_contents(filename: $path);
            if ($code === false || preg_match(pattern: '/^declare\(strict_types=1\);$/m', subject: $code) !== 1) {
                $missing[] = $path;
            }
        }

        $this->assertNotSame([], $paths);
        $this->assertSame([], $missing);
    }
}
