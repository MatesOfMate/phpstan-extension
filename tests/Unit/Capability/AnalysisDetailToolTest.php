<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Tests\Unit\Capability;

use MatesOfMate\Common\Cache\RunCache;
use MatesOfMate\PhpStanExtension\Capability\AnalysisDetailTool;
use MatesOfMate\PhpStanExtension\Grouping\ErrorGrouper;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Mate\Encoding\ResponseEncoder;

/**
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalysisDetailToolTest extends TestCase
{
    private string $cacheDir;
    private RunCache $cache;
    private AnalysisDetailTool $tool;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir().'/phpstan-detail-'.bin2hex(random_bytes(4));
        $this->cache = new RunCache($this->cacheDir, 'phpstan-runs', 20);
        $this->tool = new AnalysisDetailTool($this->cache);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir.'/*/*') ?: [] as $file) {
            @unlink($file);
        }
        foreach (glob($this->cacheDir.'/*') ?: [] as $dir) {
            @rmdir($dir);
        }
        @rmdir($this->cacheDir);
    }

    public function testAnUnknownRunIdIsReportedWithTheAvailableOnes(): void
    {
        $this->cache->store(['groups' => []]);

        $decoded = $this->decode($this->tool->execute('nope'));

        $this->assertStringContainsString('Unknown run id', (string) $decoded['error']);
        $this->assertStringContainsString('Cached runs', (string) $decoded['hint']);
    }

    public function testAnEmptyCacheSaysToRunTheAnalysisFirst(): void
    {
        $decoded = $this->decode($this->tool->execute('nope'));

        $this->assertStringContainsString('Run phpstan-analyse first', (string) $decoded['hint']);
    }

    public function testEveryErrorComesBackWhenNothingIsNarrowed(): void
    {
        $decoded = $this->decode($this->tool->execute($this->storeRun()));

        $this->assertSame(4, $decoded['returned']);
        $this->assertArrayNotHasKey('truncated', $decoded);
    }

    public function testOneGroupCanBeFetched(): void
    {
        $decoded = $this->decode($this->tool->execute($this->storeRun(), group: 'g2'));

        $this->assertSame(1, $decoded['returned']);
        $this->assertSame('argument.type', $decoded['entries'][0]['identifier']);
    }

    public function testErrorsCanBeNarrowedToAFile(): void
    {
        $decoded = $this->decode($this->tool->execute($this->storeRun(), file: 'Invoice.php'));

        $this->assertSame(2, $decoded['returned']);
    }

    /**
     * A short list that does not say it is short is indistinguishable from a
     * complete one, and anything counting entries would undercount.
     */
    public function testACutListSaysThatItWasCut(): void
    {
        $decoded = $this->decode($this->tool->execute($this->storeRun(), limit: 2));

        $this->assertSame(2, $decoded['returned']);
        $this->assertTrue($decoded['truncated']);
        $this->assertStringContainsString('raise limit', (string) $decoded['hint']);
    }

    /**
     * `ignorable: false` marks a parse error or internal error rather than a
     * normal, ignoreErrors-suppressible finding, so it must survive the trip
     * through the cache and back out, not just the initial grouping.
     */
    public function testIgnorableSurvivesTheCacheRoundTrip(): void
    {
        $groups = (new ErrorGrouper())->group([
            ['file' => '/app/src/Broken.php', 'line' => 1, 'message' => 'e', 'identifier' => null, 'ignorable' => false],
        ]);
        $id = $this->cache->store(['groups' => $groups]);

        $decoded = $this->decode($this->tool->execute($id));

        $this->assertFalse($decoded['entries'][0]['ignorable']);
    }

    private function storeRun(): string
    {
        $groups = (new ErrorGrouper())->group([
            ['file' => '/app/src/Invoice.php', 'line' => 10, 'message' => 'a', 'identifier' => 'return.type'],
            ['file' => '/app/src/Invoice.php', 'line' => 20, 'message' => 'b', 'identifier' => 'return.type'],
            ['file' => '/app/src/Order.php', 'line' => 30, 'message' => 'c', 'identifier' => 'return.type'],
            ['file' => '/app/src/Order.php', 'line' => 40, 'message' => 'd', 'identifier' => 'argument.type'],
        ]);

        return $this->cache->store(['groups' => $groups]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $encoded): array
    {
        $decoded = ResponseEncoder::decode($encoded);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}
