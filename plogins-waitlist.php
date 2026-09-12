<?php

declare(strict_types=1);

/**
 * Plugin Name:       Waitlist - Back in Stock for WooCommerce
 * Plugin URI:        https://plogins.com/plogins-waitlist/
 * Description:       Lightweight, accessible back-in-stock / waitlist notifications for WooCommerce. Built with Core Web Vitals and WCAG 2.2 AA in mind.
 * Version:           1.0.27
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plogins-waitlist
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 8.0
 * WC tested up to:      11.0
 */

namespace Waitlist;

defined('ABSPATH') || exit;

const VERSION     = '1.0.27';

// Legacy aliases for Plogins Waitlist PRO <= 1.0.2 (it coupled to the old `Restock\` namespace
// + `restock/booted`). Safe to remove once all PRO installs are >= 1.0.3.
if (! defined('Restock\\VERSION')) {
    define('Restock\\VERSION', VERSION);
}
if (! class_exists('Restock\\Plugin', false)) {
    class_alias(Plugin::class, 'Restock\\Plugin');
}
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

/**
 * A queued restock mailing must not outlive the plugin.
 *
 * The batches are single events carrying a cursor in their arguments, so
 * wp_clear_scheduled_hook() (which only matches events with the same
 * arguments) would leave them behind. wp_unschedule_hook() clears the hook
 * whatever arguments an event was queued with.
 *
 * The hook name is a literal, not WaitlistEngine::NOTIFY_HOOK. Reading that
 * constant autoloads the class, and an older Plogins Waitlist PRO prepends an
 * autoloader carrying its own WPPoland\StorefrontKit\Waitlist\WaitlistEngine
 * with no such constant. Deactivating this plugin then threw an uncaught Error
 * before core wrote `active_plugins`, so the plugin could not be switched off
 * at all. Nothing in a deactivation path may depend on which copy of a shared
 * class won the autoloader. tests/batching-test.php asserts this literal still
 * equals WaitlistEngine::NOTIFY_HOOK.
 */
register_deactivation_hook(PLUGIN_FILE, static function (): void {
    wp_unschedule_hook('plogins_waitlist_notify_batch');
});
