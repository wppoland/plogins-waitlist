<?php

declare(strict_types=1);

namespace Restock\Admin;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;
use Restock\Plugin;

/**
 * Enqueues the admin stylesheet and tooltip script, but only on Restock's own
 * settings screens, so nothing leaks into the rest of wp-admin.
 */
final class Assets implements HasHooks
{
    private const HANDLE = 'restock-admin';

    /**
     * Hook suffixes of the Restock admin pages where assets should load.
     * Matches the `$hook_suffix` passed to `admin_enqueue_scripts`.
     */
    private const PAGE_HOOKS = [
        'woocommerce_page_restock-settings',
        'restock_page_restock-subscribers',
    ];

    public function registerHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hookSuffix): void
    {
        if (! in_array($hookSuffix, self::PAGE_HOOKS, true)) {
            return;
        }

        $plugin = Plugin::instance();

        wp_enqueue_style(
            self::HANDLE,
            $plugin->url('assets/css/admin.css'),
            [],
            \Restock\VERSION,
        );

        wp_enqueue_script(
            self::HANDLE,
            $plugin->url('assets/js/admin.js'),
            [],
            \Restock\VERSION,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ],
        );
    }
}
