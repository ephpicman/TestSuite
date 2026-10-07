<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

/**
 * Describes a failed test assertion.
 *
 * This is an EphpicMan result model rather than a replacement for PHPUnit's
 * failure mechanism. It gives the WordPress result view a stable, small API.
 *
 * @psalm-api
 */
final class Failure
{
    /**
     * @param string $message Human-readable failure message.
     * @param mixed $expected Expected value, when available.
     * @param mixed $actual Actual value, when available.
     */
    public function __construct(
        private readonly string $message,
        private readonly mixed $expected = null,
        private readonly mixed $actual = null,
    ) {
    }

    /**
     * Returns the failure message.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Returns the expected value, when PHPUnit reported one.
     */
    public function expected(): mixed
    {
        return $this->expected;
    }

    /**
     * Returns the actual value, when PHPUnit reported one.
     */
    public function actual(): mixed
    {
        return $this->actual;
    }
}
