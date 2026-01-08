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

use MatesOfMate\PhpStan\DTO\ProcessResult;
use PHPUnit\Framework\TestCase;

class ProcessResultTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $result = new ProcessResult(
            exitCode: 0,
            output: 'test output',
            errorOutput: 'test error',
        );

        $this->assertSame(0, $result->exitCode);
        $this->assertSame('test output', $result->output);
        $this->assertSame('test error', $result->errorOutput);
    }

    public function testIsSuccessfulReturnsTrueForExitCodeZero(): void
    {
        $result = new ProcessResult(0, '', '');

        $this->assertTrue($result->isSuccessful());
    }

    public function testIsSuccessfulReturnsFalseForNonZeroExitCode(): void
    {
        $result = new ProcessResult(1, '', '');

        $this->assertFalse($result->isSuccessful());
    }
}
