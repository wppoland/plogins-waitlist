<?php

declare(strict_types=1);

/**
 * Loads the plugin bootstrap the way WordPress does, and reports what happened.
 *
 * Run on its own: php tests/bootstrap-load.php
 * tests/batching-test.php runs it in a subprocess and reads the last line.
 *
 * A separate process on purpose. The thing being measured is what including
 * plogins-waitlist.php emits and defines by itself, and a test that has already
 * loaded half the plugin cannot see that.
 *
 * Prints one line: BOOTSTRAP {"alias":bool,"alias_target":string}
 * Anything else on stdout or stderr is a diagnostic the bootstrap emitted, which
 * is the failure this exists to catch.
 */

define('ABSPATH', __DIR__ . '/');

function add_action(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function register_activation_hook(string $file, $callback): void
{
}

function register_deactivation_hook(string $file, $callback): void
{
}

require_once dirname(__DIR__) . '/plogins-waitlist.php';

$alias = class_exists('Restock\\Plugin', false);

echo 'BOOTSTRAP ' . json_encode([
    'alias' => $alias,
    'alias_target' => $alias ? (new ReflectionClass('Restock\\Plugin'))->getName() : '',
]) . "\n";
