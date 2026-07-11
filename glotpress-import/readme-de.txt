=== Plogins Waitlist - Back in Stock for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Warteliste für wieder verfügbare Artikel für WooCommerce. Deine Kundschaft hinterlässt ihre E-Mail-Adresse, du benachrichtigst sie beim Wiederauffüllen. Barrierefrei, ohne Layout-Verschiebung.

== Description ==

Plogins Waitlist fügt nicht vorrätigen WooCommerce-Produkten ein Wartelisten-Formular hinzu. Ein Käufer trägt seine E-Mail-Adresse ein, und sobald du das Produkt wieder auf „Auf Lager“ setzt, benachrichtigt Plogins Waitlist alle Wartenden über den eigenen WordPress-Mailer deiner Website. Es gibt keinen externen Dienst, kein Konto zum Anmelden, und nichts verlässt deine Datenbank.

Das Formular wird in PHP in der Einzelprodukt-Zusammenfassung gerendert, wo es im normalen Seitenfluss sitzt, statt nach dem Laden eingefügt zu werden, sodass es umgebende Inhalte nicht verschiebt. Beim Absenden läuft eine kleine `fetch`-Anfrage in reinem JavaScript, im Footer mit `defer` geladen; das Plugin bringt kein eigenes jQuery mit. Bei variablen Produkten klinkt es sich in das vorhandene Variations-Skript von WooCommerce ein, sodass das Formular erst erscheint, wenn eine nicht verfügbare Variante ausgewählt ist.

Barrierefreiheit war von Anfang an ein zentrales Anliegen und kein nachträglicher Gedanke. Das E-Mail-Feld trägt eine visuell verborgene Beschriftung, die Einwilligung ist eine echte Pflicht-Checkbox, und die Erfolgs-/Fehlermeldung wird über eine `aria-live`-Region angesagt, während das Formular während des Absendens `aria-busy` meldet.

Die Abonnentendaten liegen in einer einzigen Tabelle `{prefix}_restock_waitlist`, die das Plugin anlegt und deren Version es verfolgt. Benachrichtigungen werden am Hook `woocommerce_product_set_stock_status` ausgelöst, sodass keine Warteschlange und kein Hintergrund-Cron laufen muss. Beim Deinstallieren wird die Tabelle gelöscht und die Optionen des Plugins entfernt, sodass nichts zurückbleibt.

Quellcode und Issues: https://github.com/wppoland/plogins-waitlist . Patches und Fehlerberichte sind dort willkommen.

<strong>Funktionen</strong>

* Wartelisten-Formular, das automatisch auf Produktseiten erscheint, die nicht vorrätig oder auf Bestellung („lieferbar auf Bestellung“) sind
* Variable Produkte: das Formular erscheint, nachdem der Käufer eine nicht verfügbare Variante gewählt hat
* WooCommerce-Tab <strong>Mein Konto → Wartelisten</strong> für eingeloggte Kundschaft (Listen ansehen, Warteliste verlassen)
* Asynchrones Absenden per fetch-Aufruf in reinem JavaScript, sodass die Seite nicht neu lädt
* E-Mail-Feld für eingeloggte Kundschaft vorausgefüllt
* Pflicht-Einwilligungs-Checkbox bei jeder Anmeldung
* Automatische Klartext-E-Mail-Benachrichtigung beim Wiederauffüllen, gesendet über `wp_mail`
* Optionale Überschrift und Einleitungstext über dem Formular
* Anpassbare Formularbeschriftungen, Button-Text, Bildschirmmeldungen beim Absenden sowie Betreff/Einleitung/Schlusstext der Benachrichtigungs-E-Mail
* Platziere das Formular mit dem Shortcode `[restock_waitlist]` oder dem Elementor-Widget „Wartelisten-Formular“
* Anmeldungen von Gästen (nicht eingeloggt) ein- oder ausschalten
* Social-Proof-Zeile „N Käufer warten bereits“ über dem Formular (optional, anpassbar, Singular/Plural-fähig)
* Abonnentenliste im Adminbereich mit Filter pro Produkt, CSV-Export und Entfernen per Klick
* Im Theme überschreibbares Formular-Template (`yourtheme/restock/single-product/waitlist-form.php`)
* Kompatibel mit WooCommerce HPOS (Custom Order Tables) und Cart/Checkout-Blocks

= You may also like these plugins =

