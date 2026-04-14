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

use MatesOfMate\PhpStanExtension\Parser\AnalysisResult;
use Symfony\AI\Mate\Encoding\ResponseEncoder;

/**
 * Formats PHPStan analysis results for compact MCP responses.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ToonFormatter
{
    public function format(AnalysisResult $result, string $mode = 'default'): string
    {
        return match ($mode) {
            'default' => $this->formatDefault($result),
            'summary' => $this->formatSummary($result),
            'detailed' => $this->formatDetailed($result),
            default => throw new \InvalidArgumentException("Unknown format mode: {$mode}"),
        };
    }

    private function formatDefault(AnalysisResult $result): string
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
            $data['errors'] = array_map(
                static fn (array $e): array => [
                    'file' => basename((string) $e['file']),
                    'line' => $e['line'],
                    'message' => $e['message'],
                    'ignorable' => $e['ignorable'],
                ],
                $result->errors
            );
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

    private function formatDetailed(AnalysisResult $result): string
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
            $data['errors'] = array_map(
                static fn (array $e): array => [
                    'file' => $e['file'],
                    'line' => $e['line'],
                    'message' => $e['message'],
                    'ignorable' => $e['ignorable'],
                ],
                $result->errors
            );
        }

        return ResponseEncoder::encode($data);
    }
}
