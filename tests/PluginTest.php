<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\Tests;

use EphpicMan\TestSuite\Autoloader;
use EphpicMan\TestSuite\Plugin;
use EphpicMan\TestSuite\UnitTesting\UnitTest;

final class PluginTest extends UnitTest
{
    public function testReturnsSameInstance(): void
    {
        $first = Plugin::instance();
        $second = Plugin::instance();

        $this->assertSame($first, $second);
    }

    public function testProvidesAutoloader(): void
    {
        $this->assertInstanceOf(
            Autoloader::class,
            Plugin::instance()->autoloader()
        );
    }

    public function testProvidesExistingTestDirectories(): void
    {
        $directories = Plugin::instance()->getTestsDirectories();

        $this->assertContains(
            dirname(__DIR__) . '/tests',
            $directories
        );
    }
}
