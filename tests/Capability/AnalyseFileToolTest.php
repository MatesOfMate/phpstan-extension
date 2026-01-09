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

use MatesOfMate\PhpStan\Capability\AnalyseFileTool;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Runner\AnalysisResult;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseFileToolTest extends TestCase
{
    public function testExecuteAnalysesSingleFile(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], 6, 1.0, '32MB');

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['path']) && __FILE__ === $options['path']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->with($analysisResult, 'toon')
            ->willReturn('file analysis output');

        $tool = new AnalyseFileTool($runner, $formatter);
        $result = $tool->execute(__FILE__);

        $this->assertSame('file analysis output', $result);
    }

    public function testExecuteThrowsExceptionForNonexistentFile(): void
    {
        $runner = $this->createMock(PhpStanRunner::class);
        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseFileTool($runner, $formatter);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found: /nonexistent/file.php');

        $tool->execute('/nonexistent/file.php');
    }

    public function testExecutePassesLevelToRunner(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['level']) && 8 === $options['level']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $tool = new AnalyseFileTool($runner, $formatter);
        $tool->execute(__FILE__, level: 8);
    }

    public function testExecutePassesConfigurationToRunner(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['configuration']) && 'custom.neon' === $options['configuration']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $tool = new AnalyseFileTool($runner, $formatter);
        $tool->execute(__FILE__, configuration: 'custom.neon');
    }
}
