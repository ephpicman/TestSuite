# Architecture

## Purpose

EphpicMan Test Suite provides a WordPress integration layer around PHPUnit.

Its purpose is not to replace PHPUnit. The project provides the WordPress-specific discovery, configuration and presentation needed to run plugin tests from the WordPress admin area.

## Layers

### WordPress integration

The `Plugin` class is the entry point for WordPress-facing behaviour.

It:

- registers the admin page;
- protects the test-running action with a capability check and nonce;
- provides the default Test Suite test directory;
- exposes the `ephpicman_test_directories` extension point;
- creates the project `Runner`.

### Public test API

`UnitTest` is the class that consumer plugins extend.

It extends:

```php
PHPUnit\Framework\TestCase
```

and therefore inherits PHPUnit's test functionality.

The small `Test` interface is retained as an EphpicMan marker for compatibility and internal discovery.

### Test discovery

`Runner` receives one or more test directories.

For each directory it:

1. finds files matching `*Test.php`;
2. requires those files once;
3. records which classes existed before loading;
4. finds newly declared concrete subclasses of `UnitTest`;
5. passes those classes to the PHPUnit adapter.

The before/after class snapshot is intentional. It prevents a nested Runner invocation from rediscovering classes that were already loaded by an outer Runner.

### PHPUnit execution

`PhpUnitRunner` is the adapter between the public EphpicMan API and PHPUnit's programmatic execution facilities.

It creates a PHPUnit `TestSuite`, adds the discovered `UnitTest` classes and executes the suite.

This class is deliberately isolated because it depends on PHPUnit APIs that are implementation-oriented rather than part of the consumer-facing EphpicMan API.

### Result collection

`PhpUnitResultCollector` listens to PHPUnit events and converts them into EphpicMan `TestResult` objects.

The collector records:

- class;
- method;
- duration;
- assertion count;
- comparison failures;
- errors;
- skipped and incomplete outcomes.

The collector filters events by the test classes belonging to its own Runner invocation. This is important because PHPUnit's event system operates within the current PHP process.

### Result model

`TestResult` is the stable result object consumed by the WordPress admin UI and other EphpicMan code.

`Failure` contains a failure message and, when available, expected and actual values.

These classes are deliberately small. They are result models, not another testing engine.

## Why PHPUnit is used directly

A previous implementation duplicated a number of PHPUnit concepts:

- assertions;
- lifecycle handling;
- test method discovery;
- assertion counting;
- failure handling;
- execution.

That created unnecessary maintenance cost and limited the features available to consumer plugins.

The current design delegates these responsibilities to PHPUnit.

The EphpicMan layer only owns behaviour that is specific to the WordPress plugin ecosystem.

## Multiple plugins

Multiple plugins can register test directories:

```php
add_filter(
    'ephpicman_test_directories',
    function (array $directories): array {
        $directories[] = WP_PLUGIN_DIR . '/my-plugin/tests';

        return $directories;
    }
);
```

All registered tests run in the same PHP process.

Consequences:

- plugin test classes must use distinct namespaces;
- global state can be shared between tests;
- test code should clean up state that it changes;
- one plugin should not assume process isolation from another plugin.

The Test Suite currently provides process-level integration, not sandboxed execution.

## Autoloading

Composer owns the project's dependency autoloading.

The EphpicMan `Autoloader` is a compatibility wrapper around Composer's `ClassLoader`.

This is intentional: the project keeps its established public API without maintaining a duplicate PSR-4 implementation.

## Compatibility policy

The following principle applies to the public API:

> Prefer backwards-compatible evolution over breaking changes.

If a public API must change, introduce a migration path where practical.

Internal PHPUnit adapter classes may change as PHPUnit evolves, provided the public EphpicMan API remains stable.

## Testing the architecture

There are two complementary test layers.

### `phpunit/`

These tests use PHPUnit directly to test the Test Suite implementation.

They are suitable for:

- isolated classes;
- PHPUnit integration details;
- edge cases that do not require a live WordPress runtime.

### `tests/`

These tests are loaded by the Test Suite itself.

They verify that the product works through its public runtime path.

This distinction is deliberate and should be preserved.
