<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Parser;

use MatesOfMate\Common\Truncator\MessageTruncator;
use MatesOfMate\PhpStanExtension\Runner\RunResult;

/**
 * Parses PHPStan JSON output into structured data.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class JsonOutputParser
{
    public function __construct(
        private readonly MessageTruncator $truncator = new MessageTruncator([
            'Parameter ',
            'Method ',
            'Property ',
            'Call to ',
            'Access to ',
            'Cannot ',
            'Variable ',
        ]),
    ) {
    }

    /**
     * PHPStan's `--error-format=json` writes a single JSON object to stdout, but nothing
     * guarantees that object is the only thing on stdout: a PHPStan extension can print its
     * own warning before it, or trailing text can follow it. Extracting the outermost braces
     * before decoding survives both; a genuinely broken payload still falls back to the raw
     * output instead of losing it to an uncaught exception.
     */
    public function parse(RunResult $runResult): AnalysisResult
    {
        $json = $this->extractJsonObject($runResult->output);

        try {
            if (null === $json) {
                throw new \JsonException('No JSON object found in output.');
            }

            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->parseFailure($runResult, 'Could not parse PHPStan JSON output; raw output is included.');
        }

        if (!\is_array($data)) {
            return $this->parseFailure($runResult, 'PHPStan JSON output did not contain an object.');
        }

        $errors = [];
        foreach ($data['files'] ?? [] as $file => $fileData) {
            foreach ($fileData['messages'] ?? [] as $message) {
                $errors[] = [
                    'file' => $file,
                    'line' => $message['line'] ?? 0,
                    'message' => $this->truncator->truncate($message['message'] ?? '', 200),
                    'ignorable' => $message['ignorable'] ?? true,
                ];
            }
        }

        return new AnalysisResult(
            errorCount: \count($errors),
            fileErrorCount: $data['totals']['file_errors'] ?? 0,
            errors: $errors,
            level: null,
            executionTime: null,
            memoryUsage: null,
        );
    }

    private function parseFailure(RunResult $runResult, string $diagnostic): AnalysisResult
    {
        return new AnalysisResult(
            errorCount: 0,
            fileErrorCount: 0,
            errors: [],
            level: null,
            executionTime: null,
            memoryUsage: null,
            parseFailed: true,
            rawOutput: $this->truncator->truncate($runResult->output, 4000),
            errorOutput: $this->truncator->truncate($runResult->errorOutput, 2000),
            diagnostics: [$diagnostic],
        );
    }

    /**
     * Slices the outermost `{ … }` from the output. PHPStan's JSON payload is always a single
     * top-level object, so the first `{` to the last `}` covers a leading warning line, a
     * trailing one, or both, without needing to understand what surrounds it.
     */
    private function extractJsonObject(string $output): ?string
    {
        $start = strpos($output, '{');
        $end = strrpos($output, '}');

        if (false === $start || false === $end || $end < $start) {
            return null;
        }

        return substr($output, $start, $end - $start + 1);
    }
}
