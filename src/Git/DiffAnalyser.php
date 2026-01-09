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

use MatesOfMate\PhpStan\Process\PhpStanProcessExecutor;

/**
 * Analyzes git diffs to identify changed PHP files for targeted PHPStan analysis.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class DiffAnalyser
{
    public function __construct(
        private readonly PhpStanProcessExecutor $executor,
    ) {
    }

    /**
     * @return string[]
     */
    public function getChangedPhpFiles(string $baseRef): array
    {
        if (!$this->isGitRepository()) {
            return [];
        }

        $args = [
            'diff',
            '--name-only',
            '--diff-filter=ACMR',
            $baseRef,
            'HEAD',
            '--',
            '*.php',
        ];

        $result = $this->executor->execute('git', $args, usePhpBinary: false);

        if (!$result->isSuccessful()) {
            return [];
        }

        $files = array_filter(
            explode("\n", trim($result->output)),
            fn ($file): bool => '' !== $file && file_exists($file),
        );

        return array_values($files);
    }

    public function detectDefaultBranch(): ?string
    {
        // Try main first
        if ($this->refExists('main')) {
            return 'main';
        }

        // Fallback to master
        if ($this->refExists('master')) {
            return 'master';
        }

        // No baseline branch exists
        return null;
    }

    public function hasUncommittedChanges(): bool
    {
        if (!$this->isGitRepository()) {
            return false;
        }

        $result = $this->executor->execute('git', ['diff', '--quiet'], usePhpBinary: false);

        return !$result->isSuccessful();
    }

    public function isGitRepository(): bool
    {
        $result = $this->executor->execute('git', ['rev-parse', '--git-dir'], usePhpBinary: false);

        return $result->isSuccessful();
    }

    private function refExists(string $ref): bool
    {
        $result = $this->executor->execute('git', ['rev-parse', '--verify', '--quiet', $ref], usePhpBinary: false);

        return $result->isSuccessful();
    }
}
