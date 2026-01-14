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

use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Parser\AnalysisResult;
use PHPUnit\Framework\TestCase;

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

        $output = $this->formatter->format($result, 'toon');

        $this->assertStringContainsString('summary:', $output);
        $this->assertStringContainsString('level:', $output);
        $this->assertStringContainsString('6', $output);
        $this->assertStringContainsString('status: OK', $output);
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

        $output = $this->formatter->format($result, 'toon');

        $this->assertStringContainsString('summary:', $output);
        $this->assertStringContainsString('errors:', $output);
        $this->assertStringContainsString('Test.php', $output);
        $this->assertStringContainsString('Error message', $output);
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

        $this->assertStringContainsString('files_with_errors:', $output);
        $this->assertStringContainsString('3', $output);
        $this->assertStringContainsString('total_errors:', $output);
        $this->assertStringContainsString('5', $output);
        $this->assertStringContainsString('status: FAIL', $output);
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

        $output = $this->formatter->format($result, 'detailed');

        $this->assertStringContainsString('summary:', $output);
        $this->assertStringContainsString('errors:', $output);
        $this->assertStringContainsString('/full/path/to/Test.php', $output);
        $this->assertStringContainsString('Property has no type', $output);
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

        $output = $this->formatter->format($result, 'by-file');

        $this->assertStringContainsString('summary:', $output);
        $this->assertStringContainsString('by_file:', $output);
        $this->assertStringContainsString('File1.php', $output);
        $this->assertStringContainsString('File2.php', $output);
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

        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('summary:', $output);
        $this->assertStringContainsString('by_type:', $output);
        $this->assertStringContainsString('missing-type', $output);
        $this->assertStringContainsString('missing-return-type', $output);
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
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('missing-type', $output);
    }

    public function testCategorizeErrorForMissingReturnType(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method foo() has no return type', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('missing-return-type', $output);
    }

    public function testCategorizeErrorForNullableReturn(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method should return Foo|null but returns Foo', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('nullable-return', $output);
    }

    public function testCategorizeErrorForUndefinedProperty(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Access to undefined property Foo::$bar', 'ignorable' => false],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('undefined-property', $output);
    }

    public function testCategorizeErrorForUndefinedMethod(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Call to undefined method Foo::bar()', 'ignorable' => false],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('undefined-method', $output);
    }

    public function testCategorizeErrorForTypeMismatch(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Method expects string but int given', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('type-mismatch', $output);
    }

    public function testCategorizeErrorForOther(): void
    {
        $errors = [
            ['file' => 'Test.php', 'line' => 1, 'message' => 'Some other error message', 'ignorable' => true],
        ];

        $result = new AnalysisResult(1, 1, $errors, null, null, null);
        $output = $this->formatter->format($result, 'by-type');

        $this->assertStringContainsString('other', $output);
    }
}
