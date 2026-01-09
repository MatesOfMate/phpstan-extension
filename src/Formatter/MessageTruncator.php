<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Formatter;

use MatesOfMate\Common\Truncator\MessageTruncator as CommonMessageTruncator;
use MatesOfMate\Common\Truncator\MessageTruncatorInterface;

/**
 * Truncates error messages for token efficiency with PHPStan-specific rules.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class MessageTruncator implements MessageTruncatorInterface
{
    private readonly CommonMessageTruncator $truncator;

    public function __construct()
    {
        $this->truncator = new CommonMessageTruncator([
            'Parameter ',
            'Method ',
            'Property ',
            'Call to ',
            'Access to ',
            'Cannot ',
            'Variable ',
        ]);
    }

    public function truncate(string $message, int $maxLength = 80): string
    {
        $message = $this->truncator->truncate($message, $maxLength);

        // PHPStan-specific: Shorten "of method ClassName::methodName()"
        $message = (string) preg_replace('/of method [A-Za-z0-9\\\\]+::/', 'of ', $message);

        return $message;
    }

    public function truncateFileName(string $path, ?string $projectRoot = null): string
    {
        $projectRoot ??= getcwd();
        if (false === $projectRoot) {
            return basename($path);
        }

        if (str_starts_with($path, $projectRoot)) {
            $path = substr($path, \strlen($projectRoot) + 1);
        }

        if (str_starts_with($path, 'src/')) {
            return substr($path, 4);
        }

        return $path;
    }
}
