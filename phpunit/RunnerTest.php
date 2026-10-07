<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\UnitTesting\Runner;
use PHPUnit\Framework\TestCase;

final class RunnerTest extends TestCase
{
    public function testRunsTestsFromDirectoryWithoutRecursivelyRunningPreloadedTests(): void
    {
        $directory = $this->createFixtureDirectory();

        file_put_contents(
            $directory . '/PassingFixtureTest.php',
            '<?php
declare(strict_types=1);

namespace EphpicMan\\TestSuite\\PHPUnitFixtures;

use EphpicMan\\TestSuite\\UnitTesting\\UnitTest;

final class PassingFixtureTest extends UnitTest
{
    public function testWorks(): void
    {
        $this->assertTrue(true);
    }
}
'
        );

        try {
            $results = (new Runner($directory))->run();

            $this->assertCount(1, $results);
            $this->assertSame(
                'EphpicMan\\TestSuite\\PHPUnitFixtures\\PassingFixtureTest',
                $results[0]->class()
            );
            $this->assertSame('testWorks', $results[0]->method());
            $this->assertTrue($results[0]->passed());
            $this->assertSame(1, $results[0]->assertions());
        } finally {
            $this->removeFixtureDirectory($directory);
        }
    }

    public function testReportsUnexpectedErrors(): void
    {
        $directory = $this->createFixtureDirectory();

        file_put_contents(
            $directory . '/ErrorFixtureTest.php',
            '<?php
declare(strict_types=1);

namespace EphpicMan\\TestSuite\\PHPUnitFixtures;

use EphpicMan\\TestSuite\\UnitTesting\\UnitTest;

final class ErrorFixtureTest extends UnitTest
{
    public function testErrors(): void
    {
        throw new \\RuntimeException(\'fixture error\');
    }
}
'
        );

        try {
            $results = (new Runner($directory))->run();

            $this->assertCount(1, $results);
            $this->assertFalse($results[0]->passed());
            $this->assertSame('fixture error', $results[0]->error());
        } finally {
            $this->removeFixtureDirectory($directory);
        }
    }

    private function createFixtureDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/ephpicman-test-suite-' . bin2hex(random_bytes(8));

        mkdir($directory, 0755, true);

        return $directory;
    }

    private function removeFixtureDirectory(string $directory): void
    {
        $files = glob($directory . '/*');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            unlink($file);
        }

        rmdir($directory);
    }
}
