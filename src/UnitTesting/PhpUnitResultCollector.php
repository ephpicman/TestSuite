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
    /** @var array<string, float> */
    private array $startedAt = [];

    /** @var array<string, Failure> */
    private array $failures = [];

    /** @var array<string, string> */
    private array $errors = [];

    /** @var list<TestResult> */
    private array $results = [];

    public function trace(Event $event): void
    {
        if ($event instanceof PreparationStarted) {
            $this->startedAt[$event->test()->id()] = $event->telemetryInfo()->durationSinceStart()->asFloat();

            return;
        }

        if ($event instanceof Failed) {
            if ($event->hasComparisonFailure()) {
                $comparison = $event->comparisonFailure();

                $this->failures[$event->test()->id()] = new Failure(
                    $event->throwable()->message(),
                    $comparison->expected(),
                    $comparison->actual()
                );
            } else {
                $this->failures[$event->test()->id()] = new Failure(
                    $event->throwable()->message()
                );
            }

            return;
        }

        if ($event instanceof Errored) {
            $this->errors[$event->test()->id()] = $event->throwable()->message();

            return;
        }

        if ($event instanceof Skipped) {
            $this->errors[$event->test()->id()] = 'Skipped: ' . $event->throwable()->message();

            return;
        }

        if ($event instanceof MarkedIncomplete) {
            $this->errors[$event->test()->id()] = 'Incomplete: ' . $event->throwable()->message();

            return;
        }

        if (! $event instanceof Finished) {
            return;
        }

        $test = $event->test();

        if (! $test instanceof TestMethod) {
            return;
        }

        $id = $test->id();
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
