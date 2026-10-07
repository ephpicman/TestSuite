<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

/**
 * @psalm-api
 */
final class TestResult
{
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly float $duration,
        private readonly int $assertions,
        private readonly ?Failure $failure = null,
        private readonly ?string $error = null,
    ) {
    }

    public function class(): string
    {
        return $this->class;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function assertions(): int
    {
        return $this->assertions;
    }

    public function passed(): bool
    {
        return $this->failure === null && $this->error === null;
    }

    public function failure(): ?Failure
    {
        return $this->failure;
    }

    public function error(): ?string
    {
        return $this->error;
    }
}
