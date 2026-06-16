<?php

declare(strict_types=1);

namespace Restock\Admin;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;

/**
 * Admin settings page registered under the WooCommerce menu.
 *
 * Stores settings in the `restock_settings` option (array).
 */
final class Settings implements HasHooks
{
    private const OPTION = 'restock_settings';
    private const PAGE   = 'restock-settings';
    private const SECTION_GENERAL = 'restock_general';
    private const SECTION_DISPLAY = 'restock_display';
    private const SECTION_FORM    = 'restock_form';
    private const SECTION_MESSAGES = 'restock_messages';
    private const SECTION_EMAIL   = 'restock_email';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Restock Settings', 'restock'),
            __('Restock', 'restock'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // ── General ──────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_GENERAL,
            __('General', 'restock'),
            '__return_false',
            self::PAGE,
        );

        add_settings_field(
            'allow_guests',
            __('Allow guest subscriptions', 'restock'),
            [$this, 'renderCheckbox'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'    => 'allow_guests',
                'label' => __('Allow visitors who are not logged in to subscribe.', 'restock'),
                'help'  => __('When on, anyone can join the waitlist by entering their email. When off, only logged-in customers can subscribe and guests see your "login required" message instead. Turn off to keep your list tied to real accounts.', 'restock'),
            ],
        );

        add_settings_field(
            'show_on_single',
            __('Show form on product page', 'restock'),
            [$this, 'renderCheckbox'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'    => 'show_on_single',
                'label' => __('Display the waitlist form on single product pages.', 'restock'),
                'help'  => __('Automatically shows the form on each product page when that product is out of stock or on backorder. On variable products, the form appears after the shopper selects an unavailable variation. Turn off only if you place the form yourself with the [restock_waitlist] shortcode.', 'restock'),
            ],
        );

        add_settings_field(
            'show_in_account',
            __('My Account waitlists', 'restock'),
            [$this, 'renderCheckbox'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'    => 'show_in_account',
                'label' => __('Show a Waitlists tab in WooCommerce My Account.', 'restock'),
                'help'  => __('Logged-in customers can review active waitlists and leave a list from My Account. After enabling, visit Settings → Permalinks and click Save once if the tab returns a 404.', 'restock'),
            ],
        );

        add_settings_field(
            'account_menu_label',
            __('My Account menu label', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'          => 'account_menu_label',
                'placeholder' => __('Waitlists', 'restock'),
                'description' => __('Label for the My Account menu item.', 'restock'),
                'help'        => __('Wording of the menu item customers click in My Account to see the products they are waiting for. Only shown when "My Account waitlists" is on. Leave blank to use the default.', 'restock'),
            ],
        );

