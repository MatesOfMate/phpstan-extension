<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\DTO;

readonly class AnalysisResult
{
    /**
     * @param ErrorMessage[] $errors
     */
    public function __construct(
        public int $errorCount,
        public int $fileErrorCount,
        public array $errors,
        public ?int $level,
        public ?float $executionTime,
        public ?string $memoryUsage,
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }
}
