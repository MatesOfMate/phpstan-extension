<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Tests\Parser;

use MatesOfMate\PhpStan\Parser\ConfigurationDetector;
use PHPUnit\Framework\TestCase;

class ConfigurationDetectorTest extends TestCase
{
    private ConfigurationDetector $detector;
    private string $testDir;

    protected function setUp(): void
    {
        $this->detector = new ConfigurationDetector();
        $this->testDir = sys_get_temp_dir().'/phpstan-test-'.uniqid();
        mkdir($this->testDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);
    }

    public function testDetectReturnsNullWhenNoConfigExists(): void
    {
        $result = $this->detector->detect($this->testDir);

        $this->assertNull($result);
    }

    public function testDetectFindsPhpstanNeon(): void
    {
        $configFile = $this->testDir.'/phpstan.neon';
        file_put_contents($configFile, 'parameters:');

        $result = $this->detector->detect($this->testDir);

        $this->assertSame($configFile, $result);
    }

    public function testDetectFindsPhpstanNeonDist(): void
    {
        $configFile = $this->testDir.'/phpstan.neon.dist';
        file_put_contents($configFile, 'parameters:');

        $result = $this->detector->detect($this->testDir);

        $this->assertSame($configFile, $result);
    }

    public function testDetectFindsPhpstanDistNeon(): void
    {
        $configFile = $this->testDir.'/phpstan.dist.neon';
        file_put_contents($configFile, 'parameters:');

        $result = $this->detector->detect($this->testDir);

        $this->assertSame($configFile, $result);
    }

    public function testDetectPrioritizesPhpstanNeonOverOthers(): void
    {
        $configFile1 = $this->testDir.'/phpstan.neon';
        $configFile2 = $this->testDir.'/phpstan.neon.dist';
        file_put_contents($configFile1, 'parameters:');
        file_put_contents($configFile2, 'parameters:');

        $result = $this->detector->detect($this->testDir);

        $this->assertSame($configFile1, $result);
    }

    public function testGetConfiguredLevelExtractsIntegerLevel(): void
    {
        $configFile = $this->testDir.'/phpstan.neon';
        file_put_contents($configFile, "parameters:\n    level: 6\n");

        $result = $this->detector->getConfiguredLevel($configFile);

        $this->assertSame(6, $result);
    }

    public function testGetConfiguredLevelExtractsMaxLevel(): void
    {
        $configFile = $this->testDir.'/phpstan.neon';
        file_put_contents($configFile, "parameters:\n    level: max\n");

        $result = $this->detector->getConfiguredLevel($configFile);

        $this->assertSame(9, $result);
    }

    public function testGetConfiguredLevelReturnsNullWhenNoLevelSet(): void
    {
        $configFile = $this->testDir.'/phpstan.neon';
        file_put_contents($configFile, "parameters:\n    paths:\n        - src\n");

        $result = $this->detector->getConfiguredLevel($configFile);

        $this->assertNull($result);
    }

    public function testGetConfiguredLevelReturnsNullForNonExistentFile(): void
    {
        $result = $this->detector->getConfiguredLevel($this->testDir.'/nonexistent.neon');

        $this->assertNull($result);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
