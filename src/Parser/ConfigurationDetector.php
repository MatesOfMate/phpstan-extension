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

class ConfigurationDetector
{
    private const CONFIG_FILES = [
        'phpstan.neon',
        'phpstan.neon.dist',
        'phpstan.dist.neon',
    ];

    public function detect(string $projectRoot): ?string
    {
        foreach (self::CONFIG_FILES as $configFile) {
            $path = $projectRoot.'/'.$configFile;
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function getConfiguredLevel(string $configFile): ?int
    {
        if (!file_exists($configFile)) {
            return null;
        }

        $content = file_get_contents($configFile);
        if (false === $content) {
            return null;
        }

        // Simple regex extraction for MVP
        if (preg_match('/level:\s*(\d+|max)/i', $content, $matches)) {
            return 'max' === strtolower($matches[1]) ? 9 : (int) $matches[1];
        }

        return null;
    }
}
