<?php

declare(strict_types=1);

namespace Waitlist\Contract;

defined('ABSPATH') || exit;

/**
 * A service that registers its own WordPress hooks.
 */
interface HasHooks
{
    public function registerHooks(): void;
}
