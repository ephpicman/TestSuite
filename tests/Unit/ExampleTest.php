<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests\Unit;

use EphpicMan\TestSuite\Example;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UnusedClass
 */
final class ExampleTest extends TestCase
{
    public function testItGreets(): void
    {
        self::assertSame('Hello, EphpicMan!', (new Example())->greet('EphpicMan'));
    }
}
