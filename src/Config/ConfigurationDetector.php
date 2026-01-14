<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Config;

use MatesOfMate\Common\Config\ConfigurationDetector as CommonConfigDetector;
use MatesOfMate\Common\Config\ConfigurationDetectorInterface;

/**
 * Detects PHPStan configuration files in project directories.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ConfigurationDetector implements ConfigurationDetectorInterface
{
    private readonly CommonConfigDetector $detector;

    public function __construct()
    {
        $this->detector = new CommonConfigDetector([
            'phpstan.neon',
            'phpstan.neon.dist',
            'phpstan.dist.neon',
        ]);
    }

    public function detect(?string $projectRoot = null): ?string
    {
        return $this->detector->detect($projectRoot);
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
