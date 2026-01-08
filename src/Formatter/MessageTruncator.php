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

class MessageTruncator
{
    public function truncate(string $message, int $maxLength = 80): string
    {
        // Remove common prefixes for token efficiency
        $prefixes = [
            'Parameter ',
            'Method ',
            'Property ',
            'Call to ',
            'Access to ',
            'Cannot ',
            'Variable ',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($message, $prefix)) {
                $message = substr($message, \strlen($prefix));
                break;
            }
        }

        // Shorten fully qualified class names
        $message = preg_replace('/\\\\([A-Z][a-z]+)\\\\/', '$1\\', $message) ?? $message;

        // Shorten "of method ClassName::methodName()"
        $message = preg_replace('/of method [A-Za-z0-9\\\\]+::/', 'of ', $message) ?? $message;

        // Truncate if still too long
        if (\strlen($message) > $maxLength) {
            return substr($message, 0, $maxLength - 3).'...';
        }

        return $message;
    }

    public function truncateFileName(string $path, ?string $projectRoot = null): string
    {
        if (null === $projectRoot) {
            $cwd = getcwd();
            $projectRoot = false !== $cwd ? $cwd : '';
        }

        // Remove project root prefix
        if ('' !== $projectRoot && str_starts_with($path, $projectRoot)) {
            $path = substr($path, \strlen($projectRoot) + 1);
        }

        // Remove src/ prefix if present
        if (str_starts_with($path, 'src/')) {
            return substr($path, 4);
        }

        return $path;
    }
}
