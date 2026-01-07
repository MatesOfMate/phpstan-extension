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

use MatesOfMate\ExampleExtension\Capability\ExampleTool;
use PHPUnit\Framework\TestCase;

class ExampleToolTest extends TestCase
{
    public function testReturnsValidJson(): void
    {
        $tool = new ExampleTool();

        $result = $tool->execute();

        $this->assertJson($result);
    }

    public function testContainsExpectedKeys(): void
    {
        $tool = new ExampleTool();

        $result = json_decode($tool->execute(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('hint', $result);
    }
}
