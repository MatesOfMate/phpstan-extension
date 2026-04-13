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

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'toon'));

        $this->assertSame(6, $decoded['summary']['level']);
        $this->assertSame(0, $decoded['summary']['files_with_errors']);
        $this->assertSame('OK', $decoded['status']);
    }

    public function testFormatToonWithErrors(): void
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

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'toon'));

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

    public function testFormatByFileMode(): void
    {
        $errors = [
            ['file' => '/path/File1.php', 'line' => 10, 'message' => 'Error 1', 'ignorable' => true],
            ['file' => '/path/File2.php', 'line' => 20, 'message' => 'Error 2', 'ignorable' => true],
        ];

        $result = new AnalysisResult(
            errorCount: 2,
            fileErrorCount: 2,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-file'));

        $this->assertSame(2, $decoded['summary']['files_with_errors']);
        $this->assertArrayHasKey('File1.php', $decoded['by_file']);
        $this->assertArrayHasKey('File2.php', $decoded['by_file']);
    }

    public function testFormatByTypeMode(): void
    {
        $errors = [
            ['file' => 'Test1.php', 'line' => 10, 'message' => 'Property has no type', 'ignorable' => true],
            ['file' => 'Test2.php', 'line' => 20, 'message' => 'Method has no return type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(
            errorCount: 2,
            fileErrorCount: 2,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '64MB',
        );

        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertSame(2, $decoded['summary']['total_errors']);
        $this->assertArrayHasKey('missing-type', $decoded['by_type']);
        $this->assertArrayHasKey('missing-return-type', $decoded['by_type']);
    }

    public function testFormatThrowsExceptionForInvalidMode(): void
    {
        $result = new AnalysisResult(0, 0, [], 6, 1.5, '64MB');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format mode: invalid-mode');
        $this->formatter->format($result, 'invalid-mode');
    }

    public function testCategorizeErrorForMissingType(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Property $foo has no type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('missing-type', $decoded['by_type']);
    }

    public function testCategorizeErrorForMissingReturnType(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method foo() has no return type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('missing-return-type', $decoded['by_type']);
    }

    public function testCategorizeErrorForNullableReturn(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method should return Foo|null but returns Foo', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('nullable-return', $decoded['by_type']);
    }

    public function testCategorizeErrorForUndefinedProperty(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Access to undefined property Foo::$bar', 'ignorable' => false],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('undefined-property', $decoded['by_type']);
    }

    public function testCategorizeErrorForUndefinedMethod(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Call to undefined method Foo::bar()', 'ignorable' => false],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('undefined-method', $decoded['by_type']);
    }

    public function testCategorizeErrorForTypeMismatch(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method expects string but int given', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('type-mismatch', $decoded['by_type']);
    }

    public function testCategorizeErrorForOther(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Some other error message', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $decoded = ResponseEncoder::decode($this->formatter->format($result, 'by-type'));

        $this->assertArrayHasKey('other', $decoded['by_type']);
    }
}
