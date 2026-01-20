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

use MatesOfMate\PhpStanExtension\Capability\AnalyseTool;
use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use MatesOfMate\PhpStanExtension\Formatter\ToonFormatter;
use MatesOfMate\PhpStanExtension\Parser\AnalysisResult;
use MatesOfMate\PhpStanExtension\Parser\JsonOutputParser;
use MatesOfMate\PhpStanExtension\Runner\PhpStanRunner;
use MatesOfMate\PhpStanExtension\Runner\RunResult;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseToolTest extends TestCase
{
    public function testExecuteRunsAnalysisAndReturnsFormattedOutput(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], 6, 1.5, '64MB');

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', [])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with($runResult)
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->with($analysisResult, 'toon')
            ->willReturn('formatted output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $result = $tool->execute();

        $this->assertSame('formatted output', $result);
    }

    public function testExecutePassesConfigurationToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['--configuration', 'phpstan.neon'])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(configuration: 'phpstan.neon');
    }

    public function testExecutePassesLevelToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['--level', '8'])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(level: 8);
    }

    public function testExecutePassesPathToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['src/'])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(path: 'src/');
    }

    public function testExecuteSupportsMultipleFormatterModes(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->method('run')->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->with($analysisResult, 'summary')
            ->willReturn('summary output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $result = $tool->execute(mode: 'summary');

        $this->assertSame('summary output', $result);
    }

    public function testExecutePassesAllParametersToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['--configuration', 'phpstan.neon', '--level', '8', 'src/'])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(
            configuration: 'phpstan.neon',
            level: 8,
            path: 'src/',
        );
    }
}
