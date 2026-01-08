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

use MatesOfMate\PhpStan\Parser\JsonOutputParser;
use PHPUnit\Framework\TestCase;

class JsonOutputParserTest extends TestCase
{
    private JsonOutputParser $parser;

    protected function setUp(): void
    {
        $this->parser = new JsonOutputParser();
    }

    public function testParseEmptyOutput(): void
    {
        $json = json_encode([
            'totals' => [
                'errors' => 0,
                'file_errors' => 0,
            ],
            'files' => [],
            'errors' => [],
        ], \JSON_THROW_ON_ERROR);

        $result = $this->parser->parse($json);

        $this->assertSame(0, $result->errorCount);
        $this->assertSame(0, $result->fileErrorCount);
        $this->assertCount(0, $result->errors);
    }

    public function testParseWithErrors(): void
    {
        $json = json_encode([
            'totals' => [
                'errors' => 0,
                'file_errors' => 2,
            ],
            'files' => [
                'src/Test.php' => [
                    'errors' => 2,
                    'messages' => [
                        [
                            'message' => 'Error message 1',
                            'line' => 10,
                            'ignorable' => true,
                        ],
                        [
                            'message' => 'Error message 2',
                            'line' => 20,
                            'ignorable' => false,
                        ],
                    ],
                ],
            ],
            'errors' => [],
        ], \JSON_THROW_ON_ERROR);

        $result = $this->parser->parse($json);

        $this->assertSame(0, $result->errorCount);
        $this->assertSame(2, $result->fileErrorCount);
        $this->assertCount(2, $result->errors);

        $this->assertSame('src/Test.php', $result->errors[0]->file);
        $this->assertSame(10, $result->errors[0]->line);
        $this->assertSame('Error message 1', $result->errors[0]->message);
        $this->assertTrue($result->errors[0]->ignorable);

        $this->assertSame('src/Test.php', $result->errors[1]->file);
        $this->assertSame(20, $result->errors[1]->line);
        $this->assertSame('Error message 2', $result->errors[1]->message);
        $this->assertFalse($result->errors[1]->ignorable);
    }

    public function testParseMultipleFiles(): void
    {
        $json = json_encode([
            'totals' => [
                'errors' => 0,
                'file_errors' => 3,
            ],
            'files' => [
                'src/File1.php' => [
                    'errors' => 1,
                    'messages' => [
                        ['message' => 'Error 1', 'line' => 5, 'ignorable' => true],
                    ],
                ],
                'src/File2.php' => [
                    'errors' => 2,
                    'messages' => [
                        ['message' => 'Error 2', 'line' => 10, 'ignorable' => true],
                        ['message' => 'Error 3', 'line' => 15, 'ignorable' => false],
                    ],
                ],
            ],
            'errors' => [],
        ], \JSON_THROW_ON_ERROR);

        $result = $this->parser->parse($json);

        $this->assertCount(3, $result->errors);
        $this->assertSame('src/File1.php', $result->errors[0]->file);
        $this->assertSame('src/File2.php', $result->errors[1]->file);
        $this->assertSame('src/File2.php', $result->errors[2]->file);
    }
}
