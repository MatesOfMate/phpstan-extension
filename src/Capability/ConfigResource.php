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
use Mcp\Capability\Attribute\McpResource;

/**
 * Provides PHPStan configuration information as an MCP resource.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ConfigResource
{
    public function __construct(
        private readonly ConfigurationDetector $configDetector,
    ) {
    }

    /**
     * @return array{uri: string, mimeType: string, text: string}
     */
    #[McpResource(
        uri: 'phpstan://config',
        name: 'phpstan_configuration',
        description: 'PHPStan project configuration details in TOON format. Provides project root, config file path, configured level, and full config content. Use for: understanding project setup, checking configured analysis level, locating configuration files, troubleshooting configuration issues.',
        mimeType: 'text/plain',
    )]
    public function getConfiguration(): array
    {
        $projectRoot = getcwd();
        if (false === $projectRoot) {
            throw new \RuntimeException('Unable to determine current working directory');
        }

        $configPath = $this->configDetector->detect($projectRoot);

        $data = [
            'project_root' => $projectRoot,
            'config_file' => $configPath,
            'config_exists' => null !== $configPath,
        ];

        if (null !== $configPath) {
            $data['configured_level'] = $this->configDetector->getConfiguredLevel($configPath);
            $data['config_content'] = file_exists($configPath) ? file_get_contents($configPath) : null;
        }

        return [
            'uri' => 'phpstan://config',
            'mimeType' => 'text/plain',
            'text' => toon($data),
        ];
    }
}
