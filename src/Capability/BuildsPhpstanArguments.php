<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Capability;

use MatesOfMate\PhpStan\Config\ConfigurationDetector;

/**
 * Provides helper methods for building PHPStan command arguments.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
trait BuildsPhpstanArguments
{
    private readonly ConfigurationDetector $configDetector;

    /**
     * @param string|list<string>|null $path
     *
     * @return array<string>
     */
    private function buildPhpstanArgs(
        string|array|null $path = null,
        ?string $configuration = null,
        ?int $level = null,
    ): array {
        $args = [];

        $config = $configuration ?? $this->configDetector->detect();
        if ($config) {
            $args[] = '--configuration';
            $args[] = $config;
        }

        if (null !== $level) {
            $args[] = '--level';
            $args[] = (string) $level;
        }

        if (null !== $path) {
            if (\is_array($path)) {
                $args[] = '--';
                $args = [...$args, ...$path];
            } else {
                $args[] = $path;
            }
        }

        return $args;
    }
}
