=== Plogins Waitlist - Back in Stock for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Back-in-stock waitlist for WooCommerce. Shoppers leave their email, you email them on restock. Accessible, no layout shift.

== Description ==

Plogins Waitlist adds a waitlist form to out-of-stock WooCommerce products. A shopper enters their email, and when you set the product back to "In stock", Plogins Waitlist emails everyone waiting through your site's own WordPress mailer. There is no external service, no account to sign up for, and nothing leaves your database.

The form is rendered in PHP on the single-product summary, where it sits in the normal page flow rather than being injected after load, so it does not shift surrounding content. Submitting it runs a small vanilla-JavaScript `fetch` request loaded with `defer` in the footer; the plugin adds no jQuery of its own. On variable products it hooks into WooCommerce's existing variation script so the form only appears once an unavailable variation is selected.

Accessibility was a first-class concern rather than an afterthought. The email field carries a visually hidden label, consent is a real required checkbox, and the success/error message is announced through an `aria-live` region while the form reports `aria-busy` during submission.

Subscriber data lives in a single `{prefix}_restock_waitlist` table that the plugin creates and version-tracks. Notifications fire on the `woocommerce_product_set_stock_status` hook, so there is no queue or background cron to run. Uninstalling drops the table and removes the plugin's options, leaving nothing behind.

Source and issues: https://github.com/wppoland/plogins-waitlist . Patches and bug reports are welcome there.

<strong>Features</strong>

* Waitlist form shown automatically on out-of-stock and backorder ("on backorder") product pages
* Variable products: form appears after the shopper selects an unavailable variation
* WooCommerce <strong>My Account → Waitlists</strong> tab for logged-in customers (review lists, leave waitlist)
* Asynchronous submit with a vanilla-JavaScript fetch call, so the page does not reload
* Email field pre-filled for logged-in customers
* Required consent checkbox for every signup
* Automatic plain-text email notification on restock, sent via `wp_mail`
* Optional heading and intro text shown above the form
* Customisable form labels, button text, on-screen submit messages, and notification email subject/intro/closing text
* `[restock_waitlist]` shortcode for placing the form manually in a product template
* Toggle guest (not-logged-in) subscriptions on or off
* Social-proof "N shoppers are already waiting" line above the form (optional, customisable, singular/plural aware)
* Admin subscriber list with per-product filter, CSV export, and one-click remove
* Theme-overridable form template (`yourtheme/restock/single-product/waitlist-form.php`)
* Compatible with WooCommerce HPOS (Custom Order Tables) and Cart/Checkout Blocks

= You may also like these plugins =

