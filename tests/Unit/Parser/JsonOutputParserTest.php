<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Parser;

use MatesOfMate\PhpStanExtension\Parser\JsonOutputParser;
use MatesOfMate\PhpStanExtension\Runner\RunResult;
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

        $runResult = new RunResult(exitCode: 0, output: $json, errorOutput: '');
        $result = $this->parser->parse($runResult);

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

        $runResult = new RunResult(exitCode: 1, output: $json, errorOutput: '');
        $result = $this->parser->parse($runResult);

        $this->assertSame(2, $result->errorCount);
        $this->assertSame(2, $result->fileErrorCount);
        $this->assertCount(2, $result->errors);

        $this->assertSame('src/Test.php', $result->errors[0]['file']);
        $this->assertSame(10, $result->errors[0]['line']);
        $this->assertSame('Error message 1', $result->errors[0]['message']);
        $this->assertTrue($result->errors[0]['ignorable']);

        $this->assertSame('src/Test.php', $result->errors[1]['file']);
        $this->assertSame(20, $result->errors[1]['line']);
        $this->assertSame('Error message 2', $result->errors[1]['message']);
        $this->assertFalse($result->errors[1]['ignorable']);
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

        $runResult = new RunResult(exitCode: 1, output: $json, errorOutput: '');
        $result = $this->parser->parse($runResult);

        $this->assertSame(3, $result->errorCount);
        $this->assertCount(3, $result->errors);
        $this->assertSame('src/File1.php', $result->errors[0]['file']);
        $this->assertSame('src/File2.php', $result->errors[1]['file']);
        $this->assertSame('src/File2.php', $result->errors[2]['file']);
    }

    public function testParseWithLeadingWarningBeforeJson(): void
    {
        $json = json_encode([
            'totals' => ['errors' => 0, 'file_errors' => 0],
            'files' => [],
            'errors' => [],
        ], \JSON_THROW_ON_ERROR);

        $runResult = new RunResult(exitCode: 0, output: "PHPStan turbo extension: could not load, falling back.\n".$json, errorOutput: '');
        $result = $this->parser->parse($runResult);

        $this->assertFalse($result->parseFailed);
        $this->assertSame(0, $result->errorCount);
        $this->assertSame(0, $result->fileErrorCount);
    }

    public function testParseWithTrailingTextAfterJson(): void
    {
        $json = json_encode([
            'totals' => ['errors' => 0, 'file_errors' => 1],
            'files' => [
                'src/Test.php' => [
                    'errors' => 1,
                    'messages' => [
                        ['message' => 'Error message', 'line' => 1, 'ignorable' => true],
                    ],
                ],
            ],
            'errors' => [],
        ], \JSON_THROW_ON_ERROR);

        $runResult = new RunResult(exitCode: 1, output: $json."\nDone in 1.23s.", errorOutput: '');
        $result = $this->parser->parse($runResult);

        $this->assertFalse($result->parseFailed);
        $this->assertSame(1, $result->errorCount);
        $this->assertSame('src/Test.php', $result->errors[0]['file']);
    }

    public function testParseFallsBackToRawOutputWhenNoJsonIsPresent(): void
    {
        $runResult = new RunResult(exitCode: 1, output: 'PHPStan hit an internal error and printed a table instead.', errorOutput: 'Fatal error: something broke');
        $result = $this->parser->parse($runResult);

        $this->assertTrue($result->parseFailed);
        $this->assertSame(0, $result->errorCount);
        $this->assertSame([], $result->errors);
        $this->assertStringContainsString('PHPStan hit an internal error', (string) $result->rawOutput);
        $this->assertStringContainsString('Fatal error', (string) $result->errorOutput);
        $this->assertNotSame([], $result->diagnostics);
    }

    public function testParseFallsBackOnEmptyOutput(): void
    {
        $runResult = new RunResult(exitCode: 1, output: '', errorOutput: '');
        $result = $this->parser->parse($runResult);

        $this->assertTrue($result->parseFailed);
        $this->assertSame(0, $result->errorCount);
    }
}
