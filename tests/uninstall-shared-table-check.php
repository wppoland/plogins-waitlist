<?php

declare(strict_types=1);

/**
 * Fails when uninstall drops the waitlist table while the PRO add-on, which
 * adds subscribers to it as well, is still installed, or leaves it behind once
 * the add-on is gone, or cleans only one site of a network.
 *
 * Runs uninstall.php against a fake $wpdb with and without the add-on on disk
 * (under its current and its pre-rename text domain) and reads the DROPs. The
 * last run is a bulk delete of both plugins: get_plugins() still holds the
 * add-on in its request cache though the add-on is gone from disk. A network
 * run with two sites must drop both tables and clear both sites' options and
 * scheduled batches.
 *
 *   php tests/uninstall-shared-table-check.php [path/to/uninstall.php]
 */

define('ABSPATH', sys_get_temp_dir() . '/');
define('WP_UNINSTALL_PLUGIN', 'plogins-waitlist/plogins-waitlist.php');

$GLOBALS['addon'] = null;
$GLOBALS['cache'] = null;
$GLOBALS['multisite'] = false;
$GLOBALS['blog'] = 1;
$GLOBALS['done'] = [];

// Like core, the list is cached for the request and only wp_cache_delete()
// makes get_plugins() read the disk again.
function wp_cache_delete($key, $group = '')
{
    if ('plugins' === $key && 'plugins' === $group) {
        $GLOBALS['cache'] = null;
    }

    return true;
}
function get_plugins()
{
    if (null !== $GLOBALS['cache']) {
        return $GLOBALS['cache'];
    }
    $plugins = ['plogins-waitlist/plogins-waitlist.php' => ['TextDomain' => 'plogins-waitlist']];
    if ($GLOBALS['addon'] !== null) {
        $plugins['renamed-folder/main.php'] = ['TextDomain' => $GLOBALS['addon']];
    }

    return $GLOBALS['cache'] = $plugins;
}
function wp_unschedule_hook($hook)
{
    $GLOBALS['done'][] = $GLOBALS['blog'] . ':unschedule:' . $hook;

    return 0;
}
function delete_option($name)
{
    $GLOBALS['done'][] = $GLOBALS['blog'] . ':option:' . $name;

    return true;
}
function is_multisite()
{
    return $GLOBALS['multisite'];
}
function get_sites($args = [])
{
    return [1, 2];
}
function switch_to_blog($id)
{
    global $wpdb;
    $GLOBALS['blog'] = (int) $id;
    $wpdb->prefix = 1 === (int) $id ? 'wp_' : "wp_{$id}_";

    return true;
}
function restore_current_blog()
{
    return switch_to_blog(1);
}
function delete_metadata(...$args)
{
    return true;
}

final class FakeWpdb
{
    public string $prefix = 'wp_';
    /** @var list<string> */
    public array $queries = [];

    public function query(string $sql): int
    {
        $this->queries[] = $sql;

        return 0;
    }
}

$file = $argv[1] ?? dirname(__DIR__) . '/uninstall.php';

function drops_waitlist(string $file, ?string $addon, ?array $cached = null): bool
{
    global $wpdb;
    $GLOBALS['addon'] = $addon;
    $GLOBALS['cache'] = $cached;
    $wpdb = new FakeWpdb();
    include $file;

    return preg_grep('/DROP TABLE IF EXISTS `?wp_restock_waitlist`?/', $wpdb->queries) !== [];
}

$fail = [];
if (! drops_waitlist($file, null)) {
    $fail[] = 'with no add-on installed, uninstall leaves wp_restock_waitlist behind';
}
foreach (['plogins-waitlist-pro', 'restock-pro'] as $addon) {
    if (drops_waitlist($file, $addon)) {
        $fail[] = "with the add-on ({$addon}) installed, uninstall drops wp_restock_waitlist it still writes to";
    }
}
// Bulk delete: the add-on was uninstalled and removed earlier in this request.
$stale = [
    'plogins-waitlist/plogins-waitlist.php'         => ['TextDomain' => 'plogins-waitlist'],
    'plogins-waitlist-pro/plogins-waitlist-pro.php' => ['TextDomain' => 'plogins-waitlist-pro'],
];
if (! drops_waitlist($file, null, $stale)) {
    $fail[] = 'bulk delete of both plugins: uninstall trusts the stale get_plugins() cache and leaves wp_restock_waitlist behind';
}

// Network: both sites lose their table, options and queued batches.
$GLOBALS['multisite'] = true;
$GLOBALS['done'] = [];
$GLOBALS['addon'] = null;
$GLOBALS['cache'] = null;
$wpdb = new FakeWpdb();
include $file;
foreach ([1 => 'wp_', 2 => 'wp_2_'] as $blog => $prefix) {
    if (preg_grep("/DROP TABLE IF EXISTS `?{$prefix}restock_waitlist`?/", $wpdb->queries) === []) {
        $fail[] = "multisite: uninstall leaves {$prefix}restock_waitlist behind on site {$blog}";
    }
    foreach (["{$blog}:option:restock_settings", "{$blog}:unschedule:plogins_waitlist_notify_batch"] as $want) {
        if (! in_array($want, $GLOBALS['done'], true)) {
            $fail[] = "multisite: uninstall skips {$want}";
        }
    }
}

if ($fail !== []) {
    foreach ($fail as $f) {
        fwrite(STDERR, "FAIL: {$f}\n");
    }
    exit(1);
}

echo "ok: wp_restock_waitlist is dropped only once the add-on is gone, on every site\n";
