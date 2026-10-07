<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests\Unit;

use Composer\Autoload\ClassLoader;
use EphpicMan\TestSuite\Autoloader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AutoloaderTest extends TestCase
{
    public function testCanRegisterNamespace(): void
    {
        $autoloader = new Autoloader(new ClassLoader());

        $autoloader->addNamespace(
            'EphpicMan\\TestSuite\\Fixtures',
            __DIR__ . '/../fixtures'
        );

        self::assertTrue(true);
    }

    public function testCanLoadClass(): void
    {
        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace(
            'EphpicMan\\TestSuite\\Fixtures',
            __DIR__ . '/../fixtures'
        );
        $autoloader->register();

        self::assertTrue(
            class_exists('EphpicMan\\TestSuite\\Fixtures\\Example')
        );
    }

    public function testIgnoresUnknownClass(): void
    {
        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace(
            'EphpicMan\\TestSuite\\Fixtures',
            __DIR__ . '/../fixtures'
        );
        $autoloader->register();

        self::assertFalse(
            class_exists(
                'EphpicMan\\TestSuite\\Fixtures\\DoesNotExist',
                false
            )
        );
    }

    public function testCanRegisterMultipleNamespaces(): void
    {
        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace(
            'EphpicMan\\TestSuite\\Fixtures',
            __DIR__ . '/../fixtures'
        );
        $autoloader->addNamespace(
            'EphpicMan\\TestSuite\\OtherFixtures',
            __DIR__ . '/../other-fixtures'
        );
        $autoloader->register();

        self::assertTrue(
            class_exists('EphpicMan\\TestSuite\\Fixtures\\Example')
        );
        self::assertTrue(
            class_exists(
                'EphpicMan\\TestSuite\\OtherFixtures\\OtherExample'
            )
        );
    }

    public function testCanNormaliseNamespace(): void
    {
        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace(
            '\\EphpicMan\\TestSuite\\Fixtures\\',
            __DIR__ . '/../fixtures/'
        );
        $autoloader->register();

        self::assertTrue(
            class_exists('EphpicMan\\TestSuite\\Fixtures\\Normalised')
        );
    }

    public function testRejectsEmptyNamespace(): void
    {
        $autoloader = new Autoloader(new ClassLoader());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Namespace cannot be empty.');

        $autoloader->addNamespace('', __DIR__ . '/../fixtures');
    }

    public function testRejectsEmptyDirectory(): void
    {
        $autoloader = new Autoloader(new ClassLoader());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Namespace directory cannot be empty.');

        $autoloader->addNamespace('EphpicMan\\TestSuite\\Fixtures', '');
    }
}
