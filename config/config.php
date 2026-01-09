<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MatesOfMate\PhpStan\Capability\AnalyseDiffTool;
use MatesOfMate\PhpStan\Capability\AnalyseFileTool;
use MatesOfMate\PhpStan\Capability\AnalyseTool;
use MatesOfMate\PhpStan\Capability\ClearCacheTool;
use MatesOfMate\PhpStan\Capability\ConfigResource;
use MatesOfMate\PhpStan\Formatter\ErrorGrouper;
use MatesOfMate\PhpStan\Formatter\MessageTruncator;
use MatesOfMate\PhpStan\Formatter\ToonFormatter;
use MatesOfMate\PhpStan\Git\DiffAnalyser;
use MatesOfMate\PhpStan\Parser\ConfigurationDetector;
use MatesOfMate\PhpStan\Parser\JsonOutputParser;
use MatesOfMate\PhpStan\Parser\NeonParser;
use MatesOfMate\PhpStan\Process\PhpStanProcessExecutor;
use MatesOfMate\PhpStan\Runner\PhpStanRunner;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // MCP Tools - automatically discovered by #[McpTool] attribute
    $services->set(AnalyseTool::class);
    $services->set(AnalyseFileTool::class);
    $services->set(AnalyseDiffTool::class);
    $services->set(ClearCacheTool::class);

    // MCP Resources - automatically discovered by #[McpResource] attribute
    $services->set(ConfigResource::class);

    // Runner layer
    $services->set(PhpStanRunner::class);
    $services->set(PhpStanProcessExecutor::class);

    // Parser layer
    $services->set(JsonOutputParser::class);
    $services->set(ConfigurationDetector::class);
    $services->set(NeonParser::class);

    // Formatter layer
    $services->set(ToonFormatter::class);
    $services->set(MessageTruncator::class);
    $services->set(ErrorGrouper::class);

    // Git layer
    $services->set(DiffAnalyser::class);
};
