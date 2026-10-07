<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use PHPUnit\Framework\TestCase;

/**
 * Base class for tests executed by EphpicMan Test Suite.
 *
 * This class deliberately contains no custom assertion or test-execution
 * implementation. It extends PHPUnit's TestCase so consumer plugins receive
 * PHPUnit's assertions, fixtures, data providers and other supported features.
 *
 * @see Test
 * @see TestResult
 */
abstract class UnitTest extends TestCase implements Test
{
}