Weitere kostenlose WooCommerce-Plugins von WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - Mengen- und Volumenpreisstufen mit einer serverseitig gerenderten Preistabelle.
* [Sieve - Search & Filter](https://wordpress.org/plugins/sieve/) - schnelle AJAX-Produktsuche und -Filterung für WooCommerce, ohne jQuery.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Konformität für den polnischen Markt: GPSR, Omnibus, DSGVO, Rechnungen und Shop-Module.

Durchstöbere den vollständigen Katalog unter https://plogins.com/de/ .

== Installation ==

1. Installiere und aktiviere WooCommerce (8.0 oder neuer).
2. Installiere Plogins Waitlist aus dem WordPress-Plugin-Verzeichnis oder lade den Ordner `plogins-waitlist` nach `/wp-content/plugins/` hoch.
3. Aktiviere das Plugin über den Bildschirm <strong>Plugins</strong>.
4. Besuche optional <strong>WooCommerce → Plogins Waitlist</strong>, um Beschriftungen und Benachrichtigungstexte anzupassen; sinnvolle Standardwerte funktionieren sofort.
5. Das Wartelisten-Formular erscheint automatisch auf jeder Produktseite, die nicht vorrätig oder auf Bestellung ist.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-waitlist/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-waitlist/
* <strong>Quellcode</strong> - https://github.com/wppoland/plogins-waitlist
* <strong>Fehlerberichte und Funktionswünsche</strong> - https://github.com/wppoland/plogins-waitlist/issues


= Does Plogins Waitlist require WooCommerce? =
Ja. Plogins Waitlist ist eine WooCommerce-Erweiterung und erfordert WooCommerce 8.0 oder neuer. Fehlt WooCommerce oder ist es veraltet, zeigt das Plugin einen Admin-Hinweis und bleibt inaktiv.

= How are notifications sent? =
Wenn WooCommerce den Lagerstatus eines Produkts auf `instock` setzt, sendet Plogins Waitlist eine Klartext-E-Mail an jeden wartenden Abonnenten dieses Produkts (und bei Varianten an das übergeordnete Produkt) über den eigenen WordPress-Mailer deiner Website (`wp_mail`). Abonnenten, die erfolgreich benachrichtigt wurden, werden als benachrichtigt markiert, damit sie nicht zweimal kontaktiert werden.

= Does it work with variable products? =
Ja. Wähle zuerst die Optionen im normalen WooCommerce-Variationsformular. Ist die gewählte Variante nicht vorrätig oder auf Bestellung, erscheint das Wartelisten-Formular und die Anmeldung wird für genau diese Variante gespeichert.

= Can guests join the waitlist? =
Ja, standardmäßig. Du kannst Anmeldungen auf eingeloggte Kundschaft beschränken, indem du <strong>Gast-Anmeldungen erlauben</strong> unter <strong>WooCommerce → Plogins Waitlist</strong> deaktivierst.

= Can customers manage waitlists in My Account? =
Ja. Eingeloggte Kundschaft sieht unter „Mein Konto“ einen Tab <strong>Wartelisten</strong> mit aktiven Anmeldungen, aktuellem Lagerstatus und einem Button, um jede Liste zu verlassen.

= Does this comply with GDPR / consent requirements? =
Jede Anmeldung verlangt, dass der Käufer eine ausdrückliche Einwilligungs-Checkbox anhakt, bevor er der Warteliste beitreten kann; ohne sie wird das Formular nicht abgeschickt. Die E-Mail-Adressen der Abonnenten werden nur in einer eigenen Tabelle in deiner eigenen WordPress-Datenbank gespeichert und niemals an einen externen Dienst gesendet. Für den Wortlaut deiner Einwilligungs-Beschriftung und die Datenschutzerklärung deiner Website bist du verantwortlich.

= Can I export the subscriber list? =
Ja. Unter <strong>WooCommerce → Plogins Waitlist → Abonnenten</strong> kannst du Abonnenten ansehen, nach Produkt filtern und die Liste als CSV exportieren.

= Does the form reload the page on submit? =
Nein. Das Formular wird mit einem `fetch`-Aufruf in reinem JavaScript abgeschickt und das Ergebnis in einer `aria-live`-Region angesagt, sodass die Seite bleibt, wo sie ist. Plogins Waitlist lädt dafür kein jQuery; bei variablen Produkten verlässt es sich auf das eigene Variations-Skript von WooCommerce, um zu wissen, welche Variante gewählt ist.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es netzwerkweit oder auf einzelnen Websites; jede Website behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Das Wartelisten-Formular bei einem nicht vorrätigen Produkt, wo ein Käufer seine E-Mail-Adresse hinterlässt und die erforderliche Einwilligungs-Checkbox anhakt, um benachrichtigt zu werden, sobald das Produkt zurück ist.
2. Der Einstellungsbildschirm von Plogins Waitlist mit in Abschnitte gegliederten Karten und Inline-Hilfe für Gastzugang, Überschrift und Einleitung, Formularbeschriftungen, Bildschirmmeldungen und Text der Benachrichtigungs-E-Mail.
3. Der „Wartelisten“-Tab unter „Mein Konto“ einer Kundin oder eines Kunden, der die erwarteten Produkte, den aktuellen Lagerstatus und einen Button zum Verlassen jeder Liste zeigt.

== External Services ==

Plogins Waitlist stellt keine Verbindung zu externen Diensten her. E-Mails zur Benachrichtigung über wieder verfügbare Artikel werden über den eigenen WordPress-Mailer deiner Website (`wp_mail`) gesendet; die Abonnentendaten bleiben in deiner WordPress-Datenbank.

== Translations ==

Plogins Waitlist enthält deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche. Die Textdomain ist `plogins-waitlist`, sodass Sprachpakete von WordPress.org diese mitgelieferten Übersetzungen ebenfalls überschreiben oder erweitern können.

== Changelog ==

= 1.0.4 =
* Deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche mitgeliefert.

= 1.0.3 =
* Interne Aufräumarbeiten: die interne Code-Identität auf Waitlist vereinheitlicht (war Restock). Keine Änderung an deinen Einstellungen oder Daten; Legacy-Aliase halten ältere PRO-Builds während des Updates funktionsfähig.

= 1.0.2 =
* Auf dem Einstellungsbildschirm eine Übersicht über kommende PRO-Funktionen hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.4.1 =
* Doku: Ein Abschnitt „Das könnte dir auch gefallen“ hinzugefügt, der die anderen kostenlosen WooCommerce-Plugins von WPPoland verlinkt. Keine funktionalen Änderungen.

= 0.4.0 =
* Neu: optionale Social-Proof-Zeile über dem Formular, die zeigt, wie viele Käufer bereits auf ein Produkt warten („12 Käufer warten bereits auf diesen Artikel.“). Singular/Plural-fähig, vollständig anpassbarer Text mit einem {count}-Platzhalter und ausgeblendet, wenn niemand wartet.
* Neu: einen Abonnenten direkt aus der Abonnentenliste im Adminbereich entfernen (nonce-geschützt, mit Bestätigung).

= 0.3.3 =
* Neu: Elementor-Widget für das Wartelisten-Formular für wieder verfügbare Artikel, sodass es sich mit dem Elementor-Editor platzieren lässt. Selbstgeschützt: wird nur geladen, wenn Elementor aktiv ist.

= 0.3.2 =
* Die Textdomain in drei Admin-Hilfetexten korrigiert, damit sie zum Plugin-Slug passt.

= 0.3.1 =
* In Plogins Waitlist für WooCommerce umbenannt, für einen eindeutigen, nicht generischen Verzeichnisnamen. Keine funktionalen Änderungen.

= 0.3.0 =
* Neu: WooCommerce-Tab <strong>Wartelisten</strong> unter „Mein Konto“ mit Lagerstatus und Aktion zum Verlassen der Warteliste.
* Neu: variantenbewusste Wartelisten-Anmeldungen bei variablen Produkten (das Formular erscheint, nachdem eine nicht vorrätige Variante gewählt wurde).
* Neu: Einstellungen für die Beschriftung des „Mein Konto“-Menüs, den Variantenhinweis und die Bestätigungsmeldung beim Abmelden.

= 0.2.0 =
* Neu: Shortcode `[restock_waitlist]`, um das Wartelisten-Formular manuell zu platzieren (optionales Attribut `id`, um ein bestimmtes Produkt anzusprechen).
* Neu: optionale Formularüberschrift und Einleitungstext, über die Einstellungsseite konfigurierbar.
* Neu: bearbeitbare Bildschirm-Formularmeldungen (Erfolg, ungültige E-Mail, fehlende Einwilligung, Anmeldung erforderlich).
* Verbessert: Die Einstellungsseite zeigt jetzt jede Formularbeschriftung, Meldung und jeden E-Mail-Text, den die Engine unterstützt, statt sich auf fest kodierte Standardwerte zu verlassen.
* Verbessert: Leere optionale Einstellungen greifen jetzt korrekt auf die eingebauten Standardwerte zurück.

= 0.1.0 =
* Erste Veröffentlichung.
