<?php

/**
 * Waitlist uninstall routine.
 *
 * Drops the plugin table and removes plugin options when the user deletes
 * the plugin from the WordPress admin.
 *
 * @package Waitlist
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// A queued restock mailing must not outlive the plugin.
//
// The deactivation hook already unschedules it, but `wp plugin delete` removes
// an active plugin without deactivating it first, and then uninstall.php is the
// only thing that runs. Without this the batch events stayed in the options
// table, waking on every request with nothing left to answer them.
//
// The hook name is a literal: this file runs with the plugin's classes
// unloaded, and an older Plogins Waitlist PRO supplies a WaitlistEngine that
// has no such constant. tests/batching-test.php keeps the literal in step with
// WaitlistEngine::NOTIFY_HOOK.
wp_unschedule_hook('plogins_waitlist_notify_batch');

global $wpdb;

// Drop the waitlist table.
$restock_table = $wpdb->prefix . 'restock_waitlist';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from $wpdb->prefix, cannot be parameterised.
$wpdb->query( "DROP TABLE IF EXISTS {$restock_table}" );

// Remove options.
delete_option( 'restock_settings' );
delete_option( 'restock_schema_version' );

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'restock_pro_banner_dismissed', '', true);
