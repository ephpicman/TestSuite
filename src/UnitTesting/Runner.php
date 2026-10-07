<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use ReflectionClass;
use Throwable;

final class Runner
{
    /** @var list<string> */
    private array $testsDirectories;

    /** @param string|list<string> $testsDirectories */
    public function __construct(string|array $testsDirectories)
    {
        $this->testsDirectories = is_array($testsDirectories)
            ? $testsDirectories
            : [$testsDirectories];
    }

    /** @return list<TestResult> */
    public function run(): array
    {
        $declaredClasses = get_declared_classes();

        $this->loadTests();
        $results = [];

        foreach ($this->findTestClasses($declaredClasses) as $class) {
            try {
                /** @psalm-suppress UnsafeInstantiation */
                $test = new $class();

                foreach ($test->runTests() as $result) {
                    $results[] = $result;
                }
            } catch (Throwable $exception) {
                $results[] = new TestResult(
                    $class,
                    '__construct',
                    0.0,
                    0,
                    null,
                    $exception->getMessage()
                );
            }
        }

        return $results;
    }

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
     * @param list<class-string> $declaredClasses
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
