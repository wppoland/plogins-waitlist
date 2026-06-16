=== Restock - Back in Stock Notifications for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Back-in-stock waitlist for WooCommerce. Shoppers leave their email, you email them on restock. Accessible, no layout shift.

== Description ==

Restock adds a waitlist form to out-of-stock WooCommerce products. A shopper enters their email, and when you set the product back to "In stock", Restock emails everyone waiting through your site's own WordPress mailer. There is no external service, no account to sign up for, and nothing leaves your database.

The form is rendered in PHP on the single-product summary, where it sits in the normal page flow rather than being injected after load, so it does not shift surrounding content. Submitting it runs a small vanilla-JavaScript `fetch` request loaded with `defer` in the footer; the plugin adds no jQuery of its own. On variable products it hooks into WooCommerce's existing variation script so the form only appears once an unavailable variation is selected.

Accessibility was a first-class concern rather than an afterthought. The email field carries a visually hidden label, consent is a real required checkbox, and the success/error message is announced through an `aria-live` region while the form reports `aria-busy` during submission.

Subscriber data lives in a single `{prefix}_restock_waitlist` table that the plugin creates and version-tracks. Notifications fire on the `woocommerce_product_set_stock_status` hook, so there is no queue or background cron to run. Uninstalling drops the table and removes the plugin's options, leaving nothing behind.

Source and issues: https://github.com/wppoland/restock — patches and bug reports welcome there.

**Features**

* Waitlist form shown automatically on out-of-stock and backorder ("on backorder") product pages
* Variable products: form appears after the shopper selects an unavailable variation
* WooCommerce **My Account → Waitlists** tab for logged-in customers (review lists, leave waitlist)
* Asynchronous submit with a vanilla-JavaScript fetch call, so the page does not reload
* Email field pre-filled for logged-in customers
* Required consent checkbox for every signup
* Automatic plain-text email notification on restock, sent via `wp_mail`
* Optional heading and intro text shown above the form
* Customisable form labels, button text, on-screen submit messages, and notification email subject/intro/closing text
* `[restock_waitlist]` shortcode for placing the form manually in a product template
* Toggle guest (not-logged-in) subscriptions on or off
* Admin subscriber list with per-product filter and CSV export
* Theme-overridable form template (`yourtheme/restock/single-product/waitlist-form.php`)
* Compatible with WooCommerce HPOS (Custom Order Tables) and Cart/Checkout Blocks

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Restock from the WordPress plugin directory, or upload the `restock` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** screen.
4. Optionally visit **WooCommerce → Restock** to customise labels and notification text; sensible defaults work out of the box.
5. The waitlist form appears automatically on any out-of-stock or backorder product page.

== Frequently Asked Questions ==

= Does Restock require WooCommerce? =
Yes. Restock is a WooCommerce extension and requires WooCommerce 8.0 or later. It will show an admin notice and stay inactive if WooCommerce is missing or out of date.

= How are notifications sent? =
When WooCommerce sets a product's stock status to `instock`, Restock sends a plain-text email to every pending subscriber for that product (and its parent, for variations) using your site's own WordPress mailer (`wp_mail`). Subscribers who are emailed successfully are marked as notified so they are not contacted twice.

= Does it work with variable products? =
Yes. Choose options in the standard WooCommerce variation form first. When the selected variation is out of stock or on backorder, the waitlist form appears and the subscription is stored for that specific variation.

= Can guests join the waitlist? =
Yes by default. You can restrict signups to logged-in customers by unchecking **Allow guest subscriptions** in **WooCommerce → Restock**.

= Can customers manage waitlists in My Account? =
Yes. Logged-in customers see a **Waitlists** tab under My Account with active subscriptions, current stock status, and a button to leave each list.

= Does this comply with GDPR / consent requirements? =
Every signup requires the shopper to tick an explicit consent checkbox before they can join the waitlist; the form will not submit without it. Subscriber emails are stored only in a custom table in your own WordPress database and are never sent to any external service. You are responsible for the wording of your consent label and your site's privacy policy.

= Can I export the subscriber list? =
Yes. From **WooCommerce → Restock → Subscribers** you can view subscribers, filter by product, and export the list as CSV.

= Does the form reload the page on submit? =
No. The form is submitted with a vanilla-JavaScript `fetch` call and the result is announced in an `aria-live` region, so the page stays put. Restock loads no jQuery for this; on variable products it does rely on WooCommerce's own variation script to know which variation is selected.

== Screenshots ==

1. The waitlist form on an out-of-stock product — a shopper leaves their email and ticks the required consent box to be notified when it returns.
2. The Restock settings screen — sectioned cards with inline help for guest access, the heading and intro, form labels, on-screen messages, and notification email text.
3. A customer's My Account → Waitlists tab — products they are waiting for, the current stock status, and a button to leave each list.

== External Services ==

Restock does not connect to any external services. Back-in-stock notification emails are sent through your own site's WordPress mailer (`wp_mail`); subscriber data stays in your WordPress database.

== Changelog ==

= 0.3.0 =
* New: WooCommerce My Account **Waitlists** tab with stock status and leave-waitlist action.
* New: variation-aware waitlist signups on variable products (form shows after an out-of-stock variation is selected).
* New: settings for My Account menu label, variation prompt, and unsubscribe confirmation message.

= 0.2.0 =
* New: `[restock_waitlist]` shortcode to place the waitlist form manually (optional `id` attribute to target a specific product).
* New: optional form heading and intro text, configurable from the settings page.
* New: editable on-screen form messages (success, invalid email, missing consent, login required).
* Improved: the settings page now exposes every form label, message, and email text the engine supports, instead of relying on hardcoded defaults.
* Improved: empty optional settings now correctly fall back to the built-in defaults.

= 0.1.0 =
* Initial release.
