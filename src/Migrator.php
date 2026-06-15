<?php

declare(strict_types=1);

namespace Restock;

defined('ABSPATH') || exit;

/**
 * Runs versioned schema migrations once per version. Tracks the applied
 * schema version in the `restock_schema_version` option.
 */
final class Migrator
{
    private const OPTION = 'restock_schema_version';

    /** Ordered list of migration class short-names under Restock\Migration. */
    private const MIGRATIONS = [
        'Migration_0_1_0',
        'Migration_0_2_1',
    ];

    public function run(): void
    {
        $applied = (string) get_option(self::OPTION, '');
        $migrations = self::MIGRATIONS;

        foreach ($migrations as $name) {
            if (version_compare($this->versionOf($name), $applied, '>')) {
                /** @var class-string $class */
                $class = 'Restock\\Migration\\' . $name;
                if (class_exists($class) && method_exists($class, 'migrate')) {
                    $class::migrate();
                }
            }
        }

        $latest = $migrations[count($migrations) - 1];
        update_option(self::OPTION, $this->versionOf($latest));
    }

    private function versionOf(string $migrationName): string
    {
        // "Migration_0_1_0" -> "0.1.0"
        return str_replace('_', '.', substr($migrationName, strlen('Migration_')));
    }
}
