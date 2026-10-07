<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\UnitTesting;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Tracer\Tracer;

/**
 * Converts PHPUnit test events into the stable EphpicMan result model.
 *
 * PHPUnit owns execution and failure semantics. This collector only observes
 * those events and translates the information needed by the WordPress UI.
 *
 * @psalm-suppress InternalMethod
 * @psalm-suppress MissingOverrideAttribute
 */
final class PhpUnitResultCollector implements Tracer
{
    /** @var array<string, true> Test classes belonging to this Runner invocation. */
    private array $testClasses;

    /** @var array<string, float> Start timestamps keyed by PHPUnit test ID. */
    private array $startedAt = [];

    /** @var array<string, Failure> Assertion failures keyed by PHPUnit test ID. */
    private array $failures = [];

    /** @var array<string, string> Errors keyed by PHPUnit test ID. */
    private array $errors = [];

    /** @var list<TestResult> Completed test results. */
    private array $results = [];

    /**
     * @param list<class-string<UnitTest>> $testClasses Test classes owned by this collector.
     */
    public function __construct(array $testClasses)
    {
        $this->testClasses = array_fill_keys($testClasses, true);
    }

    /**
     * Records relevant PHPUnit events for the configured test classes.
     *
     * PHPUnit's event facade is process-wide. Filtering by class here prevents
     * a nested or unrelated PHPUnit execution from polluting this collector.
     */
    /** @psalm-suppress MissingOverrideAttribute */
    public function trace(Event $event): void
    {
        if (
            ! $event instanceof PreparationStarted
            && ! $event instanceof Failed
            && ! $event instanceof Errored
            && ! $event instanceof Skipped
            && ! $event instanceof MarkedIncomplete
            && ! $event instanceof Finished
        ) {
            return;
        }

        $test = $event->test();

        if (! $test instanceof TestMethod) {
            return;
        }

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
            $this->errors[$id] = 'Skipped: ' . $event->message();

            return;
        }

        if ($event instanceof MarkedIncomplete) {
            $this->errors[$id] = 'Incomplete: ' . $event->throwable()->message();

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

    /**
     * Returns all completed results collected during this execution.
     *
     * @return list<TestResult>
     */
    public function results(): array
    {
        return $this->results;
    }
}
