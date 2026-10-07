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
     * Discovery is based on the reflected source file rather than a
     * before/after declared-class snapshot. This is important in WordPress,
     * where a plugin or Composer bootstrap may load a test class before the
     * Runner is invoked.
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
