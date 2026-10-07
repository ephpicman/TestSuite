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
 * @psalm-suppress InternalMethod
 */
final class PhpUnitRunner
{
    /**
     * @param list<class-string<UnitTest>> $testClasses
     * @return list<TestResult>
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
