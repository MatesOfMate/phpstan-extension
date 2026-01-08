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

use MatesOfMate\PhpStan\DTO\ErrorMessage;
use MatesOfMate\PhpStan\Formatter\ErrorGrouper;
use PHPUnit\Framework\TestCase;

class ErrorGrouperTest extends TestCase
{
    private ErrorGrouper $grouper;

    protected function setUp(): void
    {
        $this->grouper = new ErrorGrouper();
    }

    public function testGroupByFileGroupsErrorsByFilePath(): void
    {
        $errors = [
            new ErrorMessage('File1.php', 10, 'Error 1', true),
            new ErrorMessage('File2.php', 20, 'Error 2', true),
            new ErrorMessage('File1.php', 30, 'Error 3', true),
        ];

        $result = $this->grouper->groupByFile($errors);

        $this->assertArrayHasKey('File1.php', $result);
        $this->assertArrayHasKey('File2.php', $result);
        $this->assertCount(2, $result['File1.php']);
        $this->assertCount(1, $result['File2.php']);
    }

    public function testGroupByTypeClassifiesErrors(): void
    {
        $errors = [
            new ErrorMessage('File.php', 10, 'Property has no type', true),
            new ErrorMessage('File.php', 20, 'Undefined property Test::$prop', true),
            new ErrorMessage('File.php', 30, 'Method has no return type', true),
        ];

        $result = $this->grouper->groupByType($errors);

        $this->assertArrayHasKey('missing-type', $result);
        $this->assertArrayHasKey('undefined-property', $result);
    }

    public function testGroupByFixabilityCategorizesErrors(): void
    {
        $errors = [
            new ErrorMessage('File.php', 10, 'Property has no type', true),
            new ErrorMessage('File.php', 20, 'Undefined property Test::$prop', true),
            new ErrorMessage('File.php', 30, 'Complex error here', true),
        ];

        $result = $this->grouper->groupByFixability($errors);

        $this->assertArrayHasKey('auto', $result);
        $this->assertArrayHasKey('manual', $result);
        $this->assertArrayHasKey('complex', $result);
    }
}
