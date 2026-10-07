<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests;

use EphpicMan\TestSuite\UnitTesting\Runner;
use EphpicMan\TestSuite\UnitTesting\UnitTest;

final class RunnerTest extends UnitTest
{
    public function testRunsTestsFromDirectory(): void
    {
        $runner = new Runner(__DIR__ . '/runner-fixtures');

        $results = $runner->run();

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->passed());
        $this->assertSame('testWorks', $results[0]->method());
    }

    public function testAcceptsMultipleDirectories(): void
    {
        $runner = new Runner([
            __DIR__ . '/runner-fixtures',
            __DIR__ . '/other-runner-fixtures',
        ]);

        $results = $runner->run();

        $this->assertCount(2, $results);
    }

    public function testReportsUnexpectedErrors(): void
    {
        $runner = new Runner(__DIR__ . '/error-fixtures');

        $results = $runner->run();

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]->passed());
        $this->assertNotNull($results[0]->error());
    }
}
