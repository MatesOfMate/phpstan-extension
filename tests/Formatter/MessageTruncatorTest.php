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

use MatesOfMate\PhpStan\Formatter\MessageTruncator;
use PHPUnit\Framework\TestCase;

class MessageTruncatorTest extends TestCase
{
    private MessageTruncator $truncator;

    protected function setUp(): void
    {
        $this->truncator = new MessageTruncator();
    }

    public function testTruncateRemovesCommonPrefixes(): void
    {
        $message = 'Parameter $id of method Test::method() expects int, string given';
        $result = $this->truncator->truncate($message);

        $this->assertStringStartsNotWith('Parameter ', $result);
    }

    public function testTruncateShortensFullyQualifiedClassNames(): void
    {
        $message = '\\App\\Entity\\User expects int';
        $result = $this->truncator->truncate($message);

        $this->assertStringNotContainsString('\\App\\Entity\\', $result);
    }

    public function testTruncateShortensMethodReferences(): void
    {
        $message = 'Parameter of method App\\Service\\UserService::getUser() expects int';
        $result = $this->truncator->truncate($message);

        $this->assertStringContainsString('of ', $result);
        $this->assertStringNotContainsString('method App\\Service\\UserService::', $result);
    }

    public function testTruncateLimitsLengthToMaxLength(): void
    {
        $message = str_repeat('a', 100);
        $result = $this->truncator->truncate($message, 50);

        $this->assertLessThanOrEqual(50, \strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    public function testTruncateDoesNotModifyShortMessages(): void
    {
        $message = 'Short message';
        $result = $this->truncator->truncate($message);

        $this->assertSame($message, $result);
    }

    public function testTruncateFileNameRemovesProjectRoot(): void
    {
        $path = '/Users/test/project/src/Service/UserService.php';
        $result = $this->truncator->truncateFileName($path, '/Users/test/project');

        $this->assertSame('Service/UserService.php', $result);
    }

    public function testTruncateFileNameRemovesSrcPrefix(): void
    {
        $path = 'src/Service/UserService.php';
        $result = $this->truncator->truncateFileName($path);

        $this->assertSame('Service/UserService.php', $result);
    }

    public function testTruncateFileNameHandlesNullProjectRoot(): void
    {
        $path = 'relative/path/File.php';
        $result = $this->truncator->truncateFileName($path);

        $this->assertIsString($result);
    }
}
