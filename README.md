# EphpicMan Test Suite

EphpicMan Test Suite is a WordPress developer toolkit for running PHPUnit-based tests from inside WordPress.

It is designed for EphpicMan plugins that need a consistent way to:

- define plugin tests;
- discover tests from one or more plugin directories;
- run those tests from the WordPress admin area;
- present useful results to a WordPress administrator.

The project uses `PHPUnit` as its test engine. EphpicMan Test Suite does not implement a second assertion or test-execution framework.

## Requirements

- PHP 8.2.27 or newer
- WordPress 6.5 or newer
- Composer for development and dependency installation

## How it works

A consumer plugin extends the public `UnitTest` class:

```php
<?php

namespace Acme\MyPlugin\Tests;

use EphpicMan\TestSuite\UnitTesting\UnitTest;

final class CalculatorTest extends UnitTest
{
    public function testAddition(): void
    {
        $this->assertSame(5, 2 + 3);
    }
}
```

The inheritance chain is:

```text
YourPluginTest
    ↓
EphpicMan\TestSuite\UnitTesting\UnitTest
    ↓
PHPUnit\Framework\TestCase
    ↓
PHPUnit
```

This means consumer plugins use PHPUnit's `TestCase` API rather than a second, EphpicMan-specific assertion API.

For example:

```php
final class ProductTest extends UnitTest
{
    public function testProductIsActive(): void
    {
        $product = new Product();

        $this->assertTrue($product->isActive());
    }

    public function testInvalidProductThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Product('');
    }
}
```

PHPUnit features such as assertions, fixtures, data providers, exception expectations and other supported `TestCase` features are provided by PHPUnit itself.

## Registering tests from another plugin

A plugin can register one or more test directories with the `ephpicman_test_directories` filter:

```php
add_filter(
    'ephpicman_test_directories',
    function (array $directories): array {
        $directories[] = __DIR__ . '/tests';

        return $directories;
    }
);
```

The Test Suite also registers its own `tests/` directory by default.

Test files must use the `*Test.php` filename convention.

A typical plugin structure is:

```text
my-plugin/
├── my-plugin.php
├── src/
└── tests/
    ├── ProductTest.php
    └── SettingsTest.php
```

Tests should use distinct namespaces. All registered test directories are loaded into the same PHP process, so class-name collisions must be avoided.

## Running tests

When EphpicMan Test Suite is active, users with the required WordPress capability can open:

**WordPress Admin → Unit Testing**

The Test Suite:

1. loads registered test files;
2. discovers concrete classes extending `UnitTest`;
3. creates a PHPUnit test suite;
4. executes the tests through PHPUnit;
5. translates PHPUnit events into the Test Suite's result model;
6. displays the results in the WordPress admin interface.

The result view includes:

- test class and method;
- assertion count;
- execution duration;
- pass/fail/error state;
- failure messages;
- expected and actual values where PHPUnit reports a comparison failure;
- unexpected errors.

## Public API

The main consumer-facing API is:

- `EphpicMan\TestSuite\UnitTesting\UnitTest`
- `EphpicMan\TestSuite\UnitTesting\Runner`
- `EphpicMan\TestSuite\UnitTesting\TestResult`
- `EphpicMan\TestSuite\UnitTesting\Failure`
- `ephpicman_test_directories`

Consumer plugins should normally extend `UnitTest` and register their test directory. They should not depend on the internal PHPUnit adapter classes.

## Architecture

The project separates the WordPress integration layer from the test engine:

```text
WordPress
   │
   ├── Admin UI
   ├── Test directory registration
   └── EphpicMan Runner
            │
            ├── test discovery
            └── PHPUnit adapter
                    │
                    └── PHPUnit
```

The classes under `src/UnitTesting/` named `PhpUnitRunner` and `PhpUnitResultCollector` are implementation details. They isolate PHPUnit integration from the public EphpicMan API.

## Autoloading

Composer provides the project's dependency and PSR-4 autoloading infrastructure.

The `Autoloader` class preserves the existing `addNamespace()` and `register()` API while delegating class loading to Composer's `ClassLoader`.

This avoids maintaining a second PSR-4 implementation.

## Development

Install dependencies:

```bash
composer install
```

Run PHPUnit:

```bash
composer test
```

Run the complete quality gate:

```bash
composer run check-all
```

The complete quality gate runs:

- PHP-CS-Fixer;
- PHPStan;
- Psalm;
- PHPUnit.

The PHPUnit suite in `phpunit/` tests EphpicMan Test Suite itself.

The `tests/` directory contains tests that exercise the plugin's WordPress-facing runtime test system.

## Repository structure

```text
src/                    Production code
src/UnitTesting/        Public testing API and PHPUnit integration
tests/                  Runtime/self-hosting tests
phpunit/                PHPUnit tests for the Test Suite itself
.github/workflows/      Continuous integration
```

For more detail, see [docs/architecture.md](docs/architecture.md) and [CONTRIBUTING.md](CONTRIBUTING.md).

## Compatibility

The project targets PHP 8.2.27 and newer.

The public EphpicMan testing API should remain stable. Changes to public behaviour should favour backwards compatibility and should be introduced deliberately.

## Licence

GPL-3.0-or-later.
