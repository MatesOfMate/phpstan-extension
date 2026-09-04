CHANGELOG
=========

0.7.0
-----

 * Add a `phpstan-static-analysis` skill covering run scoping, error interpretation, and cache handling
 * Support symfony/ai-mate 0.13
 * Replace the `#[McpTool]` and `#[McpResource]` attributes with Mate's native `#[MateTool]` and `#[MateResource]`
 * Drop the `#[Schema]` parameter attributes; the input schema is now generated from the parameter types and `@param` docblocks

0.6.0
-----

 * Support symfony/ai-mate 0.12

0.4.0
-----

 * Support symfony/ai-mate 0.11

0.3.0
-----

 * Support symfony/ai-mate 0.8 through 0.10

0.2.0
-----

 * Simplify analysis tools by removing analyse-file in favor of analyse with path targeting
 * Reduce output modes to default, summary, and detailed
 * Improve tool and parameter descriptions for generated schemas
 * Align extension documentation and instructions with the simplified tool inventory

0.1.0
-----

 * Add PHPStan analysis tools (analyse, analyse-file, clear-cache)
 * Add config resource providing PHPStan configuration info
 * Add TOON formatter for ~67% token reduction vs raw PHPStan JSON output
 * Add error categorization with by-type output mode
 * Add multiple output modes: toon, summary, detailed, by-file, by-type
 * Add auto-detection of phpstan.neon configuration
 * Add INSTRUCTIONS.md for AI agent guidance
