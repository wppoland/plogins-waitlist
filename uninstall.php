<?php

/**
 * Waitlist uninstall routine.
 *
 * Drops the plugin table and removes plugin options when the user deletes
 * the plugin from the WordPress admin, on every site of a network.
 *
 * @package Waitlist
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// Drop the waitlist table, unless the PRO add-on is still installed (active or
// not): it adds subscribers to this table too, so whichever of the two is
// removed last drops it. Matched on the text domain, current and pre-rename,
// so a renamed plugin folder still counts. tests/uninstall-shared-table-check.php
// keeps this in step with the add-on's cleanup.
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
// A bulk delete of both plugins uninstalls them in one request, and
// get_plugins() caches its list for the request: the add-on deleted a moment
// ago would still be listed, and the table would never be dropped.
wp_cache_delete( 'plugins', 'plugins' );
$plogins_waitlist_drop_table = array_intersect( array( 'plogins-waitlist-pro', 'restock-pro' ), array_column( get_plugins(), 'TextDomain' ) ) === array();

// Every site of a network keeps its own table, options and scheduled events,
// so the cleanup visits each of them.
$plogins_waitlist_cleanup = static function () use ( $plogins_waitlist_drop_table ) {
	global $wpdb;

	// A queued restock mailing must not outlive the plugin.
	//
	// The deactivation hook already unschedules it, but `wp plugin delete`
	// removes an active plugin without deactivating it first, and then
	// uninstall.php is the only thing that runs. Without this the batch events
	// stayed in the options table, waking on every request with nothing left
	// to answer them.
	//
	// The hook name is a literal: this file runs with the plugin's classes
	// unloaded, and an older Plogins Waitlist PRO supplies a WaitlistEngine
	// that has no such constant. tests/batching-test.php keeps the literal in
	// step with WaitlistEngine::NOTIFY_HOOK.
	wp_unschedule_hook('plogins_waitlist_notify_batch');

	// Batches queued since 1.0.32 live in Action Scheduler, not WP-Cron. It is
	// there only while WooCommerce is loaded, hence the guard.
	if (function_exists('as_unschedule_all_actions')) {
		as_unschedule_all_actions('plogins_waitlist_notify_batch');
	}

	if ( $plogins_waitlist_drop_table ) {
		$restock_table = $wpdb->prefix . 'restock_waitlist';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from $wpdb->prefix, cannot be parameterised.
		$wpdb->query( "DROP TABLE IF EXISTS {$restock_table}" );
	}

	// Remove options.
	delete_option( 'restock_settings' );
	delete_option( 'restock_schema_version' );
	// Settings of the Back in stock (Waitlist) WooCommerce email.
	delete_option( 'woocommerce_plogins_waitlist_restock_settings' );
};

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $plogins_waitlist_site_id ) {
		switch_to_blog( (int) $plogins_waitlist_site_id );
		$plogins_waitlist_cleanup();
		restore_current_blog();
	}
	unset( $plogins_waitlist_site_id );
} else {
	$plogins_waitlist_cleanup();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'restock_pro_banner_dismissed', '', true);
