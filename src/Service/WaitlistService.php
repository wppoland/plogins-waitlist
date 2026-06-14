<?php

declare(strict_types=1);

namespace Restock\Service;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;
use Restock\Repository\WaitlistRepository;
use Restock\Util\TemplateLoader;
use WPPoland\StorefrontKit\Waitlist\WaitlistEngine;

final class WaitlistService implements HasHooks
{
    private readonly WaitlistEngine $engine;

    public function __construct(
        private readonly WaitlistRepository $repository,
        private readonly TemplateLoader $templateLoader,
    ) {
        $this->engine = new WaitlistEngine(
            repository: $this->repository,
            ajaxAction: 'restock_waitlist_subscribe',
            nonceAction: 'restock_waitlist',
            scriptObjectName: 'restockWaitlist',
            assetHandle: 'restock-waitlist',
            styleUrl: \Restock\Plugin::instance()->url('assets/css/waitlist.css'),
            scriptUrl: \Restock\Plugin::instance()->url('assets/js/waitlist.js'),
            version: \Restock\VERSION,
            templateName: 'single-product/waitlist-form',
            defaultMessages: [
                'generic_error' => __('Something went wrong. Please try again.', 'restock'),
                'product_not_found' => __('Product not found.', 'restock'),
                'disabled' => __('Waitlist is unavailable for this product.', 'restock'),
                'invalid_email' => __('Provide a valid email address.', 'restock'),
                'privacy_error' => __('You must accept the consent for email contact.', 'restock'),
                'login_required' => __('Login to join the waitlist.', 'restock'),
                'success' => __('Thank you. You have been added to the waitlist.', 'restock'),
                'notify_subject' => __('Product back in stock - {product_name}', 'restock'),
                'notify_intro' => __('Product {product_name} is back in stock.', 'restock'),
                'notify_outro' => __('If you no longer wish to receive these messages, simply ignore this email.', 'restock'),
            ],
            isEnabled: fn (): bool => $this->isEnabled(),
            settings: fn (): array => $this->getSettings(),
            renderTemplate: function (string $template, array $data): void {
                $this->templateLoader->include($template, $data);
            },
        );
    }

    public function registerHooks(): void
    {
        $this->engine->registerHooks();

        add_shortcode('restock_waitlist', [$this, 'renderShortcode']);
    }

    /**
     * Render the waitlist form via a shortcode so stores can place it manually
     * (e.g. in a custom product template or page builder) instead of relying on
     * the default single-product summary placement.
     *
     * Usage: [restock_waitlist] (current product) or [restock_waitlist id="123"].
     *
     * The form's JS/CSS are enqueued by the engine on single-product pages, so
     * the shortcode is intended for use within a product template/layout. On a
     * product page the assets are already present and the async submit works.
     *
     * @param array<string, mixed>|string $atts Shortcode attributes.
     */
    public function renderShortcode(array|string $atts = []): string
    {
        $atts = shortcode_atts(['id' => 0], is_array($atts) ? $atts : [], 'restock_waitlist');

        $productId = absint($atts['id']);
        $product   = $productId > 0 ? wc_get_product($productId) : ($GLOBALS['product'] ?? null);

        if (! $product instanceof \WC_Product) {
            return '';
        }

        $settings = $this->getSettings();

        // Mirror the engine's own visibility rules: respect the show_on_single
        // toggle and only render for products that are out of stock / on backorder.
        if (empty($settings['show_on_single'])) {
            return '';
        }

        if ($product->is_in_stock() && $product->get_stock_status() !== 'onbackorder') {
            return '';
        }

        return $this->templateLoader->render('single-product/waitlist-form', [
            'product'  => $product,
            'settings' => $settings,
            'email'    => is_user_logged_in() ? wp_get_current_user()->user_email : '',
        ]);
    }

    public function isEnabled(): bool
    {
        // Restock has no global on/off switch; placement is controlled per
        // context (single product hook + shortcode). Always enabled.
        return true;
    }

    /**
     * Merged settings: persisted options on top of sensible defaults.
     *
     * The keys here intentionally mirror every option the WaitlistEngine and the
     * front-end template read, so the admin Settings page can expose them all
     * rather than leaving the engine on hardcoded fallbacks.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $defaults = [
            'allow_guests'   => true,
            'show_on_single' => true,
        ];

        $options = get_option('restock_settings', []);

        return array_merge($defaults, is_array($options) ? $options : []);
    }
}
