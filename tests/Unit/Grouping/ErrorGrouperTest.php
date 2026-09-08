<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Grouping;

use MatesOfMate\PhpStanExtension\Grouping\ErrorGrouper;
use PHPUnit\Framework\TestCase;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ErrorGrouperTest extends TestCase
{
    private ErrorGrouper $grouper;

    protected function setUp(): void
    {
        $this->grouper = new ErrorGrouper();
    }

    public function testGroupReturnsNothingForACleanAnalysis(): void
    {
        $this->assertSame([], $this->grouper->group([]));
    }

    /**
     * PHPStan names the rule behind every error in its `identifier` field. That
     * is the rule identity itself, so it beats anything recovered from the
     * message text.
     */
    public function testTheIdentifierIsPreferredOverTheMessageText(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'one sentence', 'return.type'),
            $this->error('/b.php', 'a completely unrelated sentence', 'return.type'),
        ]);

        $this->assertCount(1, $groups);
        $this->assertSame('identifier', $groups[0]['keyedBy']);
        $this->assertSame('return.type', $groups[0]['identifier']);
        $this->assertSame(2, $groups[0]['count']);
    }

    public function testDifferentIdentifiersStaySeparate(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'x', 'return.type'),
            $this->error('/b.php', 'y', 'argument.type'),
        ]);

        $this->assertCount(2, $groups);
    }

    public function testFingerprintingTakesOverWhenTheIdentifierIsAbsent(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'Method A::x() should return int but returns string.'),
            $this->error('/b.php', 'Method B::y() should return int but returns string.'),
            $this->error('/c.php', 'Cannot access offset 42 on mixed.'),
        ]);

        $this->assertCount(2, $groups);
        $this->assertSame('fingerprint', $groups[0]['keyedBy']);
        $this->assertSame(2, $groups[0]['count']);
        $this->assertNull($groups[0]['identifier']);
    }

    /**
     * A leading "/" is not itself a word boundary, so a naive \b anchor would
     * only start matching after the first path segment, leaving it behind as
     * a differentiator ("/app/src/Foo.php" and "/web/src/Foo.php" would
     * fingerprint as "/app<path>" and "/web<path>" instead of both "<path>").
     */
    public function testFingerprintNormalizesAnEmbeddedPathFromItsLeadingSlash(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'Class not found: /app/src/Foo.php'),
            $this->error('/b.php', 'Class not found: /web/src/Foo.php'),
        ]);

        $this->assertCount(1, $groups);
        $this->assertSame('fingerprint', $groups[0]['keyedBy']);
        $this->assertSame(2, $groups[0]['count']);
    }

    public function testAnEmptyIdentifierCountsAsAbsent(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'Cannot access offset 1 on mixed.', ''),
            $this->error('/b.php', 'Cannot access offset 2 on mixed.', ''),
        ]);

        $this->assertCount(1, $groups);
        $this->assertSame('fingerprint', $groups[0]['keyedBy']);
    }

    public function testGroupsAreOrderedBySizeAndCountTheirFiles(): void
    {
        $groups = $this->grouper->group([
            $this->error('/a.php', 'x', 'return.type'),
            $this->error('/a.php', 'x', 'return.type'),
            $this->error('/b.php', 'x', 'return.type'),
            $this->error('/c.php', 'y', 'argument.type'),
        ]);

        $this->assertSame([3, 1], array_column($groups, 'count'));
        $this->assertSame(['a.php' => 2, 'b.php' => 1], $groups[0]['files']);
        $this->assertCount(3, $groups[0]['members']);
    }

    /**
     * @return array<string, mixed>
     */
    private function error(string $file, string $message, ?string $identifier = null): array
    {
        return ['file' => $file, 'line' => 10, 'message' => $message, 'ignorable' => true, 'identifier' => $identifier];
    }
}
