<?php

declare(strict_types=1);

namespace Waitlist\Contract;

defined('ABSPATH') || exit;

/**
 * A service that is booted on startup.
 */
interface Bootable
{
    public function boot(): void;
}
