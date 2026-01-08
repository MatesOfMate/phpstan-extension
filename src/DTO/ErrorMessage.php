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

readonly class ErrorMessage
{
    public function __construct(
        public string $file,
        public int $line,
        public string $message,
        public bool $ignorable,
    ) {
    }
}
