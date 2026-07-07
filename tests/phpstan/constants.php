<?php
/**
 * Constants needed by PHPStan to analyse the plugin without bootstrapping WordPress.
 *
 * @package Waitlist
 */

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    // WC_VERSION is provided by the WooCommerce stubs bootstrap file.
}

namespace Waitlist {
    if (! defined('Waitlist\\VERSION')) {
        define('Waitlist\\VERSION', '0.1.0');
    }
    if (! defined('Waitlist\\PLUGIN_FILE')) {
        define('Waitlist\\PLUGIN_FILE', '/tmp/restock/restock.php');
    }
    if (! defined('Waitlist\\PLUGIN_DIR')) {
        define('Waitlist\\PLUGIN_DIR', '/tmp/restock');
    }
    if (! defined('Waitlist\\MIN_PHP_VERSION')) {
        define('Waitlist\\MIN_PHP_VERSION', '8.1.0');
    }
    if (! defined('Waitlist\\MIN_WC_VERSION')) {
        define('Waitlist\\MIN_WC_VERSION', '8.0.0');
    }
}
