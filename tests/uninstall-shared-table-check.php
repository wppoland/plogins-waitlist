<?php

declare(strict_types=1);

/**
 * Fails when uninstall drops the waitlist table while the PRO add-on, which
 * adds subscribers to it as well, is still installed, or leaves it behind once
 * the add-on is gone.
 *
 * Runs uninstall.php against a fake $wpdb with and without the add-on on disk
 * (under its current and its pre-rename text domain) and reads the DROPs.
 *
 *   php tests/uninstall-shared-table-check.php [path/to/uninstall.php]
 */

define('ABSPATH', sys_get_temp_dir() . '/');
define('WP_UNINSTALL_PLUGIN', 'plogins-waitlist/plogins-waitlist.php');

$GLOBALS['addon'] = null;

function get_plugins()
{
    $plugins = ['plogins-waitlist/plogins-waitlist.php' => ['TextDomain' => 'plogins-waitlist']];
    if ($GLOBALS['addon'] !== null) {
        $plugins['renamed-folder/main.php'] = ['TextDomain' => $GLOBALS['addon']];
    }

    return $plugins;
}
function wp_unschedule_hook($hook)
{
    return 0;
}
function delete_option($name)
{
    return true;
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

function drops_waitlist(string $file, ?string $addon): bool
{
    global $wpdb;
    $GLOBALS['addon'] = $addon;
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

if ($fail !== []) {
    foreach ($fail as $f) {
        fwrite(STDERR, "FAIL: {$f}\n");
    }
    exit(1);
}

echo "ok: wp_restock_waitlist is dropped only once the add-on is gone\n";
