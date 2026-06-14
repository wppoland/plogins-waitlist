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
    private const ACCOUNT_ENDPOINT = 'restock-waitlists';

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
                'variation_required' => __('Select product options before joining the waitlist.', 'restock'),
                'notify_subject' => __('Product back in stock - {product_name}', 'restock'),
                'notify_intro' => __('Product {product_name} is back in stock.', 'restock'),
                'notify_outro' => __('If you no longer wish to receive these messages, simply ignore this email.', 'restock'),
                'unsubscribe_success' => __('You have been removed from this waitlist.', 'restock'),
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
        add_action('init', [$this, 'registerAccountEndpoint']);
        add_filter('woocommerce_account_menu_items', [$this, 'addAccountMenuItem']);
        add_action('woocommerce_account_' . self::ACCOUNT_ENDPOINT . '_endpoint', [$this, 'renderAccountEndpoint']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAccountAssets'], 15);
        add_action('wp_enqueue_scripts', [$this, 'enqueueVariationDependency'], 9);
        add_action('wp_ajax_restock_waitlist_unsubscribe', [$this, 'handleUnsubscribe']);
    }

    public function registerAccountEndpoint(): void
    {
        add_rewrite_endpoint(self::ACCOUNT_ENDPOINT, EP_ROOT | EP_PAGES);
    }

    /**
     * @param array<string, string> $items
     * @return array<string, string>
     */
    public function addAccountMenuItem(array $items): array
    {
        $settings = $this->getSettings();

        if (empty($settings['show_in_account'])) {
            return $items;
        }

        $logout = $items['customer-logout'] ?? null;
        unset($items['customer-logout']);

        $items[self::ACCOUNT_ENDPOINT] = (string) ($settings['account_menu_label'] ?? __('Waitlists', 'restock'));

        if ($logout !== null) {
            $items['customer-logout'] = $logout;
        }

        return $items;
    }

    public function renderAccountEndpoint(): void
    {
        if (! is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped in templates/myaccount/waitlists.php.
        echo $this->templateLoader->render('myaccount/waitlists', [
            'subscriptions' => $this->repository->findActiveForAccount((int) $user->ID, (string) $user->user_email),
            'settings' => $this->getSettings(),
        ]);
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function enqueueVariationDependency(): void
    {
        if (! is_product()) {
            return;
        }

        global $product;

        if ($product instanceof \WC_Product && $product->is_type('variable')) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }
    }

    public function enqueueAccountAssets(): void
    {
        if (! function_exists('is_account_page') || ! is_account_page() || ! is_user_logged_in()) {
            return;
        }

        if (empty($this->getSettings()['show_in_account'])) {
            return;
        }

        if (function_exists('is_wc_endpoint_url') && ! is_wc_endpoint_url(self::ACCOUNT_ENDPOINT)) {
            return;
        }

        wp_enqueue_style(
            'restock-waitlist',
            \Restock\Plugin::instance()->url('assets/css/waitlist.css'),
            [],
            \Restock\VERSION,
        );
        wp_enqueue_script(
            'restock-waitlist',
            \Restock\Plugin::instance()->url('assets/js/waitlist.js'),
            [],
            \Restock\VERSION,
            [
                'in_footer' => true,
                'strategy' => 'defer',
            ],
        );

        wp_localize_script('restock-waitlist', 'restockWaitlist', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'action' => 'restock_waitlist_subscribe',
            'unsubscribeAction' => 'restock_waitlist_unsubscribe',
            'nonce' => wp_create_nonce('restock_waitlist'),
            'errorText' => __('Something went wrong. Please try again.', 'restock'),
            'unsubscribeSuccess' => __('You have been removed from this waitlist.', 'restock'),
        ]);
    }

    public function handleUnsubscribe(): void
    {
        check_ajax_referer('restock_waitlist');

        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['nonce'])), 'restock_waitlist')) {
            wp_send_json_error(['message' => __('Invalid request.', 'restock')], 403);
        }

        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => __('Login required.', 'restock')], 401);
        }

        $subscriptionId = isset($_POST['subscription_id']) ? absint(wp_unslash($_POST['subscription_id'])) : 0;

        if ($subscriptionId < 1) {
            wp_send_json_error(['message' => __('Invalid request.', 'restock')], 400);
        }

        $user = wp_get_current_user();

        if (! $this->repository->deleteForAccountOwner($subscriptionId, (int) $user->ID, (string) $user->user_email)) {
            wp_send_json_error(['message' => __('Could not remove this waitlist entry.', 'restock')], 404);
        }

        wp_send_json_success([
            'message' => (string) ($this->getSettings()['unsubscribe_success_text'] ?? __('You have been removed from this waitlist.', 'restock')),
        ]);
    }

    /**
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

        if (empty($settings['show_on_single'])) {
            return '';
        }

        if (! $product->is_type('variable') && $product->is_in_stock() && $product->get_stock_status() !== 'onbackorder') {
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
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $defaults = [
            'allow_guests'      => true,
            'show_on_single'    => true,
            'show_in_account'   => true,
        ];

        $options = get_option('restock_settings', []);

        return array_merge($defaults, is_array($options) ? $options : []);
    }
}
