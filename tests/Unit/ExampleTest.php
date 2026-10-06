<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Example;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UnusedClass
 */
final class ExampleTest extends TestCase
{
    public function testItGreets(): void
    {
        self::assertSame('Hello, TD-PHP!', (new Example())->greet('TD-PHP'));
    }
}
