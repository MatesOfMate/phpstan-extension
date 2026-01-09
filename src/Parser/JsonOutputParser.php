<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Parser;

use MatesOfMate\PhpStan\Runner\AnalysisResult;

/**
 * Parses PHPStan JSON output into structured data.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class JsonOutputParser
{
    public function parse(string $jsonOutput): AnalysisResult
    {
        $data = json_decode($jsonOutput, true, 512, \JSON_THROW_ON_ERROR);

        $errors = [];
        foreach ($data['files'] ?? [] as $file => $fileData) {
            foreach ($fileData['messages'] ?? [] as $message) {
                $errors[] = new ErrorMessage(
                    file: $file,
                    line: $message['line'] ?? 0,
                    message: $message['message'] ?? '',
                    ignorable: $message['ignorable'] ?? true,
                );
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
}
