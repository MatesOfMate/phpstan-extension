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

use MatesOfMate\PhpStan\Parser\ConfigurationDetector;
use MatesOfMate\PhpStan\Parser\JsonOutputParser;
use MatesOfMate\PhpStan\Process\PhpStanProcessExecutor;

/**
 * Executes PHPStan analysis and manages process execution.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class PhpStanRunner
{
    public function __construct(
        private readonly PhpStanProcessExecutor $executor,
        private readonly JsonOutputParser $parser,
        private readonly ConfigurationDetector $configDetector,
    ) {
    }

    /**
     * @param array{configuration?: string, level?: int, path?: string, paths?: string[], timeout?: int, memoryLimit?: string} $options
     */
    public function analyse(array $options): AnalysisResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $args = $this->buildCommandArgs($options);
        $result = $this->executor->execute('phpstan', $args, $options['timeout'] ?? 300);

        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage(true) - $startMemory;

        // PHPStan returns exit code 1 when there are errors, but that's expected
        if (!$result->isSuccessful() && '' === $result->output) {
            throw new \RuntimeException('PHPStan execution failed: '.$result->errorOutput);
        }

        $analysisResult = $this->parser->parse($result->output);

        // Add timing and memory info
        return new AnalysisResult(
            errorCount: $analysisResult->errorCount,
            fileErrorCount: $analysisResult->fileErrorCount,
            errors: $analysisResult->errors,
            level: $options['level'] ?? $this->detectLevelFromConfig($options['configuration'] ?? null),
            executionTime: $executionTime,
            memoryUsage: $this->formatBytes($memoryUsage),
        );
    }

    public function clearCache(?string $configuration = null): void
    {
        $args = ['clear-result-cache'];

        if (null !== $configuration) {
            $args[] = '-c';
            $args[] = $configuration;
        }

        $result = $this->executor->execute('phpstan', $args);

        if (!$result->isSuccessful()) {
            throw new \RuntimeException('Failed to clear PHPStan cache: '.$result->errorOutput);
        }
    }

    /**
     * @param array{configuration?: string, level?: int, path?: string, paths?: string[], memoryLimit?: string} $options
     *
     * @return array<int, string>
     */
    private function buildCommandArgs(array $options): array
    {
        $args = ['analyse'];
        $args[] = '--error-format=json';
        $args[] = '--no-progress';

        if (isset($options['level'])) {
            $args[] = '--level='.$options['level'];
        }

        if (isset($options['configuration'])) {
            $args[] = '-c';
            $args[] = $options['configuration'];
        } else {
            $cwd = getcwd();
            if (false !== $cwd) {
                $config = $this->configDetector->detect($cwd);
                if (null !== $config) {
                    $args[] = '-c';
                    $args[] = $config;
                }
            }
        }

        $args[] = '--memory-limit='.($options['memoryLimit'] ?? '512M');

        // Add paths to analyze
        if (isset($options['paths']) && \is_array($options['paths'])) {
            foreach ($options['paths'] as $path) {
                $args[] = $path;
            }
        } elseif (isset($options['path'])) {
            $args[] = $options['path'];
        }

        return $args;
    }

    private function detectLevelFromConfig(?string $configPath): ?int
    {
        if (null === $configPath) {
            $cwd = getcwd();
            if (false === $cwd) {
                return null;
            }

            $configPath = $this->configDetector->detect($cwd);
        }

        if (null === $configPath) {
            return null;
        }

        return $this->configDetector->getConfiguredLevel($configPath);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = (int) min($pow, \count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).$units[$pow];
    }
}
