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
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Parser\JsonOutputParser;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

/**
 * Runs PHPStan analysis on a specific file for quick validation.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseFileTool
{
    use BuildsPhpstanArguments;

    public function __construct(
        private readonly PhpStanRunner $runner,
        private readonly JsonOutputParser $parser,
        private readonly ToonFormatter $formatter,
        private readonly ConfigurationDetector $configDetector,
    ) {
    }

    #[McpTool(
        name: 'phpstan-analyse-file',
        description: 'Run PHPStan analysis on a specific file. Returns token-optimized TOON format. Available modes: "toon" (compact format), "summary" (totals only), "detailed" (full messages). Use for: validating changes to a single file, debugging specific file issues, focused analysis.',
    )]
    public function execute(
        #[Schema(
            description: 'Path to the PHP file to analyze (relative to project root)',
            pattern: '\.php$'
        )]
        ?string $file = null,
        #[Schema(
            description: 'Path to PHPStan configuration file (defaults to auto-detection)'
        )]
        ?string $configuration = null,
        #[Schema(
            description: 'PHPStan rule level (0-9, higher is stricter)',
            minimum: 0,
            maximum: 9
        )]
        ?int $level = null,
        #[Schema(
            description: 'Output format mode',
            enum: ['toon', 'summary', 'detailed']
        )]
        string $mode = 'toon',
    ): string {
        if (null === $file) {
            throw new \InvalidArgumentException('The "file" parameter is required for phpstan-analyse-file tool.');
        }

        $args = $this->buildPhpstanArgs(
            path: $file,
            configuration: $configuration,
            level: $level,
        );

        $runResult = $this->runner->run('analyse', $args);
        $analysisResult = $this->parser->parse($runResult);

        return $this->formatter->format($analysisResult, $mode);
    }
}
