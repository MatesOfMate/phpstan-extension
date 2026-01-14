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

use MatesOfMate\PhpStan\Capability\ClearCacheTool;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ClearCacheToolTest extends TestCase
{
    public function testExecuteClearsCacheSuccessfully(): void
    {
        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('clear-result-cache', []);

        $tool = new ClearCacheTool($runner);
        $result = $tool->execute();

        $this->assertSame('PHPStan cache cleared successfully.', $result);
    }

    public function testExecutePassesConfigurationToRunner(): void
    {
        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('clear-result-cache', ['--configuration', 'custom.neon']);

        $tool = new ClearCacheTool($runner);
        $result = $tool->execute(configuration: 'custom.neon');

        $this->assertSame('PHPStan cache cleared successfully.', $result);
    }
}
