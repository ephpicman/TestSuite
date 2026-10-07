<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Command\WPCLI;

use EphpicMan\TestSuite\Command\Application;
use EphpicMan\TestSuite\Command\CommandRegistry;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Bridges the single "epm" WP-CLI command to the EphpicMan command application.
 */
final class Bridge
{
    public function __construct(
        private readonly CommandRegistry $registry
    ) {
    }

    /** Registers the EphpicMan gateway command with WP-CLI. */
    public function register(): void
    {
        if (! class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command('epm', [$this, 'run']);
    }

    /**
     * Runs the EphpicMan command application.
     *
     * WP-CLI remains responsible for bootstrapping WordPress. EphpicMan owns
     * parsing and executing everything after the "epm" command.
     *
     * @param list<string> $args
     * @param array<string, mixed> $assocArgs
     */
    public function run(array $args, array $assocArgs): void
    {
        unset($args, $assocArgs);

        $argv = $this->getEphpicManArgv();

        $application = new Application($this->registry);
        $exitCode = $application->run(
            new ArgvInput($argv),
            new ConsoleOutput()
        );

        if ($exitCode !== 0) {
            \WP_CLI::halt($exitCode);
        }
    }

    /** @return list<string> Arguments beginning with the EphpicMan command name. */
    private function getEphpicManArgv(): array
    {
        $argv = $GLOBALS['argv'] ?? null;

        if (! is_array($argv)) {
            throw new RuntimeException(
                'Unable to access the WP-CLI process arguments.'
            );
        }

        $epmIndex = array_search('epm', $argv, true);

        if ($epmIndex === false) {
            throw new RuntimeException(
                'Unable to locate the EphpicMan "epm" command in the process arguments.'
            );
        }

        return array_values(array_slice($argv, $epmIndex));
    }
}
