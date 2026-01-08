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

class NeonParser
{
    /**
     * @return array<string, mixed>
     */
    public function parseFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if (false === $content) {
            return [];
        }

        // Simple MVP implementation - parse basic key-value pairs
        $data = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if ('' === $line) {
                continue;
            }
            if (str_starts_with($line, '#')) {
                continue;
            }

            // Parse simple key: value pairs
            if (preg_match('/^(\w+):\s*(.+)$/', $line, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);

                // Handle quoted strings
                if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
                    || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getValue(array $data, string $key): mixed
    {
        // Support dot-notation for nested keys
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (!\is_array($value) || !isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
