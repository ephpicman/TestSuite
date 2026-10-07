<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\UnitTesting\UnitTest;
use PHPUnit\Framework\TestCase;

final class UnitTestTest extends TestCase
{
    public function testAssertionsTrackExpectedAndActualValues(): void
    {
        $test = new class () extends UnitTest {
            public function testAssertions(): void
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
        };

        $results = $test->runTests();

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->passed());
        $this->assertSame(9, $results[0]->assertions());
    }

    public function testFailedAssertionProducesStructuredResult(): void
    {
        $test = new class () extends UnitTest {
            public function testFailure(): void
            {
                $this->assertSame('expected', 'actual');
            }
        };

        $results = $test->runTests();

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]->passed());
        $this->assertNotNull($results[0]->failure());
        $this->assertSame('expected', $results[0]->failure()->expected());
        $this->assertSame('actual', $results[0]->failure()->actual());
    }

    public function testSetUpAndTearDownAreExecuted(): void
    {
        $events = [];

        $test = new class ($events) extends UnitTest {
            /** @param list<string> $events */
            public function __construct(private array &$events)
            {
            }

            protected function setUp(): void
            {
                $this->events[] = 'setUp';
            }

            public function testLifecycle(): void
            {
                $this->events[] = 'test';
            }

            protected function tearDown(): void
            {
                $this->events[] = 'tearDown';
            }
        };

        $results = $test->runTests();

        $this->assertTrue($results[0]->passed());
        $this->assertSame(['setUp', 'test', 'tearDown'], $events);
    }
}
