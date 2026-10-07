<?php

declare(strict_types=1);

namespace EphpicMan\TestSuite\ErrorFixtures;

use EphpicMan\TestSuite\UnitTesting\UnitTest;

final class ErrorFixtureTest extends UnitTest
{
    public function testErrors(): void
    {
        throw new \RuntimeException('fixture error');
    }
