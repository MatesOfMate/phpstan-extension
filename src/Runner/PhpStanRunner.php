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
use Symfony\Component\Process\Process;

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

    /**
     * @param array<int, string> $customCommand
     */
    public function __construct(
        private readonly ProcessExecutorInterface $executor,
        private readonly ?string $projectRoot = null,
        private readonly array $customCommand = [],
    ) {
    }

    /**
     * @param array<int, string> $args
     */
    public function run(string $command, array $args): RunResult
    {
        $defaultArgs = self::DEFAULT_ARGS[$command] ?? [];

        if ([] !== $this->customCommand) {
            return $this->runCustomCommand($command, $args, $defaultArgs);
        }

        $result = $this->executor->execute('phpstan', [$command, ...$args, ...$defaultArgs], timeout: 300);

        return new RunResult(
            exitCode: $result->exitCode,
            output: $result->output,
            errorOutput: $result->errorOutput,
        );
    }

    /**
     * @param array<int, string> $args
     * @param array<int, string> $defaultArgs
     */
    private function runCustomCommand(string $command, array $args, array $defaultArgs): RunResult
    {
        $process = new Process(
            [...$this->customCommand, $command, ...$args, ...$defaultArgs],
            $this->projectRoot,
        );
        $process->setTimeout(300);
        $process->run();

        return new RunResult(
            exitCode: $process->getExitCode() ?? 1,
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
        );
    }
}
