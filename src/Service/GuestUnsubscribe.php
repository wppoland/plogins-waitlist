<?php

declare(strict_types=1);

namespace Waitlist\Service;

defined('ABSPATH') || exit;

use Waitlist\Contract\HasHooks;
use Waitlist\Repository\WaitlistRepository;

/**
 * "Leave this waitlist" link for shoppers without an account.
 *
 * The link carries the subscription id and an HMAC over id + email, so it
 * proves the holder received the mail without a login and without putting the
 * address in the URL. Opening it shows a confirm button rather than deleting
 * on GET, because mail scanners open every link in a message; a POST to the
 * same URL (the button, or a mail client's RFC 8058 one-click request from the
 * List-Unsubscribe header) removes the row. A wrong token, an unknown id and an
 * already removed row all get the same answer, so the link cannot be used to
 * learn whether an address is on a list.
 */
final class GuestUnsubscribe implements HasHooks
{
    public const ID_ARG = 'restock_leave';
    public const TOKEN_ARG = 'restock_token';

    /** Attempts allowed per IP address per window. */
    private const RATE_LIMIT = 20;
    private const RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

    public function __construct(
        private readonly WaitlistRepository $repository,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('template_redirect', [$this, 'handle'], 1);
    }

    public static function token(int $subscriptionId, string $email): string
    {
        return hash_hmac('sha256', $subscriptionId . '|' . strtolower(trim($email)), wp_salt('auth') . 'plogins-waitlist-leave');
    }

    public static function isValid(int $subscriptionId, string $email, string $token): bool
    {
        return $subscriptionId > 0 && $token !== '' && hash_equals(self::token($subscriptionId, $email), $token);
    }

    public static function url(int $subscriptionId, string $email): string
    {
        return add_query_arg(
            [
                self::ID_ARG => $subscriptionId,
                self::TOKEN_ARG => self::token($subscriptionId, $email),
            ],
            home_url('/'),
        );
    }

    public function handle(): void
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- The HMAC token is the credential; a nonce cannot survive in an email.
        if (! isset($_GET[self::ID_ARG])) {
            return;
        }

        $subscriptionId = absint(wp_unslash($_GET[self::ID_ARG]));
        $token = isset($_GET[self::TOKEN_ARG]) ? sanitize_text_field((string) wp_unslash($_GET[self::TOKEN_ARG])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $title = __('Leave waitlist', 'plogins-waitlist');

        if ($this->rateLimited()) {
            wp_die(esc_html__('Too many requests. Please try again in a few minutes.', 'plogins-waitlist'), esc_html($title), ['response' => 429]);
        }

        $subscription = $subscriptionId > 0 ? $this->repository->findById($subscriptionId) : null;

        if ($subscription === null || ! self::isValid($subscriptionId, $subscription->email, $token)) {
            $this->respond(__('This link is not valid, or you have already left this waitlist.', 'plogins-waitlist'), $title);
        }

        $isPost = isset($_SERVER['REQUEST_METHOD']) && strtoupper(sanitize_key(wp_unslash($_SERVER['REQUEST_METHOD']))) === 'POST';

        if ($isPost) {
            $this->repository->deleteById($subscriptionId);
            $settings = get_option('restock_settings', []);
            $message = is_array($settings) && ! empty($settings['unsubscribe_success_text'])
                ? (string) $settings['unsubscribe_success_text']
                : __('You have been removed from this waitlist.', 'plogins-waitlist');

            $this->respond($message, $title);
        }

        $product = wc_get_product($subscription->productId);
        $question = $product instanceof \WC_Product
            /* translators: %s: product name. */
            ? sprintf(__('Stop back-in-stock emails for %s?', 'plogins-waitlist'), $product->get_name())
            : __('Stop back-in-stock emails for this product?', 'plogins-waitlist');

        $form = sprintf(
            '<p>%1$s</p><form method="post" action="%2$s"><button type="submit" class="button">%3$s</button></form>',
            esc_html($question),
            esc_url(self::url($subscriptionId, $subscription->email)),
            esc_html__('Leave waitlist', 'plogins-waitlist'),
        );

        wp_die(wp_kses($form, ['p' => [], 'form' => ['method' => [], 'action' => []], 'button' => ['type' => [], 'class' => []]]), esc_html($title), ['response' => 200]);
    }

    private function respond(string $message, string $title): never
    {
        wp_die(
            esc_html($message),
            esc_html($title),
            [
                'response' => 200,
                'link_url' => esc_url(home_url('/')),
                'link_text' => esc_html__('Back to the shop', 'plogins-waitlist'),
            ],
        );
    }

    private function rateLimited(): bool
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $key = 'plogins_waitlist_leave_' . md5($ip);
        $count = (int) get_transient($key);

        if ($count >= self::RATE_LIMIT) {
            return true;
        }

        set_transient($key, $count + 1, self::RATE_WINDOW);

        return false;
    }
}
