<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests;

use EphpicMan\TestSuite\UnitTesting\UnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class UnitTestTest extends UnitTest
{
    private bool $setUpRan = false;

    protected function setUp(): void
    {
        $this->setUpRan = true;
    }

    public function testPhpUnitAssertionsAndLifecycleWork(): void
    {
        $this->assertTrue($this->setUpRan);
        $this->assertSame(10, 10);
        $this->assertEquals('10', 10);
        $this->assertFalse(false);
        $this->assertNull(null);
        $this->assertNotNull('value');
        $this->assertInstanceOf(UnitTest::class, $this);
        $this->assertCount(2, ['one', 'two']);
        $this->assertContains('two', ['one', 'two']);
    }

    #[DataProvider('additionProvider')]
    public function testPhpUnitDataProviderWorks(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $a + $b);
    }

    /** @return iterable<string, array{int, int, int}> */
    public static function additionProvider(): iterable
    {
        yield 'zero' => [0, 0, 0];
        yield 'positive' => [2, 3, 5];
        yield 'negative' => [-2, 3, 1];
    }
}
