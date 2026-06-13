<?php

declare(strict_types=1);

namespace Restock;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;

/**
 * Main plugin orchestrator: wires the DI container, runs migrations, and boots
 * every HasHooks service listed in config/hooks.php.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    private Container $container;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();

        // Register service factories up front so the container is usable from the
        // activation hook, which fires during activate_plugin() — BEFORE plugins_loaded
        // and boot() ever run. (Lazy closures: nothing is instantiated here.)
        (require PLUGIN_DIR . '/config/services.php')($this->container);
    }

    public static function instance(): Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Absolute path to the plugin directory (with optional relative path appended).
     */
    public function path(string $relative = ''): string
    {
        return PLUGIN_DIR . ($relative !== '' ? '/' . ltrim($relative, '/') : '');
    }

    /**
     * URL to the plugin directory (with optional relative path appended).
     */
    public function url(string $relative = ''): string
    {
        return plugins_url($relative, PLUGIN_FILE);
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        // Run any pending DB migrations.
        $this->container->get(Migrator::class)->run();

        // Boot hook subscribers in declared order.
        /** @var array<class-string<HasHooks>> $hooks */
        $hooks = require PLUGIN_DIR . '/config/hooks.php';
        foreach ($hooks as $hookClass) {
            $service = $this->container->get($hookClass);
            if ($service instanceof HasHooks) {
                $service->registerHooks();
            }
        }

        /**
         * Fires after Restock has booted. The PRO plugin extends Restock here.
         *
         * @param Plugin $plugin The booted plugin instance.
         */
        do_action('restock/booted', $this);
    }
}
