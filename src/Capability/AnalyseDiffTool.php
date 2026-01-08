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

class AnalyseDiffTool
{
    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly ToonFormatter $formatter,
        private readonly DiffAnalyser $diffAnalyser,
    ) {
    }

    #[McpTool(
        name: 'phpstan_analyse_diff',
        description: 'Run PHPStan only on files changed since git ref (default: main/master). Ideal for validating current work without analyzing entire codebase. Fast feedback loop for AI-assisted development.',
    )]
    public function execute(
        ?string $baseRef = null,
        ?int $level = null,
        ?string $configuration = null,
    ): string {
        try {
            if (!$this->diffAnalyser->isGitRepository()) {
                return json_encode([
                    'success' => false,
                    'error' => 'Not a git repository. This tool requires git.',
                    'output' => '',
                ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            }

            $changedFiles = $this->diffAnalyser->getChangedPhpFiles($baseRef);

            if ([] === $changedFiles) {
                $detectedRef = $baseRef ?? $this->diffAnalyser->detectDefaultBranch();

                return json_encode([
                    'success' => true,
                    'output' => \sprintf(
                        "summary{base,changed_files,errors}:\n%s|0|0\n\nNo PHP files changed since %s.",
                        $detectedRef,
                        $detectedRef,
                    ),
                    'errorCount' => 0,
                    'filesAnalysed' => 0,
                    'baseRef' => $detectedRef,
                ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            }

            $options = ['paths' => $changedFiles];
            if (null !== $configuration) {
                $options['configuration'] = $configuration;
            }
            if (null !== $level) {
                $options['level'] = $level;
            }

            $result = $this->runner->analyse($options);

            $detectedRef = $baseRef ?? $this->diffAnalyser->detectDefaultBranch();

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

            return json_encode([
                'success' => !$result->hasErrors(),
                'output' => $output,
                'errorCount' => $result->errorCount,
                'filesAnalysed' => \count($changedFiles),
                'baseRef' => $detectedRef,
                'changedFiles' => $changedFiles,
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'output' => '',
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        }
    }
}
