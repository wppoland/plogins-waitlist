<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Restock\Admin\Assets;
use Restock\Admin\Settings;
use Restock\Admin\Subscribers;
use Restock\Service\WaitlistService;

/**
 * Ordered list of HasHooks services to register during plugin booting.
 *
 * Admin-only classes are included only when running in wp-admin context.
 */
return is_admin()
    ? [
        WaitlistService::class,
        Settings::class,
        Subscribers::class,
        Assets::class,
    ]
    : [
        WaitlistService::class,
    ];
