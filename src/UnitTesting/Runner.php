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
     * Loads, discovers and executes the registered tests.
     *
     * A Runner failure is returned as a TestResult rather than allowed to
     * break the WordPress admin request.
     *
     * @return list<TestResult> Results produced by PHPUnit.
     */
    public function run(): array
    {
        /*
         * Only classes declared by the files loaded during this invocation
         * belong to this discovery pass. This also prevents nested Runner
         * calls from rediscovering an outer Runner's test classes.
         */
        $declaredClasses = get_declared_classes();

        $this->loadTests();

        $testClasses = $this->findTestClasses($declaredClasses);

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
     * Loads test files from every registered directory.
     *
     * Only direct `*Test.php` files are loaded. Directory registration is
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
     * Finds concrete UnitTest subclasses declared by this discovery pass.
     *
     * @param list<class-string> $declaredClasses Classes already loaded before discovery.
     *
     * @return list<class-string<UnitTest>>
     */
    private function findTestClasses(array $declaredClasses): array
    {
        $classes = array_diff(
            get_declared_classes(),
            $declaredClasses
        );

        $testClasses = [];

        foreach ($classes as $class) {
            if (! is_subclass_of($class, UnitTest::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            $testClasses[] = $class;
        }

        return $testClasses;
    }
}
