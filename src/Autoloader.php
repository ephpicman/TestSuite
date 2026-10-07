<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;

final class Autoloader
{
    public function __construct(
        private readonly ClassLoader $loader
    ) {
    }

    /**
     * Register a PSR-4 namespace prefix.
     *
     * @param string $namespace Namespace prefix.
     * @param string $directory Base directory for the namespace.
     */
    public function addNamespace(
        string $namespace,
        string $directory
    ): void {
        $namespace = trim($namespace, '\\');

        if ($namespace === '') {
            throw new InvalidArgumentException(
                'Namespace cannot be empty.'
            );
        }

        $directory = rtrim(
            $directory,
            DIRECTORY_SEPARATOR . '/\\'
        );

        if ($directory === '') {
            throw new InvalidArgumentException(
                'Namespace directory cannot be empty.'
            );
        }

        $this->loader->addPsr4(
            $namespace . '\\',
            $directory,
            true
        );
    }

    /**
     * Register this autoloader with PHP.
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    /**
     * Resolve a class using the configured Composer class loader.
     */
    private function load(string $class): void
    {
        $this->loader->loadClass($class);
    }
}
