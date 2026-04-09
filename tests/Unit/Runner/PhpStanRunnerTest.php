<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Runner;

use MatesOfMate\Common\Process\ProcessExecutorInterface;
use MatesOfMate\Common\Process\ProcessResult;
use MatesOfMate\PhpStanExtension\Runner\PhpStanRunner;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class PhpStanRunnerTest extends TestCase
{
    public function testRunExecutesPhpstanViaProcessExecutor(): void
    {
        $executor = $this->createMock(ProcessExecutorInterface::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                'phpstan',
                ['analyse', '--error-format=json', '--no-progress'],
                300,
                true
            )
            ->willReturn(new ProcessResult(0, '{"totals":{"file_errors":0},"files":{}}', ''));

        $runner = new PhpStanRunner($executor);
        $result = $runner->run('analyse', []);

        $this->assertSame(0, $result->exitCode);
        $this->assertStringContainsString('file_errors', $result->output);
    }

    public function testRunIncludesProvidedArguments(): void
    {
        $executor = $this->createMock(ProcessExecutorInterface::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                'phpstan',
                ['analyse', '--configuration', 'phpstan.neon', '--level', '8', '--error-format=json', '--no-progress'],
                300,
                true
            )
            ->willReturn(new ProcessResult(0, '{}', ''));

        $runner = new PhpStanRunner($executor);
        $runner->run('analyse', ['--configuration', 'phpstan.neon', '--level', '8']);
    }

    public function testCustomCommandBypassesProcessExecutor(): void
    {
        $executor = $this->createMock(ProcessExecutorInterface::class);
        $executor->expects($this->never())->method('execute');

        $runner = new PhpStanRunner(
            $executor,
            '/tmp',
            [\PHP_BINARY, '-r', 'fwrite(STDERR, "custom command failure"); exit(1);'],
        );

        $result = $runner->run('analyse', []);

        $this->assertSame(1, $result->exitCode);
    }

    public function testDefaultBehaviorUnchangedWithEmptyCustomCommand(): void
    {
        $executor = $this->createMock(ProcessExecutorInterface::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                'phpstan',
                ['analyse', '--error-format=json', '--no-progress'],
                300,
                true
            )
            ->willReturn(new ProcessResult(0, '{"totals":{"file_errors":0},"files":{}}', ''));

        $runner = new PhpStanRunner($executor, '/some/root', []);
        $result = $runner->run('analyse', []);

        $this->assertSame(0, $result->exitCode);
    }

    public function testClearCacheCommandHasNoDefaultArgs(): void
    {
        $executor = $this->createMock(ProcessExecutorInterface::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with(
                'phpstan',
                ['clear-result-cache'],
                300,
                true
            )
            ->willReturn(new ProcessResult(0, 'Cache cleared', ''));

        $runner = new PhpStanRunner($executor);
        $result = $runner->run('clear-result-cache', []);

        $this->assertSame(0, $result->exitCode);
    }
}
