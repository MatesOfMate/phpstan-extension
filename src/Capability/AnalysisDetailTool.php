<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Capability;

use MatesOfMate\Common\Cache\RunCache;
use Symfony\AI\Mate\Attribute\MateTool;
use Symfony\AI\Mate\Encoding\ResponseEncoder;

/**
 * Returns the errors behind a grouped `phpstan-analyse` response, without
 * re-running the analysis.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalysisDetailTool
{
    public function __construct(
        private readonly RunCache $cache,
    ) {
    }

    /**
     * @param string      $id    the run id returned by phpstan-analyse
     * @param string|null $group return every error in one group, for example g1
     * @param string|null $file  return only errors in files whose path contains this string
     * @param int         $limit maximum number of errors to return
     */
    #[MateTool(name: 'phpstan-analysis-detail', title: 'PHPStan Analysis Detail', description: 'Show the individual errors behind a grouped phpstan-analyse result, by run id. Narrow with the group or file argument.')]
    public function execute(
        string $id,
        ?string $group = null,
        ?string $file = null,
        int $limit = 50,
    ): string {
        $run = $this->cache->load($id);

        if (null === $run) {
            $known = $this->cache->ids();

            return ResponseEncoder::encode([
                'error' => "Unknown run id: {$id}",
                'hint' => [] === $known
                    ? 'No analyses are cached yet. Run phpstan-analyse first.'
                    : 'Cached runs, newest first: '.implode(', ', \array_slice($known, 0, 5)),
            ]);
        }

        $groups = $run['groups'] ?? [];
        if (null !== $group) {
            $groups = array_values(array_filter($groups, static fn (array $g): bool => $g['id'] === $group));
        }

        $entries = [];
        $truncated = false;
        foreach ($groups as $g) {
            foreach ($g['members'] ?? [] as $member) {
                if (null !== $file && !str_contains((string) ($member['file'] ?? ''), $file)) {
                    continue;
                }

                if (\count($entries) >= $limit) {
                    $truncated = true;
                    break 2;
                }

                $entries[] = [
                    'group' => $g['id'],
                    'identifier' => $g['identifier'],
                    'file' => $member['file'] ?? null,
                    'line' => $member['line'] ?? null,
                    'message' => $member['message'] ?? '',
                    'ignorable' => $member['ignorable'] ?? true,
                ];
            }
        }

        $payload = ['run' => $id, 'returned' => \count($entries), 'entries' => $entries];

        // Say so when the list was cut. A silently short list is indistinguishable
        // from a complete one, and anything counting entries would undercount.
        if ($truncated) {
            $payload['truncated'] = true;
            $payload['hint'] = 'More errors matched than the limit; raise limit or narrow with group/file.';
        }

        return ResponseEncoder::encode($payload);
    }
}
