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

use MatesOfMate\PhpStanExtension\Capability\ConfigResource;
use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ConfigResourceTest extends TestCase
{
    public function testGetConfigurationReturnsValidResourceStructure(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn(null);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('uri', $config);
        $this->assertArrayHasKey('mimeType', $config);
        $this->assertArrayHasKey('text', $config);
    }

    public function testGetConfigurationReturnsCorrectUri(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn(null);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        $this->assertSame('phpstan://config', $config['uri']);
    }

    public function testGetConfigurationReturnsToonMimeType(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn(null);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        $this->assertSame('text/plain', $config['mimeType']);
    }

    public function testGetConfigurationIncludesConfigPathWhenFound(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn('/path/to/phpstan.neon');
        $detector->method('getConfiguredLevel')->willReturn(6);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        // TOON format is human-readable text, just check it contains expected values
        $this->assertStringContainsString('config_exists', $config['text']);
        $this->assertStringContainsString('/path/to/phpstan.neon', $config['text']);
        $this->assertStringContainsString('6', $config['text']);
    }
}