More free WooCommerce plugins from WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - quantity and volume pricing tiers with a server-rendered price table.
* [Sieve - Search & Filter](https://wordpress.org/plugins/sieve/) - fast AJAX product search and filtering for WooCommerce, with no jQuery.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Polish-market compliance: GPSR, Omnibus, GDPR, invoices and storefront modules.

Browse the full catalogue at https://plogins.com/de/ .

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Plogins Waitlist from the WordPress plugin directory, or upload the `plogins-waitlist` folder to `/wp-content/plugins/`.
3. Activate the plugin through the <strong>Plugins<strong> screen. 4. Optionally visit </strong>WooCommerce → Plogins Waitlist</strong> to customise labels and notification text; sensible defaults work out of the box.
5. The waitlist form appears automatically on any out-of-stock or backorder product page.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentation</strong> - https://plogins.com/de/plogins-waitlist/docs/
* <strong>Plugin page</strong> - https://plogins.com/de/plogins-waitlist/
* <strong>Source code</strong> - https://github.com/wppoland/plogins-waitlist
* <strong>Bug reports and feature requests</strong> - https://github.com/wppoland/plogins-waitlist/issues


= Does Plogins Waitlist require WooCommerce? =
Yes. Plogins Waitlist is a WooCommerce extension and requires WooCommerce 8.0 or later. It will show an admin notice and stay inactive if WooCommerce is missing or out of date.

= How are notifications sent? =
When WooCommerce sets a product's stock status to `instock`, Plogins Waitlist sends a plain-text email to every pending subscriber for that product (and its parent, for variations) using your site's own WordPress mailer (`wp_mail`). Subscribers who are emailed successfully are marked as notified so they are not contacted twice.

= Does it work with variable products? =
Yes. Choose options in the standard WooCommerce variation form first. When the selected variation is out of stock or on backorder, the waitlist form appears and the subscription is stored for that specific variation.

= Can guests join the waitlist? =
Yes by default. You can restrict signups to logged-in customers by unchecking <strong>Allow guest subscriptions</strong> in <strong>WooCommerce → Plogins Waitlist</strong>.

= Can customers manage waitlists in My Account? =
Yes. Logged-in customers see a <strong>Waitlists</strong> tab under My Account with active subscriptions, current stock status, and a button to leave each list.

= Does this comply with GDPR / consent requirements? =
Every signup requires the shopper to tick an explicit consent checkbox before they can join the waitlist; the form will not submit without it. Subscriber emails are stored only in a custom table in your own WordPress database and are never sent to any external service. You are responsible for the wording of your consent label and your site's privacy policy.

= Can I export the subscriber list? =
Yes. From <strong>WooCommerce → Plogins Waitlist → Subscribers</strong> you can view subscribers, filter by product, and export the list as CSV.

= Does the form reload the page on submit? =
Nein. Das Formular wird mit einem Vanilla-JavaScript-„fetch“-Aufruf gesendet und das Ergebnis wird in einer „Aria-Live“-Region angekündigt, sodass die Seite an Ort und Stelle bleibt. Plogins Waitlist lädt dafür kein jQuery; Bei variablen Produkten verlässt es sich auf WooCommerces eigenes Variationsskript, um zu wissen, welche Variation ausgewählt ist.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Das Wartelistenformular für ein nicht vorrätiges Produkt, bei dem ein Käufer seine E-Mail-Adresse hinterlässt und das erforderliche Einverständnisfeld ankreuzt, um benachrichtigt zu werden, wenn das Produkt zurückkommt.
2. Der Plogins-Wartelisten-Einstellungsbildschirm mit unterteilten Karten und Inline-Hilfe für den Gastzugang, der Überschrift und Einleitung, Formularbeschriftungen, Bildschirmnachrichten und Benachrichtigungs-E-Mail-Text.
3. Die Registerkarte „Wartelisten für mein Konto“ eines Kunden mit den Produkten, auf die er wartet, dem aktuellen Lagerbestand und einer Schaltfläche zum Verlassen jeder Liste.

== External Services ==

Plogins Waitlist stellt keine Verbindung zu externen Diensten her. E-Mails mit Benachrichtigungen über wieder verfügbare Lagerbestände werden über den WordPress-Mailer deiner eigenen Website („wp_mail“) gesendet. Abonnentendaten bleiben in deiner WordPress-Datenbank.

== Changelog ==

= 1.0.3 =
* Interne Bereinigung: Die interne Codeidentität wurde zur Warteliste vereinheitlicht (war „Aufstocken“). Keine Änderung deiner Einstellungen oder Daten; Legacy-Aliase sorgen dafür, dass ältere PRO-Builds während des Updates funktionieren.

= 1.0.2 =
* Auf dem Einstellungsbildschirm wurde eine Übersicht über kommende PRO-Funktionen hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.4.1 =
* Dokumente: Es wurde ein Abschnitt „Das könnte dir auch gefallen“ hinzugefügt, der die anderen kostenlosen WPPoland WooCommerce-Plugins verlinkt. Keine funktionalen Änderungen.

= 0.4.0 =
* Neu: optionale Social-Proof-Zeile über dem Formular, die anzeigt, wie viele Käufer bereits auf ein Produkt warten („12 Käufer warten bereits auf diesen Artikel.“). Singular/Plural-fähiger, vollständig anpassbarer Text mit einem {count}-Platzhalter und ausgeblendet, wenn niemand wartet.
* Neu: Entferne einen Abonnenten direkt aus der Admin-Abonnentenliste (nicht geschützt, mit Bestätigung).

= 0.3.3 =
* Neu: Elementor-Widget für das Formular „Back-in-Stock-Warteliste“, sodass es mit dem Elementor-Editor platziert werden kann. Selbstgeschützt: Wird nur geladen, wenn Elementor aktiv ist.

= 0.3.2 =
* Korrigiere die Textdomäne in drei Admin-Hilfezeichenfolgen, damit sie mit dem Plugin-Slug übereinstimmt.

= 0.3.1 =
* Für einen eindeutigen, nicht generischen Verzeichnisnamen in „Plogins Waitlist for WooCommerce“ umbenannt. Keine funktionalen Änderungen.

= 0.3.0 =
* Neu: WooCommerce-Registerkarte „Mein Konto“ <strong>Wartelisten</strong> mit Lagerbestandsstatus und Aktion zum Verlassen der Warteliste.
* Neu: Variantenbezogene Wartelistenanmeldungen für variable Produkte (Formular wird angezeigt, nachdem eine nicht vorrätige Variante ausgewählt wurde).
* Neu: Einstellungen für die Menübezeichnung „Mein Konto“, die Änderungsaufforderung und die Bestätigungsnachricht zum Abbestellen.

= 0.2.0 =
* Neu: Shortcode „[restock_waitlist]“, um das Wartelistenformular manuell zu platzieren (optionales „id“-Attribut, um auf ein bestimmtes Produkt abzuzielen).
* Neu: optionale Formularüberschrift und Einleitungstext, konfigurierbar über die Einstellungsseite.
* Neu: Bearbeitbare Formularmeldungen auf dem Bildschirm (Erfolg, ungültige E-Mail, fehlende Einwilligung, Anmeldung erforderlich).
* Verbessert: Auf der Einstellungsseite werden jetzt alle von der Engine unterstützten Formularbezeichnungen, Nachrichten und E-Mail-Texte angezeigt, anstatt sich auf fest codierte Standardeinstellungen zu verlassen.
* Verbessert: Leere optionale Einstellungen greifen jetzt korrekt auf die integrierten Standardeinstellungen zurück.

= 0.1.0 =
* Erstveröffentlichung.
