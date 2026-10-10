<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Commands;

use EphpicMan\TestSuite\Command\Command;
use EphpicMan\TestSuite\UnitTesting\Runner;
use EphpicMan\TestSuite\UnitTesting\TestResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EphpicMan Test Suite command.
 */
final class TestCommand extends Command
{
    /** @param list<string> $directories */
    public function __construct(
        private readonly array $directories
    ) {
        parent::__construct('test', 'Run the registered unit tests.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        unset($input);

        $results = new Runner($this->directories)->run();
        $passed = 0;
        $assertions = 0;

        foreach ($results as $result) {
            $this->writeResult($output, $result);

            if ($result->passed()) {
                $passed++;
            }

            $assertions += $result->assertions();
        }

        $total = count($results);
        $failed = $total - $passed;

        $output->writeln('');
        $output->writeln(
            sprintf(
                '%d tests, %d passed, %d failed, %d assertions.',
                $total,
                $passed,
                $failed,
                $assertions
            )
        );

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function writeResult(OutputInterface $output, TestResult $result): void
    {
        $status = $result->passed()
            ? 'PASS'
            : ($result->failure() !== null ? 'FAIL' : 'ERROR');

        $output->writeln(
            sprintf(
                '[%s] %s::%s (%d assertions, %.4fs)',
                $status,
                $result->class(),
                $result->method(),
                $result->assertions(),
                $result->duration()
            )
        );

        if ($result->failure() !== null) {
            $output->writeln('  ' . $result->failure()->message());
        }

        if ($result->error() !== null) {
            $output->writeln('  ' . $result->error());
        }
    }
}
