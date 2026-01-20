<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Runner;

/**
 * Represents the raw result of a PHPStan command execution.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class RunResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output,
        public readonly string $errorOutput,
    ) {
    }
}
