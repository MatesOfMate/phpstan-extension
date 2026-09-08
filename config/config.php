<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MatesOfMate\Common\Cache\RunCache;
use MatesOfMate\Common\Process\ProcessExecutor;
use MatesOfMate\PhpStanExtension\Capability\AnalyseTool;
use MatesOfMate\PhpStanExtension\Capability\AnalysisDetailTool;
use MatesOfMate\PhpStanExtension\Capability\ClearCacheTool;
use MatesOfMate\PhpStanExtension\Capability\ConfigResource;
use MatesOfMate\PhpStanExtension\Config\ConfigurationDetector;
use MatesOfMate\PhpStanExtension\Formatter\ToonFormatter;
use MatesOfMate\PhpStanExtension\Grouping\ErrorGrouper;
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

    $container->parameters()->set('matesofmate_phpstan.custom_command', []);

    // Core infrastructure
    $services->set('matesofmate_phpstan.process_executor', ProcessExecutor::class)
        ->arg('$vendorPaths', ['%mate.root_dir%/vendor/bin/phpstan']);
    $services->set(PhpStanRunner::class)
        ->arg('$executor', service('matesofmate_phpstan.process_executor'))
        ->arg('$projectRoot', '%mate.root_dir%')
        ->arg('$customCommand', '%matesofmate_phpstan.custom_command%');

    $services->set(JsonOutputParser::class);
    $services->set(ErrorGrouper::class);
    $services->set('matesofmate_phpstan.run_cache', RunCache::class)
        ->arg('$cacheDir', '%mate.cache_dir%')
        ->arg('$namespace', 'phpstan-runs')
        ->arg('$keep', 20);
    $services->set(ConfigurationDetector::class);
    $services->set(NeonParser::class);

    $services->set(ToonFormatter::class);

    // Tools - automatically discovered by #[MateTool] attribute
    $services->set(AnalyseTool::class)
        ->arg('$cache', service('matesofmate_phpstan.run_cache'));
    $services->set(AnalysisDetailTool::class)
        ->arg('$cache', service('matesofmate_phpstan.run_cache'));
    $services->set(ClearCacheTool::class);

    // Resources - automatically discovered by #[MateResource] attribute
    $services->set(ConfigResource::class);
};
