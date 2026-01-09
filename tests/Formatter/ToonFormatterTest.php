<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Tests\Formatter;

use MatesOfMate\PhpStan\Formatter\ErrorGrouper;
use MatesOfMate\PhpStan\Formatter\MessageTruncator;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Parser\ErrorMessage;
use MatesOfMate\PhpStan\Runner\AnalysisResult;
use PHPUnit\Framework\TestCase;

class ToonFormatterTest extends TestCase
{
    private ToonFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ToonFormatter(
            new MessageTruncator(),
            new ErrorGrouper(),
        );
    }

    public function testFormatToonWithNoErrors(): void
    {
        $result = new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $output = $this->formatter->format($result, 'toon');

        $this->assertStringContainsString('summary{level,files_with_errors,total_errors,time}:', $output);
        $this->assertStringContainsString('6|0|0|1.500s', $output);
        $this->assertStringContainsString('status:OK', $output);
    }

    public function testFormatToonWithErrors(): void
    {
        $errors = [
            new ErrorMessage('Test.php', 10, 'Error message', true),
        ];

        $result = new AnalysisResult(
            errorCount: 1,
            fileErrorCount: 1,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $output = $this->formatter->format($result, 'toon');

        $this->assertStringContainsString('summary{level,files_with_errors,total_errors,time}:', $output);
        $this->assertStringContainsString('errors[1]{file,line,msg,ignorable}:', $output);
        $this->assertStringContainsString('Test.php|10|', $output);
    }

    public function testFormatSummaryMode(): void
    {
        $result = new AnalysisResult(
            errorCount: 5,
            fileErrorCount: 3,
            errors: [],
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $output = $this->formatter->format($result, 'summary');

        $this->assertStringContainsString('summary{files,errors,level}:', $output);
        $this->assertStringContainsString('3|5|6', $output);
        $this->assertStringContainsString('status:FAIL', $output);
    }

    public function testFormatDetailedMode(): void
    {
        $errors = [
            new ErrorMessage('Test.php', 10, 'Property has no type', true),
        ];

        $result = new AnalysisResult(
            errorCount: 1,
            fileErrorCount: 1,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $output = $this->formatter->format($result, 'detailed');

        $this->assertStringContainsString('errors[1]{file,line,msg,hint}:', $output);
        $this->assertStringContainsString('Test.php|10|', $output);
    }

    public function testFormatByFileMode(): void
    {
        $errors = [
            new ErrorMessage('File1.php', 10, 'Error 1', true),
            new ErrorMessage('File2.php', 20, 'Error 2', true),
        ];

        $result = new AnalysisResult(
            errorCount: 2,
            fileErrorCount: 2,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $output = $this->formatter->format($result, 'by-file');

        $this->assertStringContainsString('summary{files_with_errors,total_errors}:', $output);
        $this->assertStringContainsString('File1.php', $output);
        $this->assertStringContainsString('File2.php', $output);
    }

    public function testFormatThrowsExceptionForInvalidMode(): void
    {
        $result = new AnalysisResult(0, 0, [], 6, 1.5, '64MB');

        $this->expectException(\InvalidArgumentException::class);
        $this->formatter->format($result, 'invalid-mode');
    }
}
