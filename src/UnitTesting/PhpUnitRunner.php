<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
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
        $configuration = (new ConfigurationBuilder())->build([
            'phpunit',
            '--no-configuration',
            '--no-output',
            '--no-progress',
            '--no-results',
        ]);

        /** @psalm-suppress InternalClass */
        $suite = TestSuite::empty('EphpicMan Test Suite');

        foreach ($testClasses as $testClass) {
            $reflection = new ReflectionClass($testClass);

            /** @psalm-suppress InternalClass */
            $suite->addTestSuite($reflection);
        }

        $collector = new PhpUnitResultCollector($testClasses);

        /** @psalm-suppress InternalClass */
        EventFacade::instance()->registerTracer($collector);

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
