<?php
/**
 * PHPStan needs more than the 512M a stock PHP CLI gives it.
 *
 * The WooCommerce stub file alone is several megabytes, and analysis of this
 * plugin reliably crashed with "PHPStan process crashed because it reached
 * configured PHP memory limit: 512M". PHPStan has no config key for the memory
 * limit, so the limit lived only in the --memory-limit=2G flag on the composer
 * script, and anyone running vendor/bin/phpstan directly (which is how the
 * release notes record these runs) got the crash instead of the result.
 *
 * Raising it from a bootstrap file puts the setting in the repo, where it
 * belongs, and applies it to the parallel workers as well as the main process.
 *
 * @package Waitlist
 */

declare(strict_types=1);

if ((int) ini_get('memory_limit') !== -1) {
    ini_set('memory_limit', '2G');
}
