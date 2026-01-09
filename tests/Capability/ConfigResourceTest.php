<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Tests\Capability;

use MatesOfMate\PhpStan\Capability\ConfigResource;
use MatesOfMate\PhpStan\Parser\ConfigurationDetector;
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

    public function testGetConfigurationReturnsJsonMimeType(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn(null);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        $this->assertSame('application/json', $config['mimeType']);
    }

    public function testGetConfigurationIncludesConfigPathWhenFound(): void
    {
        $detector = $this->createMock(ConfigurationDetector::class);
        $detector->method('detect')->willReturn('/path/to/phpstan.neon');
        $detector->method('getConfiguredLevel')->willReturn(6);

        $resource = new ConfigResource($detector);
        $config = $resource->getConfiguration();

        $data = json_decode($config['text'], true, 512, \JSON_THROW_ON_ERROR);

        $this->assertTrue($data['config_exists']);
        $this->assertSame('/path/to/phpstan.neon', $data['config_file']);
        $this->assertSame(6, $data['configured_level']);
    }
}
