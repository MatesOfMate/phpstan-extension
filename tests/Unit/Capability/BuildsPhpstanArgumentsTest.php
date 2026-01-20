<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Capability;

use MatesOfMate\PhpStanExtension\Capability\BuildsPhpstanArguments;
use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class BuildsPhpstanArgumentsTest extends TestCase
{
    use BuildsPhpstanArguments;

    protected function setUp(): void
    {
        $this->configDetector = $this->createMock(ConfigurationDetector::class);
    }

    public function testBuildsEmptyArgsWhenAllParametersNull(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs();

        $this->assertSame([], $args);
    }

    public function testBuildsArgsWithSinglePath(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(path: 'src/Example.php');

        $this->assertSame(['src/Example.php'], $args);
    }

    public function testBuildsArgsWithMultiplePaths(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(path: ['src/Example.php', 'tests/ExampleTest.php']);

        $this->assertSame(['--', 'src/Example.php', 'tests/ExampleTest.php'], $args);
    }

    public function testBuildsArgsWithConfiguration(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(configuration: 'phpstan.neon');

        $this->assertSame(['--configuration', 'phpstan.neon'], $args);
    }

    public function testBuildsArgsWithAutoDetectedConfiguration(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn('/path/to/phpstan.neon');

        $args = $this->buildPhpstanArgs();

        $this->assertSame(['--configuration', '/path/to/phpstan.neon'], $args);
    }

    public function testBuildsArgsWithLevel(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(level: 6);

        $this->assertSame(['--level', '6'], $args);
    }

    public function testBuildsArgsWithAllParameters(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(
            path: 'src/Example.php',
            configuration: 'phpstan.neon',
            level: 8,
        );

        $this->assertSame([
            '--configuration',
            'phpstan.neon',
            '--level',
            '8',
            'src/Example.php',
        ], $args);
    }

    public function testBuildsArgsWithAllParametersAndMultiplePaths(): void
    {
        $detector = $this->configDetector;
        // @phpstan-ignore-next-line
        $detector->method('detect')->willReturn(null);

        $args = $this->buildPhpstanArgs(
            path: ['src/', 'tests/'],
            configuration: 'custom.neon',
            level: 9,
        );

        $this->assertSame([
            '--configuration',
            'custom.neon',
            '--level',
            '9',
            '--',
            'src/',
            'tests/',
        ], $args);
    }
}
