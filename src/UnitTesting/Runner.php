<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use ReflectionClass;
use Throwable;

/**
 * Discovers EphpicMan test classes and delegates their execution to PHPUnit.
 *
 * The Runner owns WordPress/EphpicMan test discovery. PHPUnit remains
 * responsible for test lifecycle, assertions and execution.
 *
 * @psalm-api
 */
final class Runner
{
    /** @var list<string> Absolute paths to registered test directories. */
    private array $testsDirectories;

    /**
     * @param string|list<string> $testsDirectories One directory or multiple test directories.
     */
    public function __construct(string|array $testsDirectories)
    {
        $this->testsDirectories = is_array($testsDirectories)
            ? $testsDirectories
            : [$testsDirectories];
    }

    /**
     * Discovers individual test methods from the registered test files.
     *
     * Test files are already loaded by the Runner, so discovery only uses
     * reflection. PHPUnit is intentionally not instantiated here: this method
     * runs while the WordPress admin page is being rendered.
     *
     * @return list<array{class: class-string<UnitTest>, method: string}>
     */
    public function discover(): array
    {
        $this->loadTests();

        $tests = [];

        foreach ($this->findTestClasses() as $testClass) {
            $reflection = new ReflectionClass($testClass);

            foreach ($reflection->getMethods() as $method) {
                if ($method->isPublic() && str_starts_with($method->getName(), 'test')) {
                    $tests[] = [
                        'class' => $testClass,
                        'method' => $method->getName(),
                    ];
                }
            }
        }

        return $tests;
    }

    /**
     * Loads, discovers and executes the registered tests.
     *
     * A Runner failure is returned as a TestResult rather than allowed to
     * break the WordPress admin request.
     *
     * @return list<TestResult> Results produced by PHPUnit.
     */
    public function run(): array
    {
        $this->loadTests();

        $testClasses = $this->findTestClasses();

        if ($testClasses === []) {
            return [];
        }

        try {
            return (new PhpUnitRunner())->run($testClasses);
        } catch (Throwable $exception) {
            return [
                new TestResult(
                    Runner::class,
                    '__runner',
                    0.0,
                    0,
                    null,
                    $exception->getMessage()
                ),
            ];
        }
    }

    /**
     * Executes one discovered test method.
     *
     * @param class-string<UnitTest> $testClass
     *
     * @return list<TestResult>
     */
    public function runTest(string $testClass, string $testMethod): array
    {
        $this->loadTests();

        $testClasses = $this->findTestClasses();

        if (! in_array($testClass, $testClasses, true)) {
            return [];
        }

        try {
            return (new PhpUnitRunner())->runTest($testClass, $testMethod);
        } catch (Throwable $exception) {
            return [
                new TestResult(
                    $testClass,
                    $testMethod,
                    0.0,
                    0,
                    null,
                    $exception->getMessage()
                ),
            ];
        }
    }

    /**
     * Loads test files from every registered directory.
     *
     * Only direct *Test.php files are loaded. Directory registration is
     * controlled by plugin code rather than user input.
     */
    private function loadTests(): void
    {
        foreach ($this->testsDirectories as $directory) {
            $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . '/*Test.php');

            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                /** @psalm-suppress UnresolvableInclude */
                require_once $file;
            }
        }
    }

    /**
     * Finds concrete UnitTest subclasses whose source files belong to the
     * directories registered for this Runner.
     *
     * @return list<class-string<UnitTest>>
     */
    private function findTestClasses(): array
    {
        $directories = [];

        foreach ($this->testsDirectories as $directory) {
            $realDirectory = realpath($directory);

            $directories[] = $realDirectory === false
                ? $directory
                : $realDirectory;
        }

        $testClasses = [];

        foreach (get_declared_classes() as $class) {
            if (! is_subclass_of($class, UnitTest::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            $file = $reflection->getFileName();

            if ($file === false) {
                continue;
            }

            $realFile = realpath($file);

            $file = $realFile === false
                ? $file
                : $realFile;

            foreach ($directories as $directory) {
                $directory = rtrim($directory, DIRECTORY_SEPARATOR);

                if (
                    $file === $directory
                    || str_starts_with($file, $directory . DIRECTORY_SEPARATOR)
                ) {
                    $testClasses[] = $class;

                    break;
                }
            }
        }

        return $testClasses;
    }
}
