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

readonly class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $output,
        public string $errorOutput,
    ) {
    }

    public function isSuccessful(): bool
    {
        return 0 === $this->exitCode;
    }
}
