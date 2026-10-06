# Contributing

TD-PHP is a generic PHP project skeleton focused on test-driven development and a small, explicit quality baseline.

## Development environment

Requirements:

- PHP 8.2+
- Composer 2.x

Install dependencies:

```bash
composer install
```

## The development loop

For behaviour changes, prefer a test-first workflow:

1. Describe the required behaviour with a test.
2. Run the test and confirm that it fails for the expected reason.
3. Implement the smallest change needed to make it pass.
4. Refactor without changing the behaviour.
5. Run the complete quality gate.

The important part is not the ceremony. The important part is that the test describes behaviour before the implementation becomes the source of truth.

## Verification

Run the complete local check with:

```bash
composer check-all
```

This verifies:

1. PHP formatting.
2. PHPStan.
3. Psalm.
4. PHPUnit.

To run individual checks:

```bash
composer test
composer analyze:phpstan
composer analyze:psalm
composer format:check
```

To apply PHP CS Fixer formatting:

```bash
composer format
```

After formatting, run `composer check-all` again.

## Tests

Production code belongs in `src/`. Tests belong in `tests/`.

Use PHPUnit for automated tests. Keep tests deterministic and focused on observable behaviour.

Prefer:

- small tests with one clear reason to fail;
- explicit inputs and outputs;
- isolated unit tests where possible;
- integration tests when integration itself is the behaviour under test.

Avoid coupling tests to implementation details unless the implementation detail is itself part of the contract being verified.

Coverage is useful for finding untested areas, but TD-PHP does not treat a coverage percentage as a substitute for test quality.

## Static analysis

Both PHPStan and Psalm are part of the baseline.

New production code should pass both analysers. Do not add broad suppressions simply to make the checks green.

When a warning identifies a real design or typing problem, prefer fixing the underlying code. If a suppression is genuinely necessary, keep it narrow and document why.

## Formatting

PHP CS Fixer is the formatting tool for this repository.

Use:

```bash
composer format
```

to apply formatting, and:

```bash
composer format:check
```

to verify formatting without modifying files.

Avoid unrelated formatting changes in a functional change.

## Project structure

Keep the top-level structure conventional:

```text
src/       production PHP
tests/     automated tests
```

Start with `tests/Unit/` when unit tests are appropriate. Add other test categories only when the project needs them.

Do not introduce framework-specific directories or infrastructure into the skeleton itself.

## Dependencies and tooling

The baseline intentionally contains four development tools:

- PHPUnit
- PHPStan
- Psalm
- PHP CS Fixer

Do not add another testing or analysis tool merely because it is popular. Add infrastructure only when the project has a demonstrated need for it.

Likewise, do not remove one of the baseline tools without an explicit decision to change TD-PHP's purpose.

## Pull requests

A good change should:

- solve one coherent problem;
- keep the change as small as practical;
- include or update tests when behaviour changes;
- preserve compatibility unless a breaking change is intentional;
- keep the skeleton generic;
- update documentation when the development workflow changes.

Before opening a pull request, run:

```bash
composer check-all
```

Then review the final diff for generated files, accidental dependencies, and unrelated changes.
