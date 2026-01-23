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

/**
 * Formats PHPStan analysis results using TOON (Token-Oriented Object Notation) for token-efficient output.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ToonFormatter
{
    public function format(AnalysisResult $result, string $mode = 'toon'): string
    {
        return match ($mode) {
            'toon' => $this->formatToon($result),
            'summary' => $this->formatSummary($result),
            'detailed' => $this->formatDetailed($result),
            'by-file' => $this->formatByFile($result),
            'by-type' => $this->formatByType($result),
            default => throw new \InvalidArgumentException("Unknown format mode: {$mode}"),
        };
    }

    private function formatToon(AnalysisResult $result): string
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

        return toon($data);
    }

    private function formatSummary(AnalysisResult $result): string
    {
        return toon([
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

        return toon($data);
    }

    private function formatByFile(AnalysisResult $result): string
    {
        $grouped = [];

        foreach ($result->errors as $error) {
            $file = basename((string) $error['file']);
            $grouped[$file][] = $error;
        }

        ksort($grouped);

        $data = [
            'summary' => [
                'files_with_errors' => $result->fileErrorCount,
                'total_errors' => $result->errorCount,
            ],
            'by_file' => $grouped,
        ];

        return toon($data);
    }

    private function formatByType(AnalysisResult $result): string
    {
        $grouped = [];

        foreach ($result->errors as $error) {
            $type = $this->categorizeError($error['message']);
            $grouped[$type][] = [
                'file' => basename((string) $error['file']),
                'line' => $error['line'],
                'message' => $error['message'],
            ];
        }

        ksort($grouped);

        $data = [
            'summary' => [
                'total_errors' => $result->errorCount,
            ],
            'by_type' => $grouped,
        ];

        return toon($data);
    }

    private function categorizeError(string $message): string
    {
        if (str_contains($message, 'has no type')) {
            return 'missing-type';
        }

        if (str_contains($message, 'has no return type')) {
            return 'missing-return-type';
        }

        if (str_contains($message, 'should return') && str_contains($message, '|null')) {
            return 'nullable-return';
        }

        if (str_contains($message, 'undefined property')) {
            return 'undefined-property';
        }

        if (str_contains($message, 'undefined method')) {
            return 'undefined-method';
        }

        if (str_contains($message, 'expects') && str_contains($message, 'given')) {
            return 'type-mismatch';
        }

        return 'other';
    }
}
