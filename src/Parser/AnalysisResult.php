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

/**
 * Represents the result of a PHPStan analysis with errors and metrics.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalysisResult
{
    /**
     * @param array<int, array<string, mixed>> $errors
     * @param list<string>                     $diagnostics
     */
    public function __construct(
        public readonly int $errorCount,
        public readonly int $fileErrorCount,
        public readonly array $errors,
        public readonly ?int $level,
        public readonly ?float $executionTime,
        public readonly ?string $memoryUsage,
        public readonly bool $parseFailed = false,
        public readonly ?string $rawOutput = null,
        public readonly ?string $errorOutput = null,
        public readonly array $diagnostics = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }
}
