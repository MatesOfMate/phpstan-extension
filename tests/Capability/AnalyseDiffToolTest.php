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

use MatesOfMate\PhpStan\Capability\AnalyseDiffTool;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Git\DiffAnalyser;
use MatesOfMate\PhpStan\Runner\AnalysisResult;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseDiffToolTest extends TestCase
{
    public function testExecuteAnalysesGitDiffFiles(): void
    {
        $changedFiles = ['src/File1.php', 'src/File2.php'];
        $analysisResult = new AnalysisResult(2, 2, [], 6, 1.0, '64MB');

        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->expects($this->once())
            ->method('isGitRepository')
            ->willReturn(true);
        $diffAnalyser->expects($this->once())
            ->method('detectDefaultBranch')
            ->willReturn('main');
        $diffAnalyser->expects($this->once())
            ->method('getChangedPhpFiles')
            ->with('main')
            ->willReturn($changedFiles);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['paths']) && $changedFiles === $options['paths']))
            ->willReturn($analysisResult);

        $formatter = $this->createMock(ToonFormatter::class);
        $formatter->expects($this->once())
            ->method('format')
            ->willReturn('errors output');

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);
        $result = $tool->execute();

        $this->assertStringContainsString('main|2|2', $result);
        $this->assertStringContainsString('src/File1.php', $result);
        $this->assertStringContainsString('errors output', $result);
    }

    public function testExecuteUsesCustomBaseRef(): void
    {
        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->method('isGitRepository')->willReturn(true);
        $diffAnalyser->expects($this->once())
            ->method('getChangedPhpFiles')
            ->with('develop')
            ->willReturn(['File.php']);
        $diffAnalyser->expects($this->never())
            ->method('detectDefaultBranch');

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->method('analyse')->willReturn(new AnalysisResult(0, 0, [], null, null, null));

        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);
        $result = $tool->execute(baseRef: 'develop');

        $this->assertStringContainsString('develop|1|0', $result);
    }

    public function testExecuteReturnsMessageWhenNoFilesChanged(): void
    {
        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->method('isGitRepository')->willReturn(true);
        $diffAnalyser->method('detectDefaultBranch')->willReturn('main');
        $diffAnalyser->method('getChangedPhpFiles')->with('main')->willReturn([]);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->never())->method('analyse');

        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);
        $result = $tool->execute();

        $this->assertStringContainsString('No PHP files changed since main', $result);
        $this->assertStringContainsString('main|0|0', $result);
    }

    public function testExecuteThrowsExceptionWhenNotGitRepository(): void
    {
        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->expects($this->once())
            ->method('isGitRepository')
            ->willReturn(false);

        $runner = $this->createMock(PhpStanRunner::class);
        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not a git repository');

        $tool->execute();
    }

    public function testExecutePassesLevelAndConfigurationToRunner(): void
    {
        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->method('isGitRepository')->willReturn(true);
        $diffAnalyser->method('detectDefaultBranch')->willReturn('main');
        $diffAnalyser->method('getChangedPhpFiles')->with('main')->willReturn(['File.php']);

        $runner = $this->createMock(PhpStanRunner::class);
        $runner->expects($this->once())
            ->method('analyse')
            ->with($this->callback(fn ($options): bool => isset($options['level'])
                && 8 === $options['level']
                && isset($options['configuration'])
                && 'custom.neon' === $options['configuration']))
            ->willReturn(new AnalysisResult(0, 0, [], null, null, null));

        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);
        $tool->execute(level: 8, configuration: 'custom.neon');
    }

    public function testExecuteThrowsExceptionWhenNoBaselineBranchExists(): void
    {
        $diffAnalyser = $this->createMock(DiffAnalyser::class);
        $diffAnalyser->method('isGitRepository')->willReturn(true);
        $diffAnalyser->expects($this->once())
            ->method('detectDefaultBranch')
            ->willReturn(null);

        $runner = $this->createMock(PhpStanRunner::class);
        $formatter = $this->createMock(ToonFormatter::class);

        $tool = new AnalyseDiffTool($runner, $formatter, $diffAnalyser);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No baseline branch found');

        $tool->execute();
    }
}
