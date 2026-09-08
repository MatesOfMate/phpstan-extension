<?php

/*
 * This file is part of the MatesOfMate Organisation.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MatesOfMate\PhpStanExtension\Grouping;

/**
 * Collapses PHPStan errors that share a rule into groups.
 *
 * PHPStan already ships the grouping key: every error carries an `identifier`
 * such as `return.type` or `function.alreadyNarrowedType`, which names the rule
 * that produced it. That is a better key than anything derived from the message
 * text, so it is used first and message fingerprinting is only the fallback for
 * output that predates identifiers or omits them.
 *
 * @internal
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ErrorGrouper
{
    /**
     * @param array<int, array<string, mixed>> $errors
     *
     * @return array<int, array<string, mixed>> groups, largest first
     */
    public function group(array $errors): array
    {
        $groups = [];

        foreach ($errors as $error) {
            $identifier = $this->identifierOf($error);
            $message = (string) ($error['message'] ?? '');
            $key = null !== $identifier ? 'id:'.$identifier : 'fp:'.$this->fingerprint($message);

            $groups[$key] ??= [
                'identifier' => $identifier,
                'keyedBy' => null !== $identifier ? 'identifier' : 'fingerprint',
                'summary' => $message,
                'count' => 0,
                'files' => [],
                'members' => [],
            ];

            ++$groups[$key]['count'];
            $file = basename((string) ($error['file'] ?? ''));
            $groups[$key]['files'][$file] = ($groups[$key]['files'][$file] ?? 0) + 1;
            $groups[$key]['members'][] = [
                'file' => $error['file'] ?? null,
                'line' => $error['line'] ?? null,
                'message' => $message,
                'ignorable' => $error['ignorable'] ?? true,
            ];
        }

        $groups = array_values($groups);
        usort($groups, static fn (array $a, array $b): int => $b['count'] <=> $a['count'] ?: strcmp((string) $a['identifier'], (string) $b['identifier']));

        foreach ($groups as $i => &$group) {
            $group['id'] = 'g'.($i + 1);
        }

        return $groups;
    }

    public function fingerprint(string $message): string
    {
        $s = $message;
        $s = preg_replace('/\b[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\b/i', '<uuid>', $s) ?? $s;
        $s = preg_replace('/\b(?:0x)?[0-9a-f]{12,}\b/i', '<hash>', $s) ?? $s;
        $s = preg_replace('/"[^"]*"|\'[^\']*\'/', '<str>', $s) ?? $s;
        // Symbol names identify the instance, not the rule: "Method A::x()
        // should return int but returns string" and the same sentence about
        // B::y() are one problem reported twice.
        $s = preg_replace('/[A-Za-z_][A-Za-z0-9_\\\\]*::[A-Za-z_][A-Za-z0-9_]*\(\)/', '<method>', $s) ?? $s;
        $s = preg_replace('/\$[A-Za-z_][A-Za-z0-9_]*/', '<var>', $s) ?? $s;
        // \b finds no boundary directly before a leading "/", but does after a
        // word character further in, e.g. "/app/src/Foo.php" -> "/app<path>".
        // Anchor on start-of-string or whitespace instead.
        $s = preg_replace('#(?:^|(?<=\s))(?:/|[A-Za-z]:\\\\)[^\s:,)]+(?::\d+)?#', '<path>', $s) ?? $s;
        $s = preg_replace('/\b\d+(?:\.\d+)?\b/', '<num>', $s) ?? $s;
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return trim($s);
    }

    /**
     * @param array<string, mixed> $error
     */
    private function identifierOf(array $error): ?string
    {
        $identifier = $error['identifier'] ?? null;

        return \is_string($identifier) && '' !== $identifier ? $identifier : null;
    }
}
