# TD-PHP

TD-PHP is a **generic PHP project skeleton for test-driven development (TDD)**.

The goal is simple: start a new PHP project with a conventional structure and the essential development-quality tooling already configured, so you can start writing tests and production code immediately.

TD-PHP is intentionally **not** a framework, CMS plugin, application, or domain-specific starter. It contains no WordPress integration and no frontend toolchain.

## What you get

TD-PHP starts with four development tools:

- **PHPUnit** — automated tests.
- **PHPStan** — static analysis.
- **Psalm** — a second, independent static-analysis pass.
- **PHP CS Fixer** — deterministic PHP formatting.

They are already wired into Composer scripts, so the normal workflow is straightforward:

```bash
composer install
composer check-all
```

The skeleton targets **PHP 8.2+**.

## Why this skeleton exists

A new PHP project should not have to spend its first day deciding how to install PHPUnit, how to structure tests, how to run static analysis, or how to enforce formatting.

TD-PHP provides that baseline from the beginning.

It is designed for a workflow where tests are part of development rather than something added after the implementation:

1. Write a failing test.
2. Implement the smallest change that makes it pass.
3. Refactor while keeping the test suite green.
4. Run static analysis and formatting checks.
5. Repeat.

The repository does not try to prescribe an application architecture. It gives you the development foundation and leaves the domain design to the project.

## Requirements

- PHP 8.2 or newer.
- Composer 2.x.

Install dependencies:

```bash
composer install
```

## Customising the skeleton

TD-PHP is a starting point, not a fixed architecture. After copying or using the skeleton for a real project, review these configuration points.

| Area | Where | What you can change |
| --- | --- | --- |
| Project identity | `composer.json` | Package name, description, keywords, homepage, and authors. |
| PHP version | `composer.json` | Change the `php` constraint to the versions your project supports. |
| Namespace | `composer.json` | Replace `App\\` with your project's production namespace. |
| Test namespace | `composer.json` | Replace `Tests\\` if your test namespace follows a different convention. |
| Dependencies | `composer.json` | Add the runtime and development packages your project actually needs. |
| Test layout | `phpunit.xml` | Change the test-suite directories or add additional suites. |
| PHPStan scope/level | `phpstan.neon` | Change analysed paths and the analysis level to match the project. |
| Psalm scope/strictness | `psalm.xml` | Change analysed directories and error level as the project evolves. |
| Formatting rules | `.php-cs-fixer.dist.php` | Adjust the fixer rules and directories to match the project's coding standard. |
| Coverage | `composer.json` / PHPUnit config | Keep coverage diagnostic, or add a project-specific coverage policy if one is justified. |
| CI PHP versions | `.github/workflows/ci.yml` | Change the supported PHP matrix and the PHP version used for quality checks. |
| CI checks | `.github/workflows/ci.yml` | Add, remove, or split jobs when the project's verification requirements change. |

### Namespace and package identity

The skeleton uses:

```text
App\\ => src/
Tests\\ => tests/
```

For a real project, replace these placeholders with the project's actual namespaces and update the corresponding directories if necessary.

### Static-analysis strictness

PHPStan starts at level 8 and analyses `src/`. Psalm analyses both `src/` and `tests/` because PHPUnit discovers test classes at runtime and they are still useful to analyse.

If a project needs a different balance between adoption effort and strictness, adjust the analyser configuration deliberately. Prefer fixing real findings over adding broad suppressions.

### Formatting policy

PHP CS Fixer provides the repository's baseline formatting rules. Projects can change the rule set, allow or forbid risky rules, and change which directories are formatted.

Keep formatting changes intentional: the skeleton should enforce a consistent policy, not continually rewrite unrelated code.

### CI matrix and quality gate

The CI workflow currently tests PHPUnit against PHP 8.2 through 8.5 and runs static analysis and formatting checks on PHP 8.5.

If your project supports a different PHP range, update the matrix accordingly. If a tool has compatibility requirements that differ from the runtime matrix, keep those concerns explicit rather than silently relying on one PHP version.

## Development commands

### Run tests

```bash
composer test
```

### Run tests with HTML coverage

```bash
composer test:coverage
```

The report is generated under `build/coverage/`.

Coverage is provided as a diagnostic tool. TD-PHP does not impose an arbitrary coverage percentage: meaningful tests matter more than maximising a number.

### Run PHPStan

```bash
composer analyze:phpstan
```

PHPStan analyses the production source tree.

### Run Psalm

```bash
composer analyze:psalm
```

Psalm provides an independent static-analysis pass over the production and test source trees.

### Check formatting

Without modifying files:

```bash
composer format:check
```

Apply formatting:

```bash
composer format
```

### Run the complete quality gate

```bash
composer check-all
```

This runs, in order:

1. PHP CS Fixer verification.
2. PHPStan.
3. Psalm.
4. PHPUnit.

The verification command does not rewrite source files.

## Project structure

```text
.
├── src/
│   └── Example.php
├── tests/
│   └── Unit/
│       └── ExampleTest.php
├── .editorconfig
├── .gitignore
├── .php-cs-fixer.dist.php
├── composer.json
├── composer.lock
├── phpstan.neon
├── phpunit.xml
├── psalm.xml
├── CONTRIBUTING.md
├── LICENSE
└── README.md
```

### Source code

Production PHP belongs under `src/`.

The Composer autoloader uses the placeholder namespace:

```text
App\\ => src/
```

When starting a real project, replace `App\\` with the project's actual namespace and package name.

### Tests

Tests belong under `tests/`.

The example test lives under `tests/Unit/` to establish a conventional starting point. As a project grows, additional test categories can be introduced where they are justified, for example:

```text
tests/
├── Unit/
├── Integration/
└── Functional/
```

Do not create test categories just for the sake of having directories. The test structure should follow the system being tested.

## TDD starting point

The repository includes one deliberately small example:

- `src/Example.php` contains a minimal class.
- `tests/Unit/ExampleTest.php` tests its observable behaviour.

The example exists to prove that the skeleton works immediately after installation and to show where production code and tests belong. It is not intended to be an application architecture.

For a real project, replace or remove the example and begin with the behaviour you actually need.

## Tooling policy

TD-PHP deliberately starts with four tools and keeps their responsibilities separate:

| Tool | Responsibility |
| --- | --- |
| PHPUnit | Tests and test execution |
| PHPStan | Static analysis |
| Psalm | Independent static analysis |
| PHP CS Fixer | Formatting |

No frontend tooling, WordPress-specific tooling, framework, test runner, mutation-testing system, or additional analysis framework is required by the baseline.

The purpose is not to maximise the number of tools. The purpose is to make a new PHP codebase testable and maintainable from its first commit.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the development and verification workflow.

Before submitting a change:

```bash
composer check-all
```

## License

TD-PHP is licensed under the MIT License. See [LICENSE](LICENSE).
