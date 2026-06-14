<?php

declare(strict_types=1);

namespace Restock;

defined('ABSPATH') || exit;

use Restock\Admin\Assets;
use Restock\Admin\Settings;
use Restock\Admin\Subscribers;
use Restock\Repository\WaitlistRepository;
use Restock\Service\WaitlistService;
use Restock\Util\TemplateLoader;

/**
 * Service registration. Returns a callable that binds every service into the
 * container. Bindings are lazy.
 */
return static function (Container $c): void {
    // Infrastructure
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());
    $c->singleton(WaitlistRepository::class, static function (): WaitlistRepository {
        global $wpdb;
        return new WaitlistRepository($wpdb);
    });

    // Utilities
    $c->singleton(TemplateLoader::class, static fn (): TemplateLoader => new TemplateLoader());

    // Services
    $c->singleton(WaitlistService::class, static fn (): WaitlistService => new WaitlistService(
        $c->get(WaitlistRepository::class),
        $c->get(TemplateLoader::class),
    ));

    // Admin (only loaded in wp-admin context)
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
        $c->singleton(Subscribers::class, static fn (): Subscribers => new Subscribers(
            $c->get(WaitlistRepository::class),
        ));
        $c->singleton(Assets::class, static fn (): Assets => new Assets());
    }
};
