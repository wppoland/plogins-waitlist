<?php

declare(strict_types=1);

namespace Waitlist\Email;

defined('ABSPATH') || exit;

use Waitlist\Service\GuestUnsubscribe;

use const Waitlist\PLUGIN_DIR;

/**
 * Back-in-stock email, editable under WooCommerce > Settings > Emails.
 *
 * Triggered directly by the batch runner through trigger(), not from an action
 * registered in this constructor: WooCommerce builds its emails only when
 * WC()->mailer() is first called, and on a cron or Action Scheduler run nothing
 * else calls it, so a constructor hook would never be listening.
 */
class RestockEmail extends \WC_Email
{
    /** Key in WC()->mailer()->emails. */
    public const KEY = 'Waitlist_Restock_Email';

    private int $subscriptionId = 0;

    private int $targetProductId = 0;

    public function __construct()
    {
        $this->id = 'plogins_waitlist_restock';
        $this->customer_email = true;
        $this->title = __('Back in stock (Waitlist)', 'plogins-waitlist');
        $this->description = __('Sent to shoppers on a waitlist when the product they waited for is back in stock.', 'plogins-waitlist');
        $this->template_html = 'emails/waitlist-restock.php';
        $this->template_plain = 'emails/plain/waitlist-restock.php';
        $this->template_base = PLUGIN_DIR . '/templates/';
        $this->placeholders = ['{product_name}' => ''];

        parent::__construct();
    }

    /**
     * Older installs set the subject on the plugin's own settings screen; it
     * stays the default until the merchant saves one here.
     */
    public function get_default_subject()
    {
        $settings = $this->pluginSettings();

        return ! empty($settings['notify_subject'])
            ? (string) $settings['notify_subject']
            : __('Product back in stock - {product_name}', 'plogins-waitlist');
    }

    public function get_default_heading()
    {
        return __('{product_name} is back in stock', 'plogins-waitlist');
    }

    public function trigger(int $subscriptionId, string $email, \WC_Product $product, int $targetProductId): bool
    {
        $this->setup_locale();

        $this->object = $product;
        $this->recipient = $email;
        $this->subscriptionId = $subscriptionId;
        $this->targetProductId = $targetProductId;
        $this->placeholders['{product_name}'] = $product->get_name();

        $sent = false;

        if ($this->is_enabled() && $this->get_recipient()) {
            $sent = (bool) $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }

        $this->restore_locale();

        return $sent;
    }

    public function get_headers()
    {
        $headers = parent::get_headers();

        if ($this->subscriptionId > 0) {
            $headers .= 'List-Unsubscribe: <' . esc_url_raw($this->unsubscribeUrl()) . ">\r\n";
            $headers .= "List-Unsubscribe-Post: List-Unsubscribe=One-Click\r\n";
        }

        return $headers;
    }

    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, $this->templateArgs(false), '', $this->template_base);
    }

    public function get_content_plain()
    {
        return wc_get_template_html($this->template_plain, $this->templateArgs(true), '', $this->template_base);
    }

    /**
     * @return array<string, mixed>
     */
    private function templateArgs(bool $plainText): array
    {
        $product = $this->object instanceof \WC_Product ? $this->object : null;
        $settings = $this->pluginSettings();
        $name = $product !== null ? $product->get_name() : '';

        $intro = ! empty($settings['notify_intro_text'])
            ? (string) $settings['notify_intro_text']
            : __('Product {product_name} is back in stock.', 'plogins-waitlist');

        return [
            'email_heading' => $this->get_heading(),
            'product' => $product,
            'product_url' => $product !== null ? (string) get_permalink($this->targetProductId > 0 ? $this->targetProductId : $product->get_id()) : '',
            'image_url' => $product !== null && $product->get_image_id() ? (string) wp_get_attachment_image_url((int) $product->get_image_id(), 'woocommerce_thumbnail') : '',
            'intro' => str_replace('{product_name}', $name, $intro),
            'outro' => ! empty($settings['notify_outro_text'])
                ? (string) $settings['notify_outro_text']
                : __('If you no longer wish to receive these messages, simply ignore this email.', 'plogins-waitlist'),
            'unsubscribe_url' => $this->subscriptionId > 0 ? $this->unsubscribeUrl() : '',
            'additional_content' => $this->get_additional_content(),
            'sent_to_admin' => false,
            'plain_text' => $plainText,
            'email' => $this,
        ];
    }

    private function unsubscribeUrl(): string
    {
        return GuestUnsubscribe::url($this->subscriptionId, (string) $this->recipient);
    }

    /**
     * @return array<string, mixed>
     */
    private function pluginSettings(): array
    {
        $settings = get_option('restock_settings', []);

        return is_array($settings) ? $settings : [];
    }
}
