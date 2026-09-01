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
use Symfony\AI\Mate\Attribute\MateTool;

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

    /**
     * @param string|null $configuration Path to the PHPStan configuration file. Defaults to auto-detection.
     * @param int|null    $level         PHPStan rule level from 0 (loosest) to 9 (strictest)
     * @param string|null $path          File or directory path to analyse. Defaults to configured project paths.
     * @param string      $mode          output detail level: default, summary, or detailed
     */
    #[MateTool(name: 'phpstan-analyse', title: 'PHPStan Analyse', description: 'Run PHPStan analysis for the project, a directory, or a single file.')]
    public function execute(
        ?string $configuration = null,
        ?int $level = null,
        ?string $path = null,
        string $mode = 'default',
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
