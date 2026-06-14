<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// If composer vendor autoloader is present, use it.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    return;
}

// Fallback PSR-4 autoloader.
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Restock\\' => __DIR__ . '/src/',
        'WPPoland\\StorefrontKit\\' => __DIR__ . '/vendor/wppoland/storefront-kit/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
        return;
    }
});