        // ── Display ─────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_DISPLAY,
            __('Heading & intro', 'restock'),
            static function (): void {
                echo '<p>' . esc_html__(
                    'Optional heading and introductory text shown above the waitlist form.',
                    'restock',
                ) . '</p>';
            },
            self::PAGE,
        );

        add_settings_field(
            'show_title',
            __('Show heading', 'restock'),
            [$this, 'renderCheckbox'],
            self::PAGE,
            self::SECTION_DISPLAY,
            [
                'id'    => 'show_title',
                'label' => __('Display a heading above the form.', 'restock'),
                'help'  => __('Shows the heading text (set below) above the form, for example "Notify me when available". Hide it for a more compact form.', 'restock'),
            ],
        );

        add_settings_field(
            'title',
            __('Heading text', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_DISPLAY,
            [
                'id'          => 'title',
                'placeholder' => __('Notify me when available', 'restock'),
                'help'        => __('The heading shown above the form (only when "Show heading" is on). Keep it short and reassuring. Leave blank to use the placeholder shown here.', 'restock'),
            ],
        );

        add_settings_field(
            'show_intro',
            __('Show intro text', 'restock'),
            [$this, 'renderCheckbox'],
            self::PAGE,
            self::SECTION_DISPLAY,
            [
                'id'    => 'show_intro',
                'label' => __('Display intro text above the form.', 'restock'),
                'help'  => __('Shows a short paragraph (set below) between the heading and the form to explain what subscribers will receive.', 'restock'),
            ],
        );

        add_settings_field(
            'intro_text',
            __('Intro text', 'restock'),
            [$this, 'renderTextarea'],
            self::PAGE,
            self::SECTION_DISPLAY,
            [
                'id'          => 'intro_text',
                'placeholder' => __('Leave your email and we will let you know the moment this product is back.', 'restock'),
                'help'        => __('Introductory sentence shown above the form (only when "Show intro text" is on). Use it to set expectations, e.g. one email, no spam. Leave blank to use the placeholder shown here.', 'restock'),
            ],
        );

        // ── Form labels ───────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_FORM,
            __('Form labels', 'restock'),
            '__return_false',
            self::PAGE,
        );

        add_settings_field(
            'email_label',
            __('Email field label', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_FORM,
            [
                'id'          => 'email_label',
                'placeholder' => __('Email address', 'restock'),
                'help'        => __('Accessible label for the email field, read out by screen readers. It is visually hidden on the form but important for accessibility. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'email_placeholder',
            __('Email field placeholder', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_FORM,
            [
                'id'          => 'email_placeholder',
                'placeholder' => __('Your email address', 'restock'),
                'help'        => __('Greyed-out hint shown inside the empty email field. It disappears as soon as the shopper types. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'privacy_label',
            __('Consent checkbox label', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_FORM,
            [
                'id'          => 'privacy_label',
                'placeholder' => __('I consent to receiving back-in-stock notifications.', 'restock'),
                'help'        => __('Text next to the required consent checkbox. Shoppers must tick it before they can subscribe, so word it clearly for GDPR/marketing-consent compliance. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'button_text',
            __('Button text', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_FORM,
            [
                'id'          => 'button_text',
                'placeholder' => __('Join Waitlist', 'restock'),
                'help'        => __('Label on the submit button. An action-oriented phrase such as "Notify me" or "Join Waitlist" works best. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'variation_prompt_text',
            __('Variable product prompt', 'restock'),
            [$this, 'renderTextarea'],
            self::PAGE,
            self::SECTION_FORM,
            [
                'id'          => 'variation_prompt_text',
                'placeholder' => __('Select options above, then join the waitlist when that variation is unavailable.', 'restock'),
                'description' => __('Shown above the form on variable products.', 'restock'),
                'help'        => __('Guidance shown above the form on variable products, telling shoppers to pick a variation first. The form then appears once they choose an unavailable one. Leave blank to use the default.', 'restock'),
            ],
        );

        // ── Messages ────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_MESSAGES,
            __('Form messages', 'restock'),
            static function (): void {
                echo '<p>' . esc_html__(
                    'Messages shown to shoppers after they submit the form. Leave blank to use the built-in defaults.',
                    'restock',
                ) . '</p>';
            },
            self::PAGE,
        );

        add_settings_field(
            'success_text',
            __('Success message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'success_text',
                'placeholder' => __('Thank you. You have been added to the waitlist.', 'restock'),
                'help'        => __('Confirmation shown after a shopper successfully joins the waitlist. Reassure them you will email when the item returns. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'invalid_email_text',
            __('Invalid email message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'invalid_email_text',
                'placeholder' => __('Provide a valid email address.', 'restock'),
                'help'        => __('Error shown when the entered email address is missing or malformed. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'privacy_error_text',
            __('Missing consent message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'privacy_error_text',
                'placeholder' => __('You must accept the consent for email contact.', 'restock'),
                'help'        => __('Error shown when a shopper submits without ticking the consent checkbox. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'login_required_text',
            __('Login required message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'login_required_text',
                'placeholder' => __('Login to join the waitlist.', 'restock'),
                'description' => __('Shown when guest subscriptions are disabled and the visitor is not logged in.', 'restock'),
                'help'        => __('Only relevant when "Allow guest subscriptions" is off. Tell visitors they need an account, ideally with a link to your login page. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'variation_required_text',
            __('Variation required message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'variation_required_text',
                'placeholder' => __('Select product options before joining the waitlist.', 'restock'),
                'help'        => __('Error shown when a shopper tries to subscribe without choosing a variation on variable products. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'unsubscribe_success_text',
            __('Unsubscribe success message', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_MESSAGES,
            [
                'id'          => 'unsubscribe_success_text',
                'placeholder' => __('You have been removed from this waitlist.', 'restock'),
                'help'        => __('Confirmation shown after a customer leaves a waitlist from My Account. Leave blank to use the default.', 'restock'),
            ],
        );

        // ── Email ─────────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_EMAIL,
            __('Notification email', 'restock'),
            static function (): void {
                echo '<p>' . esc_html__(
                    'These texts are used when a product comes back in stock and subscribers are notified.',
                    'restock',
                ) . '</p>';
            },
            self::PAGE,
        );

        add_settings_field(
            'notify_subject',
            __('Email subject', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_EMAIL,
            [
                'id'          => 'notify_subject',
                'placeholder' => __('Product back in stock - {product_name}', 'restock'),
                'description' => __('Use {product_name} as a placeholder for the product title.', 'restock'),
                'help'        => __('Subject line of the email sent to subscribers when the product is restocked. Include {product_name} so each email names the exact product. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'notify_intro_text',
            __('Email intro text', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_EMAIL,
            [
                'id'          => 'notify_intro_text',
                'placeholder' => __('Product {product_name} is back in stock.', 'restock'),
                'description' => __('Use {product_name} as a placeholder for the product title.', 'restock'),
                'help'        => __('First line of the notification email body. The product link is added automatically on the next line. Use {product_name} to name the product. Leave blank to use the default.', 'restock'),
            ],
        );

        add_settings_field(
            'notify_outro_text',
            __('Email closing text', 'restock'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_EMAIL,
            [
                'id'          => 'notify_outro_text',
                'placeholder' => __('If you no longer wish to receive these messages, simply ignore this email.', 'restock'),
                'help'        => __('Closing line of the notification email, shown after the product link. A good place for a brief reassurance or sign-off. Leave blank to use the default.', 'restock'),
            ],
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }
        ?>
        <div class="wrap restock-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="restock-admin__lead">
                <?php esc_html_e('Configure the back-in-stock waitlist form that appears on out-of-stock products. Hover or focus the ? icons for guidance on each option. Empty text fields fall back to sensible built-in defaults.', 'restock'); ?>
            </p>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::PAGE);
                do_settings_sections(self::PAGE);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Builds an accessible inline-help affordance: a "?" button whose tooltip
     * text is wired to it via aria-describedby and exposed as role="tooltip".
     * Usable on hover and keyboard focus; the help text is always read out by
     * screen readers even without JavaScript.
     */
    private function helpIcon(string $id, string $text): string
    {
        if ($text === '') {
            return '';
        }

        $bubbleId = 'restock-help-' . $id;

        return sprintf(
            '<button type="button" class="restock-help" aria-describedby="%1$s" aria-expanded="false">'
            . '<span aria-hidden="true">?</span>'
            . '<span class="screen-reader-text">%2$s</span>'
            . '<span class="restock-help__bubble" id="%1$s" role="tooltip">%3$s</span>'
            . '</button>',
            esc_attr($bubbleId),
            /* translators: accessible name for the inline-help button. */
            esc_html__('More information', 'restock'),
            esc_html($text),
        );
    }

    /**
     * Renders a text input for a settings field.
     *
     * @param array<string, string> $args
     */
    public function renderText(array $args): void
    {
        $options = (array) get_option(self::OPTION, []);
        $id      = $args['id'] ?? '';
        $value   = isset($options[$id]) ? (string) $options[$id] : '';
        $placeholder = $args['placeholder'] ?? '';
        $description = $args['description'] ?? '';

        printf(
            '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" placeholder="%4$s" class="regular-text" />',
            esc_attr($id),
            esc_attr(self::OPTION),
            esc_attr($value),
            esc_attr($placeholder),
        );

        // Help icon is pre-escaped HTML built by helpIcon().
        echo $this->helpIcon($id, $args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ($description !== '') {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    /**
     * Renders a textarea for a settings field.
     *
     * @param array<string, string> $args
     */
    public function renderTextarea(array $args): void
    {
        $options     = (array) get_option(self::OPTION, []);
        $id          = $args['id'] ?? '';
        $value       = isset($options[$id]) ? (string) $options[$id] : '';
        $placeholder = $args['placeholder'] ?? '';
        $description = $args['description'] ?? '';

        printf(
            '<textarea id="%1$s" name="%2$s[%1$s]" placeholder="%4$s" class="large-text" rows="2">%3$s</textarea>',
            esc_attr($id),
            esc_attr(self::OPTION),
            esc_textarea($value),
            esc_attr($placeholder),
        );

        // Help icon is pre-escaped HTML built by helpIcon().
        echo $this->helpIcon($id, $args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ($description !== '') {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    /**
     * Renders a checkbox for a settings field.
     *
     * @param array<string, string> $args
     */
    public function renderCheckbox(array $args): void
    {
        $options = (array) get_option(self::OPTION, []);
        $id      = $args['id'] ?? '';
        $default = in_array($id, ['allow_guests', 'show_on_single', 'show_in_account'], true);
        $checked = isset($options[$id]) ? (bool) $options[$id] : $default;
        $label   = $args['label'] ?? '';

        printf(
            '<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s /> %4$s</label>',
            esc_attr($id),
            esc_attr(self::OPTION),
            checked($checked, true, false),
            esc_html($label),
        );

        // Help icon is pre-escaped HTML built by helpIcon().
        echo $this->helpIcon($id, $args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Sanitizes and coerces the incoming POST values before they are saved.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $clean = [
            // General.
            'allow_guests'        => ! empty($raw['allow_guests']),
            'show_on_single'      => ! empty($raw['show_on_single']),
            'show_in_account'     => ! empty($raw['show_in_account']),
            'account_menu_label'   => sanitize_text_field((string) ($raw['account_menu_label'] ?? '')),
            // Display.
            'show_title'          => ! empty($raw['show_title']),
            'title'               => sanitize_text_field((string) ($raw['title'] ?? '')),
            'show_intro'          => ! empty($raw['show_intro']),
            'intro_text'          => sanitize_textarea_field((string) ($raw['intro_text'] ?? '')),
            // Form labels.
            'email_label'         => sanitize_text_field((string) ($raw['email_label'] ?? '')),
            'email_placeholder'   => sanitize_text_field((string) ($raw['email_placeholder'] ?? '')),
            'privacy_label'       => sanitize_text_field((string) ($raw['privacy_label'] ?? '')),
            'button_text'         => sanitize_text_field((string) ($raw['button_text'] ?? '')),
            'variation_prompt_text' => sanitize_textarea_field((string) ($raw['variation_prompt_text'] ?? '')),
            // Form messages (consumed by the WaitlistEngine AJAX handler).
            'success_text'        => sanitize_text_field((string) ($raw['success_text'] ?? '')),
            'invalid_email_text'  => sanitize_text_field((string) ($raw['invalid_email_text'] ?? '')),
            'privacy_error_text'  => sanitize_text_field((string) ($raw['privacy_error_text'] ?? '')),
            'login_required_text' => sanitize_text_field((string) ($raw['login_required_text'] ?? '')),
            'variation_required_text' => sanitize_text_field((string) ($raw['variation_required_text'] ?? '')),
            'unsubscribe_success_text' => sanitize_text_field((string) ($raw['unsubscribe_success_text'] ?? '')),
            // Email.
            'notify_subject'      => sanitize_text_field((string) ($raw['notify_subject'] ?? '')),
            'notify_intro_text'   => sanitize_text_field((string) ($raw['notify_intro_text'] ?? '')),
            'notify_outro_text'   => sanitize_text_field((string) ($raw['notify_outro_text'] ?? '')),
        ];

        // Drop empty optional text fields so the engine and template fall back to
        // their built-in defaults (their lookups use `?? $default`, which only
        // triggers on a missing key — not on an empty string).
        foreach ($clean as $key => $value) {
            if (! in_array($key, ['allow_guests', 'show_on_single', 'show_in_account', 'show_title', 'show_intro'], true) && $value === '') {
                unset($clean[$key]);
            }
        }

        return $clean;
    }
}
