<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests\Unit\Command;

use EphpicMan\TestSuite\Command\Command;
use EphpicMan\TestSuite\Command\CommandRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CommandRegistryTest extends TestCase
{
    public function testRegistersCommands(): void
    {
        $registry = new CommandRegistry();
        $command = new class extends Command {
            public function __construct()
            {
                parent::__construct('example', 'Example command.');
            }
        };

        $registry->register($command);

        self::assertSame([$command], $registry->all());
    }

    public function testRejectsDuplicateCommandNames(): void
    {
        $registry = new CommandRegistry();

        $first = new class extends Command {
            public function __construct()
            {
                parent::__construct('example');
            }
        };

        $second = new class extends Command {
            public function __construct()
            {
                parent::__construct('example');
            }
        };

        $registry->register($first);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The EphpicMan command "example" is already registered.'
        );

        $registry->register($second);
    }
}
