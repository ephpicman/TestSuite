<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

/**
 * Marker interface for EphpicMan test classes.
 *
 * Consumer tests normally extend {@see UnitTest} rather than implementing
 * this interface directly. The marker is retained as part of the public
 * testing model and can be used by discovery code.
 */
interface Test
{
}
