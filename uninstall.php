<?php

/**
 * Restock uninstall routine.
 *
 * Drops the plugin table and removes plugin options when the user deletes
 * the plugin from the WordPress admin.
 *
 * @package Restock
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

// Drop the waitlist table.
$restock_table = $wpdb->prefix . 'restock_waitlist';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from $wpdb->prefix, cannot be parameterised.
$wpdb->query( "DROP TABLE IF EXISTS {$restock_table}" );

// Remove options.
delete_option( 'restock_settings' );
delete_option( 'restock_schema_version' );
