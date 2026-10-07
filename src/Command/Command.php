<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Base command exposed by the EphpicMan command API.
 *
 * Concrete commands extend this class instead of depending directly on
 * Symfony Console.
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Creates an EphpicMan command.
     *
     * @param string $name Command name as exposed below "wp epm".
     * @param string $description Short command description.
     */
    public function __construct(string $name, string $description = '')
    {
        parent::__construct($name);

        if ($description !== '') {
            $this->setDescription($description);
        }
    }
}
