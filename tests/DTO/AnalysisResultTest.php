<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Tests\DTO;

use MatesOfMate\PhpStan\DTO\AnalysisResult;
use MatesOfMate\PhpStan\DTO\ErrorMessage;
use PHPUnit\Framework\TestCase;

class AnalysisResultTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $errors = [
            new ErrorMessage('file1.php', 10, 'Error 1', true),
            new ErrorMessage('file2.php', 20, 'Error 2', false),
        ];

        $result = new AnalysisResult(
            errorCount: 2,
            fileErrorCount: 2,
            errors: $errors,
            level: 6,
            executionTime: 1.5,
            memoryUsage: '128MB',
        );

        $this->assertSame(2, $result->errorCount);
        $this->assertSame(2, $result->fileErrorCount);
        $this->assertCount(2, $result->errors);
        $this->assertSame(6, $result->level);
        $this->assertSame(1.5, $result->executionTime);
        $this->assertSame('128MB', $result->memoryUsage);
    }

    public function testHasErrorsReturnsTrueWhenErrorsExist(): void
    {
        $result = new AnalysisResult(
            errorCount: 1,
            fileErrorCount: 1,
            errors: [new ErrorMessage('file.php', 1, 'Error', true)],
            level: 6,
            executionTime: 1.0,
            memoryUsage: '64MB',
        );

        $this->assertTrue($result->hasErrors());
    }

    public function testHasErrorsReturnsFalseWhenNoErrors(): void
    {
        $result = new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: 6,
            executionTime: 1.0,
            memoryUsage: '64MB',
        );

        $this->assertFalse($result->hasErrors());
    }
}
