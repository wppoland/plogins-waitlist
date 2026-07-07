<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Waitlist\Admin\Assets;
use Waitlist\Admin\Settings;
use Waitlist\Admin\Subscribers;
use Waitlist\Service\ElementorWidgets;
use Waitlist\Service\WaitlistService;

/**
 * Ordered list of HasHooks services to register during plugin booting.
 *
 * Admin-only classes are included only when running in wp-admin context.
 */
return is_admin()
    ? [
        WaitlistService::class,
        ElementorWidgets::class,
        Settings::class,
        Subscribers::class,
        Assets::class,
    ]
    : [
        WaitlistService::class,
        ElementorWidgets::class,
    ];
