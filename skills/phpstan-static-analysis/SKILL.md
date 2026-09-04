---
name: phpstan-static-analysis
description: Run PHPStan through Mate and interpret its findings, for the whole project, one directory, or a single file. Use when static analysis should be run, a type error has to be chased down, or a change must be shown not to have introduced new errors, in a project that already has a PHPStan configuration. Not for running tests (phpunit test runs), applying refactors (rector refactoring), or a standalone script/algorithm task with no PHPStan setup.
---

# PHPStan analysis

Runs PHPStan through Mate's CLI with `--error-format=json` and returns the parsed errors. Two tools and one resource:

- `phpstan-analyse` (opt `path`, `level`, `configuration`, `mode`): analyses a file, a directory, or the configured project paths.
- `phpstan-clear-cache` (opt `configuration`): drops the result cache.
- `phpstan://config`: project root, detected config file, `configured_level`, and the raw config content. `configuration` is auto-detected as `phpstan.neon`, `phpstan.neon.dist`, or `phpstan.dist.neon` in the project root.

These commands accept `--format`: `json` to parse the result, `toon` (when `helgesverre/toon` is installed) for the smallest context footprint.

## Workflow

1. Analyse what you touched, not the project: `vendor/bin/mate tools:call phpstan-analyse --path=src/Service --mode=detailed`. A whole-project run on every iteration is slow and buries your errors among pre-existing ones.
2. Fix the causes, then re-run the same scope so the before and after counts are comparable.
3. Once, before handing work back, run without `path` so the configured project paths are analysed.

`level` overrides the configured level. Leave it alone unless you are deliberately probing: raising it invents errors the project never agreed to fix, lowering it hides real ones. To learn what the project enforces, read `vendor/bin/mate resources:read phpstan://config`.

## Reading

- `summary` carries `level`, `files_with_errors`, `total_errors`, `time`; `status` is `OK` only at zero errors.
- The summary `level` is always `N/A`: the analysis payload does not carry it back. When the level matters, take `configured_level` from `phpstan://config` instead.
- Each error is `{file, line, message, ignorable}`.
- `ignorable: false` is a different kind of finding. Those cannot be silenced through `ignoreErrors`; they are parse errors, internal errors, or the analysis giving up on a file. Treat one as a broken file to fix first, not as an item on the error list.
- Messages are truncated to 200 characters. When the cut-off half is what you need, open the file at the reported line instead of guessing.
- `mode` decides the detail. `summary`: counts only. `default`: errors with the base file name. `detailed`: the full path, which is what tells two `Invoice.php` apart.
- Compare against the count you started from. An unchanged count means your change introduced nothing new, even in a project that is not at zero.

## Failure paths

- Results look stale or contradict the source: PHPStan caches. Run `vendor/bin/mate tools:call phpstan-clear-cache` and analyse again, especially after a dependency or config change.
- The call errors instead of returning findings: anything that stops PHPStan producing JSON (missing binary, a fatal in a bootstrap file, an unreadable config) surfaces as a failed call, not as zero errors. The message is about the setup, not the code.
- Zero errors on a path you expected to fail: check the path is inside the paths the configuration analyses, and spelled relative to the project root.
- A project-wide run stops at 300 seconds. Analyse by directory rather than repeating it.
- Do not add an `ignoreErrors` entry or a baseline line to make a run green. That is a project policy decision for the user to make.
