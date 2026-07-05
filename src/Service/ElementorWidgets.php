<?php

declare(strict_types=1);

namespace Restock\Service;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;
use Restock\Elementor\WaitlistWidget;

/**
 * Elementor integration service.
 *
 * Registers the waitlist Elementor widget. The `elementor/widgets/register`
 * action only fires when Elementor is active, so this service is self-guarding:
 * nothing loads unless Elementor is present. Works on Elementor 3.x and 4.0.
 */
final class ElementorWidgets implements HasHooks
{
    /**
     * Register WordPress hooks.
     */
    public function registerHooks(): void
    {
        add_action('elementor/widgets/register', [$this, 'register']);
    }

    /**
     * Register widget instances with Elementor's widgets manager.
     *
     * @param \Elementor\Widgets_Manager $widgetsManager Elementor widgets manager.
     */
    public function register($widgetsManager): void
    {
        // Loaded here (not autoloaded) so \Elementor\Widget_Base always exists.
        require_once __DIR__ . '/../Elementor/WaitlistWidget.php';
        $widgetsManager->register(new WaitlistWidget());
    }
}
