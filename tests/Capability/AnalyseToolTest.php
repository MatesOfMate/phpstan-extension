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

use MatesOfMate\PhpStan\Capability\AnalyseTool;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Runner\AnalysisResult;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseToolTest extends TestCase
{
    public function testExecuteRunsAnalysisAndReturnsFormattedOutput(): void
    {
        $analysisResult = new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => [] === $options))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->with($analysisResult, 'toon')
            ->willReturn('formatted output');

        $tool = new AnalyseTool($runner, $formatter);
        $result = $tool->execute();

        $this->assertSame('formatted output', $result);
    }

    public function testExecutePassesConfigurationToRunner(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['configuration']) && 'phpstan.neon' === $options['configuration']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $tool = new AnalyseTool($runner, $formatter);
        $tool->execute(configuration: 'phpstan.neon');
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

        $tool = new AnalyseTool($runner, $formatter);
        $tool->execute(level: 8);
    }

    public function testExecutePassesPathToRunner(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['path']) && 'src/' === $options['path']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $tool = new AnalyseTool($runner, $formatter);
        $tool->execute(path: 'src/');
    }

    public function testExecuteSupportsMultipleFormatterModes(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->method('analyse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->with($analysisResult, 'summary')
            ->willReturn('summary output');

        $tool = new AnalyseTool($runner, $formatter);
        $result = $tool->execute(outputFormat: 'summary');

        $this->assertSame('summary output', $result);
    }

    public function testExecutePassesAllParametersToRunner(): void
    {
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['configuration'])
                && 'phpstan.neon' === $options['configuration']
                && isset($options['level'])
                && 8 === $options['level']
                && isset($options['path'])
                && 'src/' === $options['path']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $tool = new AnalyseTool($runner, $formatter);
        $tool->execute(
            configuration: 'phpstan.neon',
            level: 8,
            path: 'src/',
        );
    }
}
