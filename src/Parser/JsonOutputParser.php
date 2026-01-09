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

use MatesOfMate\Common\Truncator\MessageTruncator;
use MatesOfMate\PhpStan\Runner\RunResult;

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

    public function parse(RunResult $runResult): AnalysisResult
    {
        $data = json_decode($runResult->output, true, 512, \JSON_THROW_ON_ERROR);

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
}
