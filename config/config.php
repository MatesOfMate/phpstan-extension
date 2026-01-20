<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MatesOfMate\Common\Process\ProcessExecutor;
use MatesOfMate\PhpStanExtension\Capability\AnalyseFileTool;
use MatesOfMate\PhpStanExtension\Capability\AnalyseTool;
use MatesOfMate\PhpStanExtension\Capability\ClearCacheTool;
use MatesOfMate\PhpStanExtension\Capability\ConfigResource;
use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use MatesOfMate\PhpStanExtension\Formatter\ToonFormatter;
use MatesOfMate\PhpStanExtension\Parser\JsonOutputParser;
use MatesOfMate\PhpStanExtension\Parser\NeonParser;
use MatesOfMate\PhpStanExtension\Runner\PhpStanRunner;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Core infrastructure
    $services->set('matesofmate_phpstan.process_executor', ProcessExecutor::class)
        ->arg('$vendorPaths', ['%mate.root_dir%/vendor/bin/phpstan']);
    $services->set(PhpStanRunner::class)
        ->arg('$executor', service('matesofmate_phpstan.process_executor'));

    $services->set(JsonOutputParser::class);
    $services->set(ConfigurationDetector::class);
    $services->set(NeonParser::class);

    $services->set(ToonFormatter::class);

    // Tools - automatically discovered by #[McpTool] attribute
    $services->set(AnalyseTool::class);
    $services->set(AnalyseFileTool::class);
    $services->set(ClearCacheTool::class);

    // Resources - automatically discovered by #[McpResource] attribute
    $services->set(ConfigResource::class);
};
