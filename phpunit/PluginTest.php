<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\PHPUnit;

use EphpicMan\TestSuite\Autoloader;
use EphpicMan\TestSuite\Plugin;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    public function testReturnsSameInstance(): void
    {
        $this->assertSame(Plugin::instance(), Plugin::instance());
    }

    public function testProvidesAutoloader(): void
    {
        $this->assertInstanceOf(Autoloader::class, Plugin::instance()->autoloader());
    }

}
