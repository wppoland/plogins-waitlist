<?php

declare(strict_types=1);

namespace Restock\Elementor;

defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Elementor widget: Back-in-Stock Waitlist.
 *
 * A thin wrapper around the [restock_waitlist] shortcode so the waitlist form
 * can be placed with the Elementor editor. Kept deliberately minimal (renders
 * the shortcode) so a future migration to Elementor v4 atomic widgets stays
 * localized to this class. Loaded only from the `elementor/widgets/register`
 * hook, so the `\Elementor\Widget_Base` base class is guaranteed to exist here.
 * Compatible with Elementor 3.x and 4.0.
 */
final class WaitlistWidget extends Widget_Base
{
    /**
     * Widget machine name.
     */
    public function get_name(): string
    {
        return 'restock_waitlist';
    }

    /**
     * Widget label shown in the editor.
     */
    public function get_title(): string
    {
        return esc_html__('Back-in-Stock Waitlist', 'plogins-waitlist');
    }

    /**
     * Editor panel icon.
     */
    public function get_icon(): string
    {
        return 'eicon-bell';
    }

    /**
     * Editor panel categories.
     *
     * @return string[]
     */
    public function get_categories(): array
    {
        return ['woocommerce-elements', 'general'];
    }

    /**
     * Search keywords in the editor.
     *
     * @return string[]
     */
    public function get_keywords(): array
    {
        return ['waitlist', 'restock', 'back in stock', 'stock', 'notify', 'woocommerce'];
    }

    /**
     * Register the editor controls.
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'content',
            ['label' => esc_html__('Waitlist', 'plogins-waitlist')]
        );

        $this->add_control(
            'product_id',
            [
                'label'       => esc_html__('Product ID', 'plogins-waitlist'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 0,
                'min'         => 0,
                'description' => esc_html__('Leave 0 to use the current product on a product page.', 'plogins-waitlist'),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget on the front end and in the editor preview.
     */
    protected function render(): void
    {
        $settings  = $this->get_settings_for_display();
        $productId = isset($settings['product_id']) ? absint($settings['product_id']) : 0;

        echo do_shortcode(sprintf('[restock_waitlist id="%d"]', $productId));
    }
}
