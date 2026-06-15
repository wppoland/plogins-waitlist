<?php

declare(strict_types=1);

namespace Restock\Migration;

defined('ABSPATH') || exit;

/**
 * Adds variation_id to the waitlist table for per-variation subscriptions.
 */
final class Migration_0_2_1
{
    public static function migrate(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'restock_waitlist';

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Schema migration on own plugin table.
        $column = $wpdb->get_results(
            $wpdb->prepare('SHOW COLUMNS FROM %i LIKE %s', $table, 'variation_id'),
        );

        if (! is_array($column) || $column === []) {
            $wpdb->query("ALTER TABLE {$table} ADD COLUMN variation_id BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER product_id");
        }

        $indexes = $wpdb->get_results("SHOW INDEX FROM {$table}", ARRAY_A);
        $indexNames = is_array($indexes)
            ? array_unique(array_map(static fn (array $row): string => (string) ($row['Key_name'] ?? ''), $indexes))
            : [];

        if (in_array('uk_product_email', $indexNames, true)) {
            $wpdb->query("ALTER TABLE {$table} DROP INDEX uk_product_email");
        }

        if (! in_array('uk_product_variation_email', $indexNames, true)) {
            $wpdb->query("ALTER TABLE {$table} ADD UNIQUE KEY uk_product_variation_email (product_id, variation_id, email)");
        }

        if (! in_array('idx_product_variation_notified', $indexNames, true)) {
            $wpdb->query("ALTER TABLE {$table} ADD INDEX idx_product_variation_notified (product_id, variation_id, notified)");
        }
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
    }
}
