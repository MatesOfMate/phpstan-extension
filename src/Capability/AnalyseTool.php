<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Capability;

use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use MatesOfMate\PhpStanExtension\Formatter\ToonFormatter;
use MatesOfMate\PhpStanExtension\Parser\JsonOutputParser;
use MatesOfMate\PhpStanExtension\Runner\PhpStanRunner;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

/**
 * Runs PHPStan static analysis with token-optimized output.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class AnalyseTool
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
        name: 'phpstan-analyse',
        description: 'Run PHPStan static analysis with token-optimized TOON output. Available modes: "toon" (compact format), "summary" (totals only), "detailed" (full messages), "by-file" (grouped by file), "by-type" (grouped by error type). Use for: checking code quality, finding type errors, validating changes.',
    )]
    public function execute(
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
            description: 'Path or directory to analyze (defaults to configured paths)'
        )]
        ?string $path = null,
        #[Schema(
            description: 'Output format mode',
            enum: ['toon', 'summary', 'detailed', 'by-file', 'by-type']
        )]
        string $mode = 'toon',
    ): string {
        $args = $this->buildPhpstanArgs(
            path: $path,
            configuration: $configuration,
            level: $level,
        );

        $runResult = $this->runner->run('analyse', $args);
        $analysisResult = $this->parser->parse($runResult);

        return $this->formatter->format($analysisResult, $mode);
    }
}
