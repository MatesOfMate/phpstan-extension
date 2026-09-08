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
        // Errors are reported as groups: one entry per rule, with the number of
        // places it fired, instead of one entry per error.
        $this->assertSame(1, $decoded['groups'][0]['count']);
        $this->assertSame('Error message', $decoded['groups'][0]['example']);
        $this->assertSame('Test.php', $decoded['groups'][0]['files']);
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

        $this->assertSame('Property has no type', $decoded['groups'][0]['example']);
        $this->assertSame(['/full/path/to/Test.php' => 1], $decoded['groups'][0]['files']);
    }

    /**
     * `default` mode may collapse two same-named files, that is what `detailed`
     * exists to still tell apart. If detailed also reported base names, two
     * unrelated Invoice.php files would be indistinguishable from one another.
     */
    public function testFormatDetailedModeKeepsFullPathsApartForSameNamedFiles(): void
    {
        $errors = [
            ['file' => '/app/src/Billing/Invoice.php', 'line' => 10, 'message' => 'Property has no type', 'ignorable' => true],
            ['file' => '/app/src/Legacy/Invoice.php', 'line' => 20, 'message' => 'Property has no type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(
            errorCount: 2,
            fileErrorCount: 2,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'detailed'));

        $this->assertSame([
            '/app/src/Billing/Invoice.php' => 1,
            '/app/src/Legacy/Invoice.php' => 1,
        ], $decoded['groups'][0]['files']);
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
