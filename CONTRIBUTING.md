# Contributing

Thank you for contributing to EphpicMan Test Suite.

The project values small, understandable changes, stable public APIs and evidence-based engineering decisions.

## Before you change the code

Please understand the distinction between the two test locations:

- `tests/ contains tests that exercise the Test Suite as a WordPress runtime component.
- `phpunit/ contains PHPUnit tests for the Test Suite's own implementation.

Do not move runtime tests into `phpunit/ merely to make PHPUnit discover them. The WordPress runtime test directory is part of the product behaviour.

## Development environment

Requirements:

- PHP 8.2.27 or newer;
- Composer;
- a WordPress installation for runtime/admin testing when the change affects WordPress integration.

Install dependencies with:

`@bash
composer install
`@

## Quality checks

Before opening a pull request, run:

`@bash
composer run check-all
`@

This runs:

1. PHP-CS-Fixer;
2. PHPStan;
3. Psalm;
4. PHPUnit.

All checks should pass.

## Testing WordPress behaviour

Some production code intentionally depends on WordPress functions such as `add_action() and `apply_filters().

Do not replace those dependencies with fake global functions simply to make a standalone PHPUnit test pass.

If behaviour belongs to the WordPress runtime, test it in the runtime test layer.

## Public API

The following are important public surfaces:

- `EphpicMan\TestSuite\UnitTesting\UnitTest
- `EphpicMan\TestSuite\UnitTesting\Runner
- `EphpicMan\TestSuite\UnitTesting\TestResult
- `EphpicMan\TestSuite\UnitTesting\Failure
- `EphpicMan\TestSuite\Autoloader
- `ephpicman_test_directories

Treat changes to these APIs as compatibility-sensitive.

In particular, `UnitTest is the class that consumer plugins extend. PHPUnit is the underlying test engine and should be reused rather than replaced with project-specific assertion or execution code.

## Documentation

Public classes, methods and extension points should have clear PHPDoc.

Documentation should:

- use plain British English;
- describe behaviour rather than restating the method name;
- document parameters and return values where useful;
- identify extension points and compatibility expectations;
- avoid unnecessary implementation detail in public API documentation.

Comments inside implementation code should explain **why** a non-obvious decision exists, not narrate obvious PHP syntax.

## Pull requests

A good pull request should:

- have one clear purpose;
- preserve existing behaviour unless the change explicitly intends to alter it;
- include or update tests where appropriate;
- update documentation when public behaviour changes;
- avoid unrelated refactoring.

Please explain any compatibility trade-off in the pull request description.

## Commit messages

Use concise, descriptive commit messages.

Examples:

`@text
Document PHPUnit-backed runtime testing
Preserve test directory registration behaviour
Improve failure result documentation
`@
