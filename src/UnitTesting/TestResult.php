<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

/**
 * Immutable result for a single test method.
 *
 * The result model is intentionally independent of PHPUnit's internal result
 * objects so the WordPress-facing API remains small and stable.
 *
 * @psalm-api
 */
final class TestResult
{
    /**
     * @param string $class Fully-qualified test class name.
     * @param string $method Test method name.
     * @param float $duration Test execution time in seconds.
     * @param int $assertions Number of assertions performed by the test.
     * @param Failure|null $failure Assertion failure, if the test failed.
     * @param string|null $error Unexpected error message, if execution errored.
     */
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly float $duration,
        private readonly int $assertions,
        private readonly ?Failure $failure = null,
        private readonly ?string $error = null,
    ) {
    }

    /** Returns the fully-qualified test class name. */
    public function class(): string
    {
        return $this->class;
    }

    /** Returns the executed test method name. */
    public function method(): string
    {
        return $this->method;
    }

    /** Returns the test execution time in seconds. */
    public function duration(): float
    {
        return $this->duration;
    }

    /** Returns the number of assertions performed by the test. */
    public function assertions(): int
    {
        return $this->assertions;
    }

    /**
     * Determines whether the test completed without a failure or error.
     */
    public function passed(): bool
    {
        return $this->failure === null && $this->error === null;
    }

    /** Returns the assertion failure, if one was recorded. */
    public function failure(): ?Failure
    {
        return $this->failure;
    }

    /** Returns the unexpected error message, if one was recorded. */
    public function error(): ?string
    {
        return $this->error;
    }
}
