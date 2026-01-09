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

use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Git\DiffAnalyser;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use Mcp\Capability\Attribute\McpTool;

/**
 * Runs PHPStan only on git-changed files for fast feedback loops.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseDiffTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
        private readonly DiffAnalyser $diffAnalyser,
    ) {
    }

    #[McpTool(
        name: 'phpstan-analyse-diff',
        description: 'Run PHPStan only on files changed since git ref (default: main/master). Ideal for validating current work without analyzing entire codebase. Fast feedback loop for AI-assisted development.',
    )]
    public function execute(
        ?string $baseRef = null,
        ?int $level = null,
        ?string $configuration = null,
    ): string {
        if (!$this->diffAnalyser->isGitRepository()) {
            throw new \RuntimeException('Not a git repository. This tool requires git.');
        }

        // Detect base ref early to provide better error messages
        $detectedRef = $baseRef ?? $this->diffAnalyser->detectDefaultBranch();

        if (null === $detectedRef) {
            throw new \RuntimeException('No baseline branch found (neither "main" nor "master" exists). Cannot perform diff analysis on a repository with no baseline branch. Try creating a baseline branch (e.g., git checkout -b main) or specify an existing commit hash.');
        }

        $changedFiles = $this->diffAnalyser->getChangedPhpFiles($detectedRef);

        if ([] === $changedFiles) {
            return \sprintf(
                "summary{base,changed_files,errors}:\n%s|0|0\n\nNo PHP files changed since %s.",
                $detectedRef,
                $detectedRef,
            );
        }

        $options = ['paths' => $changedFiles];
        if (null !== $configuration) {
            $options['configuration'] = $configuration;
        }
        if (null !== $level) {
            $options['level'] = $level;
        }

        $result = $this->runner->analyse($options);

        // Format output with changed files list
        $output = \sprintf(
            "summary{base,changed_files,errors}:\n%s|%d|%d\n\n",
            $detectedRef,
            \count($changedFiles),
            $result->errorCount,
        );

        $output .= \sprintf("changed_files[%d]:\n", \count($changedFiles));
        foreach ($changedFiles as $file) {
            $output .= $file."\n";
        }

        if ($result->hasErrors()) {
            $output .= "\n".$this->formatter->format($result, 'toon');
        } else {
            $output .= "\nstatus:OK - No errors found in changed files";
        }

        return $output;
    }
}
