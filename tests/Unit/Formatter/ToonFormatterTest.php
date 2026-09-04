<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Formatter;

use MatesOfMate\PhpStanExtension\Formatter\ToonFormatter;
use MatesOfMate\PhpStanExtension\Parser\AnalysisResult;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Mate\Encoding\ResponseEncoder;

class ToonFormatterTest extends TestCase
{
    private ToonFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ToonFormatter();
    }

    public function testFormatDefaultWithNoErrors(): void
    {
        $result = new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'default'));

        $this->assertSame(6, $decoded['summary']['level']);
        $this->assertSame(0, $decoded['summary']['files_with_errors']);
        $this->assertSame('OK', $decoded['status']);
    }

    public function testFormatDefaultWithErrors(): void
    {
        $errors = [
            ['file' => '/path/to/Test.php', 'line' => 10, 'message' => 'Error message', 'ignorable' => true],
        ];

        $result = new AnalysisResult(
            errorCount: 1,
            fileErrorCount: 1,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'default'));

        $this->assertSame(1, $decoded['summary']['total_errors']);
        $this->assertSame('Test.php', $decoded['errors'][0]['file']);
        $this->assertSame('Error message', $decoded['errors'][0]['message']);
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

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'summary'));

        $this->assertSame(3, $decoded['files_with_errors']);
        $this->assertSame(5, $decoded['total_errors']);
        $this->assertSame('FAIL', $decoded['status']);
    }

    public function testFormatDetailedMode(): void
    {
        $errors = [
            ['file' => '/full/path/to/Test.php', 'line' => 10, 'message' => 'Property has no type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(
            errorCount: 1,
            fileErrorCount: 1,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'detailed'));

        $this->assertSame('/full/path/to/Test.php', $decoded['errors'][0]['file']);
        $this->assertSame('Property has no type', $decoded['errors'][0]['message']);
    }

    public function testFormatThrowsExceptionForInvalidMode(): void
    {
        $result = new AnalysisResult(0, 0, [], 6, 1.5, '64MB');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format mode: invalid-mode');
        $this->formatter->format($result, 'invalid-mode');
    }

    /**
     * A run whose stdout could not be parsed as JSON must surface the raw output, not throw
     * and lose it, regardless of which mode was requested.
     */
    public function testFormatSurfacesRawOutputOnParseFailure(): void
    {
        $result = new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: null,
            executionTime: null,
            memoryUsage: null,
            parseFailed: true,
            rawOutput: 'PHPStan turbo extension: could not load.',
            errorOutput: 'Fatal error: something broke',
            diagnostics: ['Could not parse PHPStan JSON output; raw output is included.'],
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'default'));

        $this->assertSame('PARSE_ERROR', $decoded['status']);
        $this->assertStringContainsString('PHPStan turbo extension', $decoded['raw_output']);
        $this->assertStringContainsString('Fatal error', $decoded['error_output']);
        $this->assertNotSame([], $decoded['diagnostics']);
    }
}
