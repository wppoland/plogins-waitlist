<?php

/**
 * A shop manager must be able to save the Waitlist settings.
 *
 * The page is shown to manage_woocommerce, but the form posts to options.php,
 * which requires manage_options unless option_page_capability_<group> says
 * otherwise. Without the filter every save by a shop manager died with
 * "Sorry, you are not allowed to manage options for this site."
 *
 * Run: php tests/settings-capability-check.php
 */

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);

    $GLOBALS['filters'] = [];
    $GLOBALS['groups']  = [];
    $GLOBALS['caps']    = [];

    function add_action(string $hook, callable $cb): void {}
    function add_filter(string $hook, callable $cb): void
    {
        $GLOBALS['filters'][$hook][] = $cb;
    }
    function apply_filters(string $hook, mixed $value): mixed
    {
        foreach ($GLOBALS['filters'][$hook] ?? [] as $cb) {
            $value = $cb($value);
        }
        return $value;
    }
    function add_submenu_page(string $parent, string $title, string $menu, string $cap): void
    {
        $GLOBALS['caps'][] = $cap;
    }
    function register_setting(string $group): void
    {
        $GLOBALS['groups'][] = $group;
    }
    function add_settings_section(): void {}
    function add_settings_field(): void {}
    function __(string $text): string
    {
        return $text;
    }

    $root = dirname(__DIR__);
    require $root . '/src/Contract/HasHooks.php';
    require $root . '/src/Admin/ProUpsell.php';
    require $root . '/src/Admin/Settings.php';

    $settings = new Waitlist\Admin\Settings();
    $settings->registerHooks();
    $settings->addMenuPage();
    $settings->registerSettings();

    if ($GLOBALS['caps'] !== ['manage_woocommerce'] || $GLOBALS['groups'] === []) {
        fwrite(STDERR, "FAIL: expected one manage_woocommerce page with a settings group\n");
        exit(1);
    }
    foreach ($GLOBALS['groups'] as $group) {
        $cap = apply_filters('option_page_capability_' . $group, 'manage_options');
        if ($cap !== 'manage_woocommerce') {
            fwrite(STDERR, "FAIL: options.php requires {$cap} to save '{$group}'\n");
            exit(1);
        }
    }

    echo "OK: shop managers can save " . implode(', ', $GLOBALS['groups']) . "\n";
}
