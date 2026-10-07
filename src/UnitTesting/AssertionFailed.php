<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use RuntimeException;

final class AssertionFailed extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly mixed $expected = null,
        private readonly mixed $actual = null,
    ) {
        parent::__construct($message);
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
