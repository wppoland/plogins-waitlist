<?php

declare(strict_types=1);

/**
 * Plugin Name:       Plogins Waitlist - Back in Stock for WooCommerce
 * Plugin URI:        https://plogins.com/plogins-waitlist/
 * Description:       Lightweight, accessible back-in-stock / waitlist notifications for WooCommerce. Built with Core Web Vitals and WCAG 2.2 AA in mind.
 * Version:           0.3.2
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Tested up to:      7.0
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plogins-waitlist
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 8.0
 * WC tested up to:      9.6
 */

namespace Restock;

defined('ABSPATH') || exit;

const VERSION = '0.3.2';
const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR = __DIR__;
const MIN_PHP_VERSION = '8.1.0';
const MIN_WC_VERSION = '8.0.0';

/**
 * Declare WooCommerce HPOS (Custom Order Tables) + Blocks compatibility.
 */
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', PLUGIN_FILE, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', PLUGIN_FILE, true);
    }
});

/**
 * Require PHP 8.1+ before doing anything else.
 */
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    add_action('admin_notices', static function (): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: 1: Required PHP version, 2: Current PHP version */
                __('Plogins Waitlist requires PHP %1$s or higher. You are running PHP %2$s.', 'plogins-waitlist'),
                MIN_PHP_VERSION,
                PHP_VERSION,
            )),
        );
    });
    return;
}

require_once PLUGIN_DIR . '/autoload.php';

/**
 * Boot once WooCommerce is confirmed present and recent enough.
 */
add_action('plugins_loaded', static function (): void {
    if (! defined('WC_VERSION')) {
        add_action('admin_notices', static function (): void {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('Plogins Waitlist requires WooCommerce to be installed and activated.', 'plogins-waitlist'),
            );
        });
        return;
    }

    if (version_compare(WC_VERSION, MIN_WC_VERSION, '<')) {
        add_action('admin_notices', static function (): void {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html(sprintf(
                    /* translators: 1: Required WC version, 2: Current WC version */
                    __('Plogins Waitlist requires WooCommerce %1$s or higher. You are running WooCommerce %2$s.', 'plogins-waitlist'),
                    MIN_WC_VERSION,
                    WC_VERSION,
                )),
            );
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);

register_activation_hook(PLUGIN_FILE, static function (): void {
    require_once PLUGIN_DIR . '/autoload.php';
    Plugin::instance()->container()->get(Migrator::class)->run();
    flush_rewrite_rules();
});
