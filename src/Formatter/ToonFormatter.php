<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Formatter;

use MatesOfMate\PhpStanExtension\Grouping\ErrorGrouper;
use MatesOfMate\PhpStanExtension\Parser\AnalysisResult;
use Symfony\AI\Mate\Encoding\ResponseEncoder;

/**
 * Formats PHPStan analysis results for compact tool responses.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ToonFormatter
{
    public function __construct(
        private readonly ErrorGrouper $grouper = new ErrorGrouper(),
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $groups
     */
    public function format(AnalysisResult $result, string $mode = 'default', ?string $runId = null, ?array $groups = null): string
    {
        if ($result->parseFailed) {
            return $this->formatParseFailure($result);
        }

        return match ($mode) {
            'default' => $this->formatDefault($result, $runId, $groups ?? $this->grouper->group($result->errors)),
            'summary' => $this->formatSummary($result),
            'detailed' => $this->formatDetailed($result, $runId, $groups ?? $this->grouper->group($result->errors)),
            default => throw new \InvalidArgumentException("Unknown format mode: {$mode}"),
        };
    }

    /**
     * PHPStan's stdout did not decode as JSON. Surfacing the raw output (instead of throwing,
     * as before) is what lets the agent see what PHPStan actually said rather than a bare
     * "Syntax error" with the real diagnosis discarded.
     */
    private function formatParseFailure(AnalysisResult $result): string
    {
        return ResponseEncoder::encode([
            'status' => 'PARSE_ERROR',
            'diagnostics' => $result->diagnostics,
            'raw_output' => $result->rawOutput,
            'error_output' => $result->errorOutput,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $groups
     */
    private function formatDefault(AnalysisResult $result, ?string $runId, array $groups): string
    {
        $data = [
            'summary' => [
                'level' => $result->level ?? 'N/A',
                'files_with_errors' => $result->fileErrorCount,
                'total_errors' => $result->errorCount,
                'time' => null !== $result->executionTime ? round($result->executionTime, 3).'s' : null,
            ],
        ];

        if (0 === $result->errorCount) {
            $data['status'] = 'OK';
        } else {
            $data['groups'] = array_map(
                static fn (array $g): array => [
                    'id' => $g['id'],
                    'count' => $g['count'],
                    'identifier' => $g['identifier'] ?? '(none)',
                    'keyed_by' => $g['keyedBy'],
                    'example' => $g['summary'],
                    'files' => implode(', ', \array_slice(array_keys($g['files']), 0, 3)),
                ],
                $groups
            );

            if (null !== $runId) {
                $data['run'] = $runId;
                $data['next'] = \sprintf('phpstan-analysis-detail --id=%s [--group=g1] for the individual errors', $runId);
            }
        }

        return ResponseEncoder::encode($data);
    }

    private function formatSummary(AnalysisResult $result): string
    {
        return ResponseEncoder::encode([
            'files_with_errors' => $result->fileErrorCount,
            'total_errors' => $result->errorCount,
            'level' => $result->level ?? 'N/A',
            'status' => 0 === $result->errorCount ? 'OK' : 'FAIL',
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $groups
     */
    private function formatDetailed(AnalysisResult $result, ?string $runId, array $groups): string
    {
        $data = [
            'summary' => [
                'level' => $result->level ?? 'N/A',
                'files_with_errors' => $result->fileErrorCount,
                'total_errors' => $result->errorCount,
                'time' => null !== $result->executionTime ? round($result->executionTime, 3).'s' : null,
            ],
        ];

        if (0 === $result->errorCount) {
            $data['status'] = 'OK';
        } else {
            $data['groups'] = array_map(
                static function (array $g): array {
                    $files = [];
                    foreach ($g['members'] as $member) {
                        $file = (string) ($member['file'] ?? '');
                        $files[$file] = ($files[$file] ?? 0) + 1;
                    }

                    return [
                        'id' => $g['id'],
                        'count' => $g['count'],
                        'identifier' => $g['identifier'] ?? '(none)',
                        'keyed_by' => $g['keyedBy'],
                        'example' => $g['summary'],
                        // Full paths, unlike default's base names: this is what
                        // tells two same-named files in different directories apart.
                        'files' => $files,
                    ];
                },
                $groups
            );

            if (null !== $runId) {
                $data['run'] = $runId;
                $data['next'] = \sprintf('phpstan-analysis-detail --id=%s --group=g1 for the individual errors', $runId);
            }
        }

        return ResponseEncoder::encode($data);
    }
}
