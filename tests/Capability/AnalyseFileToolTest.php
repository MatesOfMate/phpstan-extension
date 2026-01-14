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
use MatesOfMate\PhpStan\Config\ConfigurationDetector;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Parser\AnalysisResult;
use MatesOfMate\PhpStan\Parser\JsonOutputParser;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use MatesOfMate\PhpStan\Runner\RunResult;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseFileToolTest extends TestCase
{
    public function testExecuteAnalysesSingleFile(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], 6, 1.0, '32MB');

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', [__FILE__])
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
            ->willReturn('file analysis output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseFileTool($runner, $parser, $formatter, $configDetector);
        $result = $tool->execute(__FILE__);

        $this->assertSame('file analysis output', $result);
    }

    public function testExecutePassesLevelToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['--level', '8', __FILE__])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseFileTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(__FILE__, level: 8);
    }

    public function testExecutePassesConfigurationToRunner(): void
    {
        $runResult = new RunResult(0, '{"totals":{"file_errors":0},"files":{}}', '');
        $analysisResult = new AnalysisResult(0, 0, [], null, null, null);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with('analyse', ['--configuration', 'phpstan.neon', __FILE__])
            ->willReturn($runResult);

        $parser = $this->createMock(JsonOutputParser::class);
        $parser->method('parse')->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->method('format')->willReturn('output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseFileTool($runner, $parser, $formatter, $configDetector);
        $tool->execute(__FILE__, configuration: 'phpstan.neon');
    }

    public function testExecuteSupportsMultipleModes(): void
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
            ->with($analysisResult, 'detailed')
            ->willReturn('detailed output');

        $configDetector = $this->createMock(ConfigurationDetector::class);
        $configDetector->method('detect')->willReturn(null);

        $tool = new AnalyseFileTool($runner, $parser, $formatter, $configDetector);
        $result = $tool->execute(__FILE__, mode: 'detailed');

        $this->assertSame('detailed output', $result);
    }
}
