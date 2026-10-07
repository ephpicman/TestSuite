<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests;

use EphpicMan\TestSuite\UnitTesting\UnitTest;
use RuntimeException;

final class UnitTestTest extends UnitTest
{
    public function testAssertionsTrackExpectedAndActualValues(): void
    {
        $this->assertSame(10, 10);
        $this->assertEquals('10', 10);
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertNull(null);
        $this->assertNotNull('value');
        $this->assertInstanceOf(UnitTest::class, $this);
        $this->assertCount(2, ['one', 'two']);
        $this->assertContains('two', ['one', 'two']);
    }

    public function testFailedAssertionProducesStructuredResult(): void
    {
        $fixture = new class () extends UnitTest {
            public function testFailure(): void
            {
                $this->assertSame('expected', 'actual');
            }
        };

        $results = $fixture->runTests();

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]->passed());

        $failure = $results[0]->failure();

        if ($failure === null) {
            throw new RuntimeException('Expected a structured failure.');
        }

        $this->assertSame('expected', $failure->expected());
        $this->assertSame('actual', $failure->actual());
    }
}
