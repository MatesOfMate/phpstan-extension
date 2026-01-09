<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStan\Formatter;

use MatesOfMate\PhpStan\Runner\AnalysisResult;

/**
 * Formats PHPStan analysis results in token-optimized TOON format.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ToonFormatter
{
    public function __construct(
        private readonly MessageTruncator $truncator,
        private readonly ErrorGrouper $grouper,
    ) {
    }

    public function format(AnalysisResult $result, string $mode = 'toon'): string
    {
        return match ($mode) {
            'toon' => $this->formatToon($result),
            'summary' => $this->formatSummary($result),
            'detailed' => $this->formatDetailed($result),
            'by-file' => $this->formatByFile($result),
            'by-type' => $this->formatByType($result),
            default => throw new \InvalidArgumentException("Unknown format mode: {$mode}"),
        };
    }

    private function formatToon(AnalysisResult $result): string
    {
        $output = [];

        // Summary line
        $output[] =
            'summary{level,files_with_errors,total_errors,time}:'
        ;
        $output[] = \sprintf(
            '%s|%d|%d|%.3fs',
            $result->level ?? 'N/A',
            $result->fileErrorCount,
            $result->errorCount,
            $result->executionTime ?? 0,
        );

        if (0 === $result->errorCount) {
            $output[] = 'status:OK';

            return implode("\n", $output);
        }

        $output[] = '';

        // Errors
        $output[] = \sprintf('errors[%d]{file,line,msg,ignorable}:', \count($result->errors));

        foreach ($result->errors as $error) {
            $file = $this->truncator->truncateFileName($error->file);
            $message = $this->truncator->truncate($error->message);
            $ignorable = $error->ignorable ? 'T' : 'F';

            $output[] = \sprintf(
                '%s|%d|%s|%s',
                $file,
                $error->line,
                $message,
                $ignorable,
            );
        }

        return implode("\n", $output);
    }

    private function formatSummary(AnalysisResult $result): string
    {
        $output = [];

        $output[] = 'summary{files,errors,level}:';
        $output[] = \sprintf(
            '%d|%d|%s',
            $result->fileErrorCount,
            $result->errorCount,
            $result->level ?? 'N/A',
        );

        $output[] = 0 === $result->errorCount ? 'status:OK' : 'status:FAIL';

        return implode("\n", $output);
    }

    private function formatDetailed(AnalysisResult $result): string
    {
        $output = [];

        // Summary
        $output[] = 'summary{level,files,errors,time}:';
        $output[] = \sprintf(
            '%s|%d|%d|%.3fs',
            $result->level ?? 'N/A',
            $result->fileErrorCount,
            $result->errorCount,
            $result->executionTime ?? 0,
        );

        if (0 === $result->errorCount) {
            $output[] = 'status:OK';

            return implode("\n", $output);
        }

        $output[] = '';

        // Errors with hints
        $output[] = \sprintf('errors[%d]{file,line,msg,hint}:', \count($result->errors));

        foreach ($result->errors as $error) {
            $file = $this->truncator->truncateFileName($error->file);
            $message = $this->truncator->truncate($error->message, 100);
            $hint = $this->generateFixHint($error->message);

            $output[] = \sprintf(
                '%s|%d|%s|%s',
                $file,
                $error->line,
                $message,
                $hint,
            );
        }

        return implode("\n", $output);
    }

    private function formatByFile(AnalysisResult $result): string
    {
        $output = [];
        $output[] = 'summary{files_with_errors,total_errors}:';
        $output[] = \sprintf('%d|%d', $result->fileErrorCount, $result->errorCount);
        $output[] = '';

        $grouped = $this->grouper->groupByFile($result->errors);

        foreach ($grouped as $file => $errors) {
            $shortFile = $this->truncator->truncateFileName($file);
            $output[] = \sprintf('%s (%d errors):', $shortFile, \count($errors));

            foreach ($errors as $error) {
                $message = $this->truncator->truncate($error->message);
                $output[] = \sprintf('  L%d: %s', $error->line, $message);
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }

    private function formatByType(AnalysisResult $result): string
    {
        $output = [];
        $output[] = 'summary{total_errors}:';
        $output[] = \sprintf('%d', $result->errorCount);
        $output[] = '';

        $grouped = $this->grouper->groupByType($result->errors);

        foreach ($grouped as $type => $errors) {
            $output[] = \sprintf('%s (%d):', $type, \count($errors));

            foreach ($errors as $error) {
                $file = $this->truncator->truncateFileName($error->file);
                $output[] = \sprintf('  %s:%d', $file, $error->line);
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }

    private function generateFixHint(string $message): string
    {
        // Generate smart fix hints based on error patterns
        if (preg_match('/has no type/', $message)) {
            return 'Add type declaration';
        }

        if (preg_match('/has no return type/', $message)) {
            return 'Add return type';
        }

        if (preg_match('/should return .+ but returns .+\|null/', $message)) {
            return 'Add |null to return type or add null check';
        }

        if (preg_match('/undefined property/', $message)) {
            return 'Check property name or add property declaration';
        }

        if (preg_match('/undefined method/', $message)) {
            return 'Check method name or add method';
        }

        if (preg_match('/expects .+ but .+ given/', $message)) {
            return 'Fix parameter type at call site';
        }

        return 'Review and fix error';
    }
}
