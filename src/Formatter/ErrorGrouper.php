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

use MatesOfMate\PhpStan\DTO\ErrorMessage;

class ErrorGrouper
{
    /**
     * @param ErrorMessage[] $errors
     *
     * @return array<string, ErrorMessage[]>
     */
    public function groupByFile(array $errors): array
    {
        $grouped = [];

        foreach ($errors as $error) {
            $grouped[$error->file][] = $error;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @param ErrorMessage[] $errors
     *
     * @return array<string, ErrorMessage[]>
     */
    public function groupByType(array $errors): array
    {
        $grouped = [];

        foreach ($errors as $error) {
            $type = $this->classifyErrorType($error->message);
            $grouped[$type][] = $error;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @param ErrorMessage[] $errors
     *
     * @return array{auto: list<ErrorMessage>, manual: list<ErrorMessage>, complex: list<ErrorMessage>}
     */
    public function groupByFixability(array $errors): array
    {
        $auto = [];
        $manual = [];
        $complex = [];

        foreach ($errors as $error) {
            $fixability = $this->determineFixability($error->message);

            if ('auto' === $fixability) {
                $auto[] = $error;
            } elseif ('manual' === $fixability) {
                $manual[] = $error;
            } else {
                $complex[] = $error;
            }
        }

        return [
            'auto' => $auto,
            'manual' => $manual,
            'complex' => $complex,
        ];
    }

    private function classifyErrorType(string $message): string
    {
        $patterns = [
            'missing-type' => '/has no (type|return type|value type)/',
            'nullable-return' => '/should return .+ but returns .+\|null/',
            'undefined-property' => '/undefined property/i',
            'undefined-method' => '/undefined method/i',
            'parameter-type' => '/parameter .+ expects .+ but .+ given/i',
            'return-type' => '/return(s|ed) .+ but .+ expected/i',
            'unused-parameter' => '/unused parameter/i',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $message)) {
                return $type;
            }
        }

        return 'other';
    }

    private function determineFixability(string $message): string
    {
        // Auto-fixable: missing type declarations
        if (preg_match('/has no (type|return type)/', $message)) {
            return 'auto';
        }

        // Auto-fixable: nullable return types
        if (preg_match('/should return .+ but returns .+\|null/', $message)) {
            return 'auto';
        }

        // Manual: undefined properties/methods
        if (preg_match('/undefined (property|method)/i', $message)) {
            return 'manual';
        }

        // Manual: type mismatches
        if (preg_match('/(expects|expected) .+ (but|,) .+ given/i', $message)) {
            return 'manual';
        }

        return 'complex';
    }
}
