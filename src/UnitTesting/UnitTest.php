<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use ReflectionMethod;
use Throwable;

abstract class UnitTest implements Test
{
    private int $assertions = 0;

    /** @return list<TestResult> */
    final public function runTests(): array
    {
        $results = [];

        foreach (get_class_methods($this) as $method) {
            if (! str_starts_with($method, 'test')) {
                continue;
            }

            $reflection = new ReflectionMethod($this, $method);

            if (
                ! $reflection->isPublic()
                || $reflection->isStatic()
                || $reflection->getNumberOfRequiredParameters() > 0
            ) {
                continue;
            }

            $this->assertions = 0;
            $started = microtime(true);
            $failure = null;
            /** @psalm-suppress UnusedVariable */
            $error = null;

            try {
                $this->setUp();
                $this->{$method}();
            } catch (AssertionFailed $exception) {
                $failure = new Failure(
                    $exception->getMessage(),
                    $exception->expected(),
                    $exception->actual()
                );
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            } finally {
                try {
                    $this->tearDown();
                } catch (Throwable $exception) {
                    $error ??= $exception->getMessage();
                }
            }

            $results[] = new TestResult(
                static::class,
                $method,
                microtime(true) - $started,
                $this->assertions,
                $failure,
                $error
            );
        }

        return $results;
    }

    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($expected === $actual) {
            return;
        }

        $this->fail(
            $message ?: sprintf(
                'Failed asserting that %s is identical to %s.',
                $this->export($actual),
                $this->export($expected)
            ),
            $expected,
            $actual
        );
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($expected == $actual) {
            return;
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that %s equals %s.', $this->export($actual), $this->export($expected)),
            $expected,
            $actual
        );
    }

    protected function assertTrue(mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($actual === true) {
            return;
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that %s is true.', $this->export($actual)),
            true,
            $actual
        );
    }

    protected function assertFalse(mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($actual === false) {
            return;
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that %s is false.', $this->export($actual)),
            false,
            $actual
        );
    }

    protected function assertNull(mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($actual === null) {
            return;
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that %s is null.', $this->export($actual)),
            null,
            $actual
        );
    }

    protected function assertNotNull(mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($actual !== null) {
            return;
        }

        $this->fail(
            $message ?: 'Failed asserting that the value is not null.',
            'not null',
            null
        );
    }

    protected function assertInstanceOf(string $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;

        if ($actual instanceof $expected) {
            return;
        }

        $this->fail(
            $message ?: sprintf(
                'Failed asserting that %s is an instance of %s.',
                $this->export($actual),
                $expected
            ),
            $expected,
            $actual
        );
    }

    protected function assertCount(int $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        $actualCount = is_countable($actual) ? count($actual) : null;

        if ($actualCount === $expected) {
            return;
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that the count is %d.', $expected),
            $expected,
            $actualCount
        );
    }

    /**
     * @param iterable<mixed> $actual
     */
    protected function assertContains(mixed $expected, iterable $actual, string $message = ''): void
    {
        $this->assertions++;

        /** @psalm-suppress MixedAssignment */
        foreach ($actual as $value) {
            if ($value === $expected) {
                return;
            }
        }

        $this->fail(
            $message ?: sprintf('Failed asserting that the iterable contains %s.', $this->export($expected)),
            $expected,
            $actual
        );
    }

    protected function fail(string $message, mixed $expected = null, mixed $actual = null): never
    {
        throw new AssertionFailed($message, $expected, $actual);
    }

    private function export(mixed $value): string
    {
        return var_export($value, true);
    }
}
