<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Process;

use MatesOfMate\Common\Process\ProcessExecutor as CommonProcessExecutor;
use MatesOfMate\Common\Process\ProcessExecutorInterface;
use MatesOfMate\Common\Process\ProcessResult;

/**
 * Executes PHPStan CLI commands with proper binary detection.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class PhpStanProcessExecutor implements ProcessExecutorInterface
{
    private readonly CommonProcessExecutor $executor;

    public function __construct()
    {
        $cwd = getcwd();
        $vendorPaths = false !== $cwd ? [
            $cwd.'/vendor/bin/phpstan',
            $cwd.'/vendor/phpstan/phpstan/phpstan',
        ] : [];

        $this->executor = new CommonProcessExecutor($vendorPaths);
    }

    public function execute(string $binaryName, array $args = [], int $timeout = 300, bool $usePhpBinary = true): ProcessResult
    {
        return $this->executor->execute($binaryName, $args, $timeout, $usePhpBinary);
    }
}
