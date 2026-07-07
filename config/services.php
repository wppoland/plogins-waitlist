<?php

declare(strict_types=1);

namespace Waitlist;

defined('ABSPATH') || exit;

use Waitlist\Admin\Assets;
use Waitlist\Admin\Settings;
use Waitlist\Admin\Subscribers;
use Waitlist\Repository\WaitlistRepository;
use Waitlist\Service\ElementorWidgets;
use Waitlist\Service\WaitlistService;
use Waitlist\Util\TemplateLoader;

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

    // Elementor integration (self-guards on the elementor/widgets/register hook)
    $c->singleton(ElementorWidgets::class, static fn (): ElementorWidgets => new ElementorWidgets());

    // Admin (only loaded in wp-admin context)
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
        $c->singleton(Subscribers::class, static fn (): Subscribers => new Subscribers(
            $c->get(WaitlistRepository::class),
        ));
        $c->singleton(Assets::class, static fn (): Assets => new Assets());
    }
};
