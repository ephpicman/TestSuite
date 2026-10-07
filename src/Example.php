<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite;

/**
 * Small example service used by the project's sample tests.
 *
 * This class is intentionally simple and exists as a lightweight fixture for
 * demonstrating the repository's testing setup.
 */
final class Example
{
    /**
     * Returns a greeting for the supplied name.
     */
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}
