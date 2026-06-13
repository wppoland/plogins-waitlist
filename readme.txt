=== Restock – Back in Stock Notifications for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, accessible back-in-stock / waitlist notifications for WooCommerce. No jQuery, no layout shift, WCAG 2.2 AA.

== Description ==

Restock adds a fast, accessible waitlist form to out-of-stock WooCommerce product pages. Shoppers leave their email; when the product is restocked, every pending subscriber is emailed automatically — using your site's own WordPress mailer, with no third-party service involved.

**Why Restock?**

* **No jQuery.** The subscribe form uses a tiny vanilla JS `fetch` call, loaded `defer` in the footer. Pages stay fast.
* **Server-rendered first.** The form is rendered in PHP on the product page; JavaScript only handles the asynchronous submit.
* **No layout shift.** The form lives in the normal document flow on the single-product summary — there is no lazy injection that pushes content around, so it does not hurt Cumulative Layout Shift.
* **WCAG 2.2 AA in mind.** Labelled email field (with a `screen-reader-text` label), a required consent checkbox, an `aria-live` status message for success/error feedback, and an `aria-busy` state while submitting.
* **Built-in email notifications.** When WooCommerce fires `woocommerce_product_set_stock_status` with status `instock`, Restock emails every pending subscriber via `wp_mail` and marks them notified. No queue or cron service required.
* **Privacy-first.** An explicit consent checkbox is required before anyone can join the waitlist.
* **Clean install.** One custom `{prefix}_restock_waitlist` table, version-tracked. Deleting the plugin drops the table and removes its options.

**Features**

* Waitlist form shown automatically on out-of-stock and backorder ("on backorder") product pages
* Asynchronous AJAX subscribe (no page reload, no jQuery)
* Email field pre-filled for logged-in customers
* Required consent checkbox for every signup
* Automatic plain-text email notification on restock, sent via `wp_mail`
* Customisable form labels, button text, and notification email subject/intro/closing text
* Toggle guest (not-logged-in) subscriptions on or off
* Admin subscriber list with per-product filter and CSV export
* Compatible with WooCommerce HPOS (Custom Order Tables) and Cart/Checkout Blocks

Built on the shared `wppoland/storefront-kit` WaitlistEngine.

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Restock from the WordPress plugin directory, or upload the `restock` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** screen.
4. Optionally visit **WooCommerce → Restock** to customise labels and notification text; sensible defaults work out of the box.
5. The waitlist form appears automatically on any out-of-stock or backorder product page.

== Frequently Asked Questions ==

= Is Restock free? =
Yes. Restock is free and licensed under the GPL.

= Does Restock require WooCommerce? =
Yes. Restock is a WooCommerce extension and requires WooCommerce 8.0 or later. It will show an admin notice and stay inactive if WooCommerce is missing or out of date.

= Can guests (visitors who are not logged in) join the waitlist? =
Yes by default. You can restrict signups to logged-in customers by unchecking **Allow guest subscriptions** in **WooCommerce → Restock**.

= How are notifications sent? =
When WooCommerce sets a product's stock status to `instock`, Restock sends a plain-text email to every pending subscriber for that product (and its parent, for variations) using your site's own WordPress mailer (`wp_mail`). Subscribers who are emailed successfully are marked as notified so they are not contacted twice.

= Does this comply with GDPR / consent requirements? =
Every signup requires the shopper to tick an explicit consent checkbox before they can join the waitlist; the form will not submit without it. Subscriber emails are stored only in a custom table in your own WordPress database and are never sent to any external service. You are responsible for the wording of your consent label and your site's privacy policy.

= Where are subscribers stored? =
In a custom `{prefix}_restock_waitlist` table in your WordPress database. Nothing is sent to any third party.

= Can I export the subscriber list? =
Yes. From **WooCommerce → Restock → Subscribers** you can view subscribers, filter by product, and export the list as CSV.

= Does the form reload the page on submit? =
No. Submission is handled by a small vanilla-JavaScript `fetch` call (no jQuery), and the result is announced in an `aria-live` region — no page reload and no layout shift.

= How do I remove all plugin data? =
Deleting the plugin from the **Plugins** screen runs the uninstall routine, which drops the `{prefix}_restock_waitlist` table and removes the `restock_settings` and `restock_schema_version` options.

== Screenshots ==

1. Waitlist form on an out-of-stock product page — email field, consent checkbox, and "Join Waitlist" button.
2. Admin subscriber list — filterable by product, with CSV export.
3. Settings page — toggle guest access and customise form labels and notification email text.

== External Services ==

Restock does not connect to any external services. Back-in-stock notification emails are sent through your own site's WordPress mailer (`wp_mail`); subscriber data stays in your WordPress database.

== Changelog ==

= 0.1.0 =
* Initial release.
