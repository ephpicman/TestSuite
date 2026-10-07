<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\UnitTesting\UnitTest;
use PHPUnit\Framework\TestCase;

final class UnitTestTest extends TestCase
{
    public function testExtendsPhpUnitTestCase(): void
    {
        $this->assertSame(TestCase::class, get_parent_class(UnitTest::class));
    }

    public function testExposesPhpUnitAssertions(): void
    {
        $this->assertTrue(method_exists(UnitTest::class, 'assertSame'));
        $this->assertTrue(method_exists(UnitTest::class, 'createStub'));
        $this->assertTrue(method_exists(UnitTest::class, 'expectException'));
    }
}
