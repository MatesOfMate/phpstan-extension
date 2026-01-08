<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Runner;

use MatesOfMate\PhpStan\DTO\ProcessResult;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ProcessExecutor
{
    /**
     * @param array<int, string> $command
     */
    public function execute(array $command, int $timeout = 300): ProcessResult
    {
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->run();

        return new ProcessResult(
            exitCode: $process->getExitCode() ?? 1,
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
        );
    }

    public function findPhpStanBinary(): ?string
    {
        // Try vendor/bin/phpstan first (most common)
        $localPath = getcwd().'/vendor/bin/phpstan';
        if (file_exists($localPath)) {
            return $localPath;
        }

        // Try global installation
        $finder = new ExecutableFinder();

        return $finder->find('phpstan');
    }

    /**
     * @return array<int, string>
     */
    public function buildPhpStanCommand(string $phpStanScript): array
    {
        // Use the current PHP binary to execute PHPStan
        return [\PHP_BINARY, $phpStanScript];
    }
}
