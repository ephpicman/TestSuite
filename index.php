<?php

/**
 * Plugin Name:       EphpicMan Test Suite
 * Description:       Professional WordPress testing and diagnostic toolkit.
 * Version:           1.0
 * Requires at least: 6.5
 * Requires PHP:      8.2.27
 * Author:            EphpicMan
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ephpicman
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$composerLoader = require __DIR__ . '/vendor/autoload.php';

$autoloader = new \EphpicMan\TestSuite\Autoloader($composerLoader);
$autoloader->register();
