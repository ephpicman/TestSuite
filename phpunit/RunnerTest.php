<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\UnitTesting\Runner;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class RunnerTest extends TestCase
{
    public function testRunsTestsFromDirectory(): void
    {
        $directory = $this->createFixtureDirectory(
            'EphpicMan\\TestSuite\\RuntimeFixtures\\PassingFixtureTest',
            'public function testWorks(): void { $this->assertTrue(true); }'
        );

        try {
            $results = (new Runner($directory))->run();

            $this->assertCount(1, $results);
            $this->assertTrue(\n                $results[0]->passed(),\n                sprintf(\n                    'failure=%s error=%s',\n                    $results[0]->failure()?->message() ?? '<none>',\n                    var_export($results[0]->error(), true),\n                ),\n            );
            $this->assertSame('testWorks', $results[0]->method());
        } finally {
            $this->removeFixtureDirectory($directory);
        }
    }

    public function testRunsAClassLoadedBeforeRunner(): void
    {
        $directory = $this->createFixtureDirectory(
            'EphpicMan\\TestSuite\\RuntimeFixtures\\PreloadedFixtureTest',
            'public function testWorks(): void { $this->assertTrue(true); }'
        );

        try {
            require_once $directory . '/PreloadedFixtureTest.php';

            $results = (new Runner($directory))->run();

            $this->assertCount(1, $results);
            $this->assertTrue($results[0]->passed());
            $this->assertSame('testWorks', $results[0]->method());
        } finally {
            $this->removeFixtureDirectory($directory);
        }
    }

    public function testReportsUnexpectedErrors(): void
    {
        $directory = $this->createFixtureDirectory(
            'EphpicMan\\TestSuite\\RuntimeFixtures\\ErrorFixtureTest',
            "public function testErrors(): void { throw new \\RuntimeException('fixture error'); }"
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

    private function createFixtureDirectory(string $class, string $method): string
    {
        $directory = sys_get_temp_dir() . '/ephpicman-test-suite-' . bin2hex(random_bytes(8));

        mkdir($directory, 0755, true);

        $separator = strrpos($class, '\\');

        if ($separator === false) {
            throw new RuntimeException('Fixture class must contain a namespace separator.');
        }

        $namespace = substr($class, 0, $separator);
        $shortClass = substr($class, $separator + 1);

        $content = sprintf(
            '<?php
declare(strict_types=1);

namespace %s;

use EphpicMan\\TestSuite\\UnitTesting\\UnitTest;

final class %s extends UnitTest
{
    %s
}
',
            $namespace,
            $shortClass,
            $method
        );

        file_put_contents($directory . '/' . $shortClass . '.php', $content);

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
