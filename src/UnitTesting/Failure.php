<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

final class Failure
{
    public function __construct(
        private readonly string $message,
        private readonly mixed $expected = null,
        private readonly mixed $actual = null,
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }

    public function expected(): mixed
    {
        return $this->expected;
    }

    public function actual(): mixed
    {
        return $this->actual;
    }
}
