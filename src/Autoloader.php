<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;

/**
 * Provides the legacy EphpicMan namespace registration API on top of Composer.
 *
 * The class intentionally delegates PSR-4 resolution to Composer's
 * ClassLoader rather than maintaining a second autoloader implementation.
 */
final class Autoloader
{
    /**
     * Creates an autoloader backed by a Composer class loader.
     *
     * @param ClassLoader $loader Composer class loader used for PSR-4 resolution.
     */
    public function __construct(
        private readonly ClassLoader $loader
    ) {
    }

    /**
     * Registers a PSR-4 namespace prefix.
     *
     * The namespace is normalised before being passed to Composer. The
     * directory is used as the base directory for the namespace prefix.
     *
     * @param string $namespace Namespace prefix, with or without a trailing slash.
     * @param string $directory Base directory for the namespace.
     *
     * @throws InvalidArgumentException If the namespace or directory is empty.
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
     * Registers this adapter with PHP's SPL autoloader stack.
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    /**
     * Delegates class resolution to Composer.
     *
     * @param string $class Fully-qualified class name requested by PHP.
     */
    private function load(string $class): void
    {
        $this->loader->loadClass($class);
    }
}
