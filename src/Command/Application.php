<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Command;

use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * Symfony Console application populated from the EphpicMan command registry.
 */
final class Application extends SymfonyApplication
{
    public function __construct(CommandRegistry $registry)
    {
        parent::__construct('EphpicMan');

        foreach ($registry->all() as $command) {
            $this->addCommand($command);
        }
    }
}
