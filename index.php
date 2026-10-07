<?php

/**
 * WordPress plugin bootstrap.
 *
 * The bootstrap intentionally contains only the WordPress guard, Composer
 * autoloader loading and plugin bootstrapping. Application behaviour lives in
 * the namespaced classes under src/.
 *
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

require __DIR__ . '/vendor/autoload.php';

\EphpicMan\TestSuite\Plugin::instance()->boot();
