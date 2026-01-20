<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Runner;

use MatesOfMate\Common\Process\ProcessExecutorInterface;

/**
 * Executes PHPStan analysis and manages process execution.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class PhpStanRunner
{
    /**
     * @var array<string, array<string>>
     */
    private const DEFAULT_ARGS = [
        'analyse' => ['--error-format=json', '--no-progress'],
    ];

    public function __construct(
        private readonly ProcessExecutorInterface $executor,
    ) {
    }

    /**
     * @param array<int, string> $args
     */
    public function run(string $command, array $args): RunResult
    {
        $defaultArgs = self::DEFAULT_ARGS[$command] ?? [];

        $result = $this->executor->execute('phpstan', [$command, ...$args, ...$defaultArgs], timeout: 300);

        return new RunResult(
            exitCode: $result->exitCode,
            output: $result->output,
            errorOutput: $result->errorOutput,
        );
    }
}
