<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\UnitTesting\UnitTest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        $failure = $results[0]->failure();

        if ($failure === null) {
            throw new RuntimeException('Expected a structured failure.');
        }

        $this->assertSame('expected', $failure->expected());
        $this->assertSame('actual', $failure->actual());
    }

    public function testSetUpAndTearDownAreExecuted(): void
    {
        $test = new class () extends UnitTest {
            /** @var list<string> */
            private array $events = [];

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

            /** @return list<string> */
            public function events(): array
            {
                return $this->events;
            }
        };

        $results = $test->runTests();

        $this->assertTrue($results[0]->passed());
        $this->assertSame(['setUp', 'test', 'tearDown'], $test->events());
    }
}
