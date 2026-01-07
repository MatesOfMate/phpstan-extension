<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\ExampleExtension\Capability;

use Mcp\Capability\Attribute\McpTool;

/**
 * Example tool demonstrating the basic structure of an AI Mate tool.
 *
 * Replace this with your actual implementation.
 */
class ExampleTool
{
    /**
     * Tools are invoked as callables.
     *
     * You can accept parameters that the AI will provide:
     * public function execute(string $name): string
     *
     * Use constructor injection for dependencies.
     */
    #[McpTool(
        name: 'example-hello',
        description: 'A simple example tool that returns a greeting. Use this as a template for your own tools.'
    )]
    public function execute(): string
    {
        return json_encode([
            'message' => 'Hello from MatesOfMate!',
            'hint' => 'Replace this tool with your actual implementation.',
        ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
    }
}
