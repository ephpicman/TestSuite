<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Command;

use InvalidArgumentException;

/**
 * Registry for commands provided by EphpicMan products and integrations.
 */
final class CommandRegistry
{
    /** @var array<string, Command> */
    private array $commands = [];

    /**
     * Registers a command by its Symfony command name.
     *
     * @throws InvalidArgumentException If another command uses the same name.
     */
    public function register(Command $command): void
    {
        $name = $command->getName();

        if ($name === null || $name === '') {
            throw new InvalidArgumentException(
                'EphpicMan commands must have a name.'
            );
        }

        if (isset($this->commands[$name])) {
            throw new InvalidArgumentException(
                sprintf('The EphpicMan command "%s" is already registered.', $name)
            );
        }

        $this->commands[$name] = $command;
    }

    /** @return list<Command> */
    public function all(): array
    {
        return array_values($this->commands);
    }
}
