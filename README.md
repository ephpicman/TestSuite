# EphpicMan Test Suite

EphpicMan Test Suite is a WordPress developer toolkit for running and inspecting tests from inside WordPress.

## Current architecture

The project has two layers:

- **Developer tooling:** PHPUnit, PHPStan and Psalm are used to develop and verify the project itself.
- **WordPress runtime:** the plugin provides a lightweight PHPUnit-like API that can discover and execute plugin test classes inside WordPress.

The runtime API is intentionally small. It is not a reimplementation of PHPUnit.

## Runtime testing API

Tests extend `EphpicMan\\TestSuite\\UnitTesting\\UnitTest`:

```php
use EphpicMan\TestSuite\UnitTesting\UnitTest;

final class CalculatorTest extends UnitTest
{
    public function testAddition(): void
    {
        $this->assertSame(5, 2 + 3);
    }
}
```

Supported assertions currently include:

- `assertSame()`
- `assertEquals()`
- `assertTrue()`
- `assertFalse()`
- `assertNull()`
- `assertNotNull()`
- `assertInstanceOf()`
- `assertCount()`
- `assertContains()`

The runner records:

- test class and method
- pass/fail/error state
- assertion count
- execution duration
- failure message
- expected and actual values
- unexpected exceptions

Each test method gets `setUp()` and `tearDown()` lifecycle hooks.

## Registering tests from another plugin

A plugin can register one or more test directories:

```php
add_filter('ephpicman_test_directories', function (array $directories): array {
    $directories[] = __DIR__ . '/tests';

    return $directories;
});
```

Test files should use the `*Test.php` suffix and contain classes extending `UnitTest`.

## WordPress admin

Tests can be executed from the **Unit Testing** admin page.

The result view reports:

- total tests
- passed tests
- failed tests
- assertion count
- execution time
- failure details
- expected and actual values
- unexpected errors

The action is protected by the WordPress capability and nonce checks used by the plugin.

## Autoloading

The project uses Composer for its own dependency management and development tooling.

The runtime `Autoloader` preserves the legacy `addNamespace()` / `register()` API while delegating PSR-4 loading to Composer's class loader. Consumer code therefore does not need to carry a second PSR-4 implementation.

## Development

Install dependencies with Composer, then run:

```bash
composer test
composer run check-all
```

The project targets PHP 8.2.27 and newer.

## Licence

GPL-3.0-or-later.
