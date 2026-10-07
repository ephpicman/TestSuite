<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\Outcome\Errored;
use PHPUnit\Event\Test\Outcome\Failed;
use PHPUnit\Event\Test\Outcome\MarkedIncomplete;
use PHPUnit\Event\Test\Outcome\Skipped;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Tracer\Tracer;

final class PhpUnitResultCollector implements Tracer
{
    /** @var array<string, true> */
    private array $testClasses;

    /** @var array<string, float> */
    private array $startedAt = [];

    /** @var array<string, Failure> */
    private array $failures = [];

    /** @var array<string, string> */
    private array $errors = [];

    /** @var list<TestResult> */
    private array $results = [];

    /** @param list<class-string<UnitTest>> $testClasses */
    public function __construct(array $testClasses)
    {
        $this->testClasses = array_fill_keys($testClasses, true);
    }

    public function trace(Event $event): void
    {
        if (! $event->test()->isTestMethod()) {
            return;
        }

        /** @var TestMethod $test */
        $test = $event->test();

        if (! isset($this->testClasses[$test->className()])) {
            return;
        }

        $id = $test->id();

        if ($event instanceof PreparationStarted) {
            $this->startedAt[$id] = $event->telemetryInfo()->durationSinceStart()->asFloat();

            return;
        }

        if ($event instanceof Failed) {
            if ($event->hasComparisonFailure()) {
                $comparison = $event->comparisonFailure();

                $this->failures[$id] = new Failure(
                    $event->throwable()->message(),
                    $comparison->expected(),
                    $comparison->actual()
                );
            } else {
                $this->failures[$id] = new Failure(
                    $event->throwable()->message()
                );
            }

            return;
        }

        if ($event instanceof Errored) {
            $this->errors[$id] = $event->throwable()->message();

            return;
        }

        if ($event instanceof Skipped) {
            $this->errors[$id] = 'Skipped: ' . $event->throwable()->message();

            return;
        }

        if ($event instanceof MarkedIncomplete) {
            $this->errors[$id] = 'Incomplete: ' . $event->throwable()->message();

            return;
        }

        if (! $event instanceof Finished) {
            return;
        }

        $startedAt = $this->startedAt[$id] ?? $event->telemetryInfo()->durationSinceStart()->asFloat();
        $finishedAt = $event->telemetryInfo()->durationSinceStart()->asFloat();

        $this->results[] = new TestResult(
            $test->className(),
            $test->methodName(),
            max(0.0, $finishedAt - $startedAt),
            $event->numberOfAssertionsPerformed(),
            $this->failures[$id] ?? null,
            $this->errors[$id] ?? null
        );

        unset(
            $this->startedAt[$id],
            $this->failures[$id],
            $this->errors[$id]
        );
    }

    /** @return list<TestResult> */
    public function results(): array
    {
        return $this->results;
    }
}
