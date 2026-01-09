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
        ?string $configuration = null,
        ?int $level = null,
        ?string $path = null,
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
