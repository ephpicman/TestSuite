<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use Composer\Autoload\ClassLoader;
use EphpicMan\TestSuite\Autoloader;
use PHPUnit\Framework\TestCase;

final class AutoloaderTest extends TestCase
{
    public function testLoadsClassesFromDynamicallyRegisteredNamespace(): void
    {
        $directory = $this->createFixtureDirectory();
        $class = 'EphpicMan\\TestSuite\\PHPUnitFixtures\\DynamicClass';

        $this->writeFixture(
            $directory,
            'DynamicClass.php',
            '<?php
declare(strict_types=1);

namespace EphpicMan\\TestSuite\\PHPUnitFixtures;

final class DynamicClass
{
    public function value(): string
    {
        return \'loaded\';
    }
}
'
        );

        try {
            $loader = new ClassLoader();
            $autoloader = new Autoloader($loader);

            $autoloader->addNamespace(
                'EphpicMan\\TestSuite\\PHPUnitFixtures',
                $directory
            );
            $autoloader->register();

            $this->assertTrue(class_exists($class));
            $this->assertSame('loaded', (new $class())->value());
        } finally {
            $this->removeFixtureDirectory($directory);
        }
    }

    public function testRejectsEmptyNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace('', sys_get_temp_dir());
    }

    public function testRejectsEmptyDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $autoloader = new Autoloader(new ClassLoader());
        $autoloader->addNamespace('EphpicMan\\TestSuite\\Fixtures', '');
    }

    private function createFixtureDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/ephpicman-test-suite-' . bin2hex(random_bytes(8));

        mkdir($directory, 0755, true);

        return $directory;
    }

    private function writeFixture(string $directory, string $filename, string $content): void
    {
        file_put_contents($directory . '/' . $filename, $content);
    }

    private function removeFixtureDirectory(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($directory);
    }
}
