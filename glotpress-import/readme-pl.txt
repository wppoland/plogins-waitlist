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

Browse the full catalogue at https://plogins.com/pl/ .

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Plogins Waitlist from the WordPress plugin directory, or upload the `plogins-waitlist` folder to `/wp-content/plugins/`.
3. Activate the plugin through the <strong>Plugins<strong> screen. 4. Optionally visit </strong>WooCommerce → Plogins Waitlist</strong> to customise labels and notification text; sensible defaults work out of the box.
5. The waitlist form appears automatically on any out-of-stock or backorder product page.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentation</strong> - https://plogins.com/pl/plogins-waitlist/docs/
* <strong>Plugin page</strong> - https://plogins.com/pl/plogins-waitlist/
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
Nie. Formularz jest przesyłany za pomocą wywołania „fetch” w języku JavaScript, a wynik jest ogłaszany w regionie „aria-live”, więc strona pozostaje na miejscu. Lista oczekujących Plogins nie ładuje w tym celu żadnego jQuery; w przypadku produktów zmiennych opiera się na własnym skrypcie odmian WooCommerce, aby wiedzieć, która odmiana jest wybrana.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Formularz listy oczekujących w przypadku produktu, którego nie ma w magazynie, w którym kupujący zostawia swój adres e-mail i zaznacza wymagane pole zgody, aby otrzymać powiadomienie o jego zwrocie.
2. Ekran ustawień listy oczekujących Plogins z podzielonymi kartami i wbudowaną pomocą dotyczącą dostępu gości, nagłówka i wprowadzenia, etykiet formularzy, komunikatów ekranowych i tekstu powiadomienia e-mail.
3. Zakładka „Listy oczekujących” na moim koncie klienta, wyświetlająca produkty, na które czeka, aktualny stan zapasów oraz przycisk umożliwiający opuszczenie każdej listy.

== External Services ==

Plogins Waitlist nie łączy się z żadnymi usługami zewnętrznymi. E-maile z powiadomieniem o ponownym pojawieniu się w magazynie są wysyłane za pośrednictwem poczty WordPress Twojej witryny (`wp_mail`); dane subskrybenta pozostają w Twojej bazie danych WordPress.

== Changelog ==

= 1.0.3 =
* Wewnętrzne porządki: ujednolicono tożsamość kodu wewnętrznego na liście oczekujących (było Restock). Brak zmian w Twoich ustawieniach i danych; Dzięki starszym aliasom starsze wersje PRO będą działać podczas aktualizacji.

= 1.0.2 =
* Dodano przegląd ekranów ustawień nadchodzących funkcji PRO.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.4.1 =
* Dokumenty: dodano sekcję „Możesz też polubić” łączącą inne bezpłatne wtyczki WPPoland WooCommerce. Żadnych zmian funkcjonalnych.

= 0.4.0 =
* Nowość: opcjonalny wiersz społeczny nad formularzem pokazujący, ilu kupujących już czeka na produkt („12 kupujących już czeka na ten artykuł”). Tekst obsługujący liczbę pojedynczą/mnogą, w pełni konfigurowalny z symbolem zastępczym {count} i ukryty, gdy nikt nie czeka.
* Nowość: usunięcie abonenta bezpośrednio z listy subskrybentów administratora (niezabezpieczona, z potwierdzeniem).

= 0.3.3 =
* Nowość: Widżet Elementora do formularza listy oczekujących ponownie dostępnych, dzięki czemu można go umieścić w edytorze Elementora. Samoobrona: ładuje się tylko wtedy, gdy Elementor jest aktywny.

= 0.3.2 =
* Napraw domenę tekstową w trzech ciągach pomocy administratora, aby pasowała do wtyczki.

= 0.3.1 =
* Zmieniono nazwę na Lista oczekujących Plogins dla WooCommerce dla odrębnej, nieogólnej nazwy katalogu. Żadnych zmian funkcjonalnych.

= 0.3.0 =
* Nowość: zakładka Moje konto WooCommerce <strong>Listy oczekujących</strong> ze stanem zapasów i listą oczekujących na opuszczenie.
* Nowość: rejestracja na liście oczekujących uwzględniająca różnice w produktach zmiennych (formularz pojawia się po wybraniu odmiany, której nie ma w magazynie).
* Nowość: ustawienia etykiety menu Moje konto, monit o zmianę i wiadomość z potwierdzeniem rezygnacji z subskrypcji.

= 0.2.0 =
* Nowość: krótki kod `[restock_waitlist]` do ręcznego umieszczenia formularza listy oczekujących (opcjonalny atrybut `id` w celu kierowania na konkretny produkt).
* Nowość: opcjonalny nagłówek formularza i tekst wprowadzenia, konfigurowalny na stronie ustawień.
* Nowość: edytowalne komunikaty formularzy ekranowych (powodzenie, nieprawidłowy adres e-mail, brak zgody, wymagane logowanie).
* Ulepszono: strona ustawień wyświetla teraz każdą etykietę formularza, wiadomość i tekst e-maila obsługiwany przez silnik, zamiast polegać na zakodowanych na stałe ustawieniach domyślnych.
* Ulepszono: puste ustawienia opcjonalne teraz prawidłowo wracają do wbudowanych ustawień domyślnych.

= 0.1.0 =
* Pierwsze wydanie.
