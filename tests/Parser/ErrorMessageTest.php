<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Tests\Parser;

use MatesOfMate\PhpStan\Parser\ErrorMessage;
use PHPUnit\Framework\TestCase;

class ErrorMessageTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $error = new ErrorMessage(
            file: 'src/Test.php',
            line: 42,
            message: 'Test error message',
            ignorable: true,
        );

        $this->assertSame('src/Test.php', $error->file);
        $this->assertSame(42, $error->line);
        $this->assertSame('Test error message', $error->message);
        $this->assertTrue($error->ignorable);
    }

    public function testIgnorableCanBeFalse(): void
    {
        $error = new ErrorMessage(
            file: 'src/Test.php',
            line: 1,
            message: 'Critical error',
            ignorable: false,
        );

        $this->assertFalse($error->ignorable);
    }
}
