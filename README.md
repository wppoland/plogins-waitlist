# Restock - Back in Stock Notifications for WooCommerce

Restock adds a fast, accessible waitlist form to out-of-stock WooCommerce product pages. Shoppers leave their email; when the product is restocked, every pending subscriber is emailed automatically using your site's own WordPress mailer — no third-party service involved.

## Features

- Waitlist form shown automatically on out-of-stock and backorder product pages.
- Variable-product aware: the form appears after a shopper selects an unavailable variation.
- A WooCommerce **My Account → Waitlists** tab where logged-in customers can review and leave their lists.
- Asynchronous subscribe with a tiny vanilla-JS `fetch` call — no jQuery, no page reload, no layout shift.
- Automatic plain-text email notifications on restock, sent via `wp_mail`, with a required consent checkbox on every signup.
- Admin subscriber list with per-product filter and CSV export, plus a `[restock_waitlist]` shortcode and theme-overridable template.
- Compatible with WooCommerce HPOS and Cart/Checkout Blocks.

## Installation

1. Install and activate WooCommerce (8.0 or later).
2. Upload the `restock` folder to `/wp-content/plugins/`, or install from the WordPress plugin directory.
3. Activate the plugin through the **Plugins** screen.
4. Optionally visit **WooCommerce → Restock** to customise labels and notification text; sensible defaults work out of the box.

## Frequently Asked Questions

**Can guests join the waitlist?**
Yes by default. You can restrict signups to logged-in customers in **WooCommerce → Restock**.

**How are notifications sent?**
When WooCommerce sets a product's stock status to `instock`, Restock emails every pending subscriber via your site's own WordPress mailer. Nothing is sent to any external service.

---

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
