<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\ExampleExtension\Tests\Capability;

use MatesOfMate\ExampleExtension\Capability\ExampleResource;
use PHPUnit\Framework\TestCase;

class ExampleResourceTest extends TestCase
{
    public function testReturnsValidResourceStructure(): void
    {
        $resource = new ExampleResource();

        $result = $resource->getConfiguration();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('uri', $result);
        $this->assertArrayHasKey('mimeType', $result);
        $this->assertArrayHasKey('text', $result);
    }

    public function testHasCorrectUri(): void
    {
        $resource = new ExampleResource();

        $result = $resource->getConfiguration();

        $this->assertEquals('example://config', $result['uri']);
    }

    public function testHasJsonMimeType(): void
    {
        $resource = new ExampleResource();

        $result = $resource->getConfiguration();

        $this->assertEquals('application/json', $result['mimeType']);
    }

    public function testContainsValidJsonText(): void
    {
        $resource = new ExampleResource();

        $result = $resource->getConfiguration();

        $this->assertJson($result['text']);
        $decoded = json_decode((string) $result['text'], true, 512, \JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('version', $decoded);
        $this->assertArrayHasKey('features', $decoded);
    }
}
