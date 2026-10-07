<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\TestRunner\Result\Facade as TestResultFacade;
use PHPUnit\TextUI\Configuration\Builder as ConfigurationBuilder;
use PHPUnit\TextUI\TestRunner;
use ReflectionClass;

/**
 * Adapts EphpicMan test classes to PHPUnit's programmatic test runner.
 *
 * This class is intentionally internal to the Test Suite. Consumer plugins
 * should extend UnitTest and use Runner rather than depending on this adapter.
 *
 * @psalm-suppress InternalMethod
 */
final class PhpUnitRunner
{
    /**
     * Discovers individual PHPUnit test methods from the supplied classes.
     *
     * PHPUnit remains responsible for deciding which methods are tests,
     * including metadata and data providers.
     *
     * @param list<class-string<UnitTest>> $testClasses Concrete test classes to inspect.
     *
     * @return list<array{class: class-string<UnitTest>, method: string}>
     */
    public function discover(array $testClasses): array
    {
        $suite = $this->createSuite($testClasses);
        $tests = [];

        foreach ($suite->collect() as $test) {
            if (! $test instanceof TestCase) {
                continue;
            }

            /** @var class-string<UnitTest> $class */
            $class = $test::class;

            $tests[] = [
                'class' => $class,
                'method' => $test->name(),
            ];
        }

        return $tests;
    }

    /**
     * Executes the supplied test classes through PHPUnit.
     *
     * PHPUnit's Text UI components are used programmatically so the Test Suite
     * can retain its WordPress admin interface without invoking the CLI.
     *
     * @param list<class-string<UnitTest>> $testClasses Concrete test classes to execute.
     *
     * @return list<TestResult> Normalised results collected from PHPUnit events.
     */
    public function run(array $testClasses): array
    {
        return $this->runSuite(
            $this->createSuite($testClasses),
            $testClasses
        );
    }

    /**
     * Executes one test method through PHPUnit.
     *
     * The class is converted to a PHPUnit suite first so PHPUnit's own
     * discovery rules remain authoritative.
     *
     * @param class-string<UnitTest> $testClass
     *
     * @return list<TestResult>
     */
    public function runTest(string $testClass, string $testMethod): array
    {
        $sourceSuite = $this->createSuite([$testClass]);
        $suite = TestSuite::empty('EphpicMan Test Suite');

        foreach ($sourceSuite->collect() as $test) {
            if (! $test instanceof TestCase || $test->name() !== $testMethod) {
                continue;
            }

            $suite->addTest($test);
        }

        if ($suite->isEmpty()) {
            return [];
        }

        return $this->runSuite($suite, [$testClass]);
    }

    /**
     * Builds a PHPUnit suite from the supplied test classes.
     *
     * @param list<class-string<UnitTest>> $testClasses
     */
    private function createSuite(array $testClasses): TestSuite
    {
        $suite = TestSuite::empty('EphpicMan Test Suite');

        foreach ($testClasses as $testClass) {
            $reflection = new ReflectionClass($testClass);

            /** @psalm-suppress InternalClass */
            $suite->addTestSuite($reflection);
        }

        return $suite;
    }

    /**
     * Executes a prepared PHPUnit suite and collects stable TestSuite results.
     *
     * @param list<class-string<UnitTest>> $testClasses Classes owned by the collector.
     */
    private function runSuite(TestSuite $suite, array $testClasses): array
    {
        $configuration = (new ConfigurationBuilder())->build([
            'phpunit',
            '--no-configuration',
            '--no-output',
            '--no-progress',
            '--no-results',
        ]);

        $collector = new PhpUnitResultCollector($testClasses);

        /** @psalm-suppress InternalClass */
        EventFacade::instance()->registerTracer($collector);

        /** @psalm-suppress InternalClass */
        TestResultFacade::init();

        /**
         * PHPUnit's event dispatcher defers dispatching until the facade is
         * sealed. The Text UI application performs this step before starting
         * the test runner; programmatic execution must do the same.
         *
         * @psalm-suppress InternalClass
         */
        EventFacade::instance()->seal();

        /** @psalm-suppress InternalClass */
        (new TestRunner())->run(
            $configuration,
            /** @psalm-suppress InternalClass */
            new DefaultResultCache(),
            $suite
        );

        return $collector->results();
    }
}
