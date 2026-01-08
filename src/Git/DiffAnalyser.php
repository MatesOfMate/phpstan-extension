<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Git;

use MatesOfMate\PhpStan\Runner\ProcessExecutor;

class DiffAnalyser
{
    public function __construct(
        private readonly ProcessExecutor $executor,
    ) {
    }

    /**
     * @return string[]
     */
    public function getChangedPhpFiles(?string $baseRef = null): array
    {
        if (!$this->isGitRepository()) {
            return [];
        }

        $baseRef ??= $this->detectDefaultBranch();

        $args = [
            'git',
            'diff',
            '--name-only',
            '--diff-filter=ACMR',
            $baseRef,
            'HEAD',
            '--',
            '*.php',
        ];

        $result = $this->executor->execute($args);

        if (!$result->isSuccessful()) {
            return [];
        }

        $files = array_filter(
            explode("\n", trim($result->output)),
            fn ($file): bool => '' !== $file && file_exists($file),
        );

        return array_values($files);
    }

    public function detectDefaultBranch(): string
    {
        // Try main first
        $result = $this->executor->execute(['git', 'rev-parse', '--verify', 'main']);
        if ($result->isSuccessful()) {
            return 'main';
        }

        // Fallback to master
        $result = $this->executor->execute(['git', 'rev-parse', '--verify', 'master']);
        if ($result->isSuccessful()) {
            return 'master';
        }

        // Default to main if neither exists
        return 'main';
    }

    public function hasUncommittedChanges(): bool
    {
        if (!$this->isGitRepository()) {
            return false;
        }

        $result = $this->executor->execute(['git', 'diff', '--quiet']);

        return !$result->isSuccessful();
    }

    public function isGitRepository(): bool
    {
        $result = $this->executor->execute(['git', 'rev-parse', '--git-dir']);

        return $result->isSuccessful();
    }
}
