=== Plogins Waitlist - Back in Stock for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, back in stock, waitlist, stock notification, email
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lista oczekujących na ponowną dostępność dla WooCommerce. Klienci zostawiają swój e-mail, a Ty wysyłasz im wiadomość, gdy produkt wróci do sprzedaży. Dostępna cyfrowo, bez przeskoków układu.

== Description ==

Plogins Waitlist dodaje formularz listy oczekujących do niedostępnych produktów WooCommerce. Klient podaje swój adres e-mail, a gdy ponownie ustawisz produkt na „Na stanie”, Plogins Waitlist wysyła wiadomość do wszystkich oczekujących za pomocą własnego mechanizmu poczty WordPress w Twojej witrynie. Nie ma żadnej usługi zewnętrznej, żadnego konta do zakładania i nic nie opuszcza Twojej bazy danych.

Formularz jest renderowany w PHP w podsumowaniu pojedynczego produktu, gdzie znajduje się w naturalnym przepływie strony, zamiast być wstrzykiwany po jej załadowaniu, więc nie przesuwa otaczającej treści. Jego wysłanie uruchamia niewielkie żądanie `fetch` w czystym JavaScripcie, ładowane z atrybutem `defer` w stopce; wtyczka nie dodaje własnego jQuery. W przypadku produktów z wariantami podłącza się do istniejącego skryptu wariantów WooCommerce, więc formularz pojawia się dopiero po wybraniu niedostępnego wariantu.

Dostępność cyfrowa była priorytetem od samego początku, a nie dodatkiem na później. Pole e-mail ma etykietę ukrytą wizualnie, zgoda to prawdziwe, wymagane pole wyboru, a komunikat o powodzeniu/błędzie jest ogłaszany w regionie `aria-live`, podczas gdy formularz zgłasza `aria-busy` w trakcie wysyłania.

Dane subskrybentów przechowywane są w jednej tabeli `{prefix}_restock_waitlist`, którą wtyczka tworzy i której wersję śledzi. Powiadomienia są wyzwalane w zdarzeniu `woocommerce_product_set_stock_status`, więc nie ma żadnej kolejki ani zadania cron działającego w tle. Odinstalowanie usuwa tabelę oraz opcje wtyczki, nie pozostawiając niczego.

Kod źródłowy i zgłoszenia: https://github.com/wppoland/plogins-waitlist . Poprawki i zgłoszenia błędów są tam mile widziane.

<strong>Funkcje</strong>

* Formularz listy oczekujących wyświetlany automatycznie na stronach produktów niedostępnych oraz dostępnych na zamówienie („na zamówienie”)
* Produkty z wariantami: formularz pojawia się po wybraniu przez klienta niedostępnego wariantu
* Zakładka WooCommerce <strong>Moje konto → Listy oczekujących</strong> dla zalogowanych klientów (przegląd list, opuszczenie listy)
* Asynchroniczne wysyłanie wywołaniem fetch w czystym JavaScripcie, dzięki czemu strona się nie przeładowuje
* Pole e-mail wstępnie wypełnione dla zalogowanych klientów
* Wymagane pole zgody przy każdym zapisie
* Automatyczne powiadomienie e-mail w formie zwykłego tekstu przy ponownej dostępności, wysyłane przez `wp_mail`
* Opcjonalny nagłówek i tekst wprowadzenia pokazywane nad formularzem
* Konfigurowalne etykiety formularza, tekst przycisku, komunikaty wysyłania na ekranie oraz temat/wprowadzenie/zakończenie powiadomienia e-mail
* Umieszczanie formularza za pomocą shortcode’u `[restock_waitlist]` lub widżetu Elementora „Formularz listy oczekujących”
* Włączanie i wyłączanie zapisów gości (niezalogowanych)
* Wiersz dowodu społecznego „N klientów już czeka” nad formularzem (opcjonalny, konfigurowalny, uwzględniający liczbę pojedynczą/mnogą)
* Lista subskrybentów w panelu z filtrem według produktu, eksportem do CSV i usuwaniem jednym kliknięciem
* Szablon formularza z możliwością nadpisania w motywie (`yourtheme/restock/single-product/waitlist-form.php`)
* Zgodność z WooCommerce HPOS (niestandardowe tabele zamówień) oraz blokami Koszyka/Kasy

= You may also like these plugins =

Więcej darmowych wtyczek WooCommerce od WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - progi cen ilościowych i hurtowych z tabelą cen renderowaną po stronie serwera.
* [Sieve - Search & Filter](https://wordpress.org/plugins/sieve/) - szybkie wyszukiwanie i filtrowanie produktów AJAX dla WooCommerce, bez jQuery.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - zgodność z polskim rynkiem: GPSR, Omnibus, RODO, faktury i moduły sklepowe.

Przejrzyj pełny katalog na https://plogins.com/pl/ .

== Installation ==

1. Zainstaluj i włącz WooCommerce (8.0 lub nowszy).
2. Zainstaluj Plogins Waitlist z katalogu wtyczek WordPress lub wgraj folder `plogins-waitlist` do `/wp-content/plugins/`.
3. Włącz wtyczkę na ekranie <strong>Wtyczki</strong>.
4. Opcjonalnie wejdź w <strong>WooCommerce → Plogins Waitlist</strong>, aby dostosować etykiety i tekst powiadomień; rozsądne ustawienia domyślne działają od razu.
5. Formularz listy oczekujących pojawia się automatycznie na każdej stronie produktu niedostępnego lub dostępnego na zamówienie.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-waitlist/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-waitlist/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-waitlist
* <strong>Zgłoszenia błędów i propozycje funkcji</strong> - https://github.com/wppoland/plogins-waitlist/issues


= Does Plogins Waitlist require WooCommerce? =
Tak. Plogins Waitlist jest rozszerzeniem WooCommerce i wymaga WooCommerce 8.0 lub nowszego. Jeśli WooCommerce nie jest zainstalowane lub jest nieaktualne, wyświetli powiadomienie w panelu i pozostanie nieaktywne.

= How are notifications sent? =
Gdy WooCommerce ustawi stan magazynowy produktu na `instock`, Plogins Waitlist wysyła wiadomość e-mail w formie zwykłego tekstu do każdego oczekującego subskrybenta tego produktu (a w przypadku wariantów — także produktu nadrzędnego), korzystając z własnego mechanizmu poczty WordPress w Twojej witrynie (`wp_mail`). Subskrybenci, do których wiadomość dotarła pomyślnie, są oznaczani jako powiadomieni, aby nie kontaktować się z nimi dwukrotnie.

= Does it work with variable products? =
Tak. Najpierw wybierz opcje w standardowym formularzu wariantów WooCommerce. Gdy wybrany wariant jest niedostępny lub dostępny na zamówienie, pojawia się formularz listy oczekujących, a subskrypcja jest zapisywana dla tego konkretnego wariantu.

= Can guests join the waitlist? =
Tak, domyślnie. Możesz ograniczyć zapisy do zalogowanych klientów, odznaczając opcję <strong>Zezwól na subskrypcje gości</strong> w <strong>WooCommerce → Plogins Waitlist</strong>.

= Can customers manage waitlists in My Account? =
Tak. Zalogowani klienci widzą zakładkę <strong>Listy oczekujących</strong> w sekcji Moje konto, z aktywnymi subskrypcjami, bieżącym stanem magazynowym i przyciskiem do opuszczenia każdej listy.

= Does this comply with GDPR / consent requirements? =
Każdy zapis wymaga od klienta zaznaczenia wyraźnego pola zgody, zanim będzie mógł dołączyć do listy oczekujących; bez tego formularz nie zostanie wysłany. Adresy e-mail subskrybentów są przechowywane wyłącznie w osobnej tabeli w Twojej własnej bazie danych WordPress i nigdy nie są wysyłane do żadnej usługi zewnętrznej. Za treść etykiety zgody oraz politykę prywatności Twojej witryny odpowiadasz Ty.

= Can I export the subscriber list? =
Tak. W <strong>WooCommerce → Plogins Waitlist → Subskrybenci</strong> możesz przeglądać subskrybentów, filtrować według produktu i eksportować listę do pliku CSV.

= Does the form reload the page on submit? =
Nie. Formularz jest wysyłany wywołaniem `fetch` w czystym JavaScripcie, a wynik jest ogłaszany w regionie `aria-live`, więc strona pozostaje na miejscu. Plogins Waitlist nie ładuje w tym celu żadnego jQuery; w przypadku produktów z wariantami korzysta z własnego skryptu wariantów WooCommerce, aby wiedzieć, który wariant jest wybrany.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest zgodna z WordPress Multisite. Włącz ją w całej sieci lub na poszczególnych witrynach; każda witryna zachowuje własne ustawienia i dane.

== Screenshots ==

1. Formularz listy oczekujących przy niedostępnym produkcie, gdzie klient zostawia swój adres e-mail i zaznacza wymagane pole zgody, aby otrzymać powiadomienie, gdy produkt wróci do sprzedaży.
2. Ekran ustawień Plogins Waitlist z kartami podzielonymi na sekcje i pomocą kontekstową dotyczącą dostępu gości, nagłówka i wprowadzenia, etykiet formularza, komunikatów na ekranie oraz tekstu powiadomień e-mail.
3. Zakładka Listy oczekujących w sekcji Moje konto klienta, pokazująca produkty, na które czeka, bieżący stan magazynowy oraz przycisk do opuszczenia każdej listy.

== External Services ==

Plogins Waitlist nie łączy się z żadnymi usługami zewnętrznymi. E-maile z powiadomieniem o ponownej dostępności są wysyłane za pomocą mechanizmu poczty WordPress Twojej własnej witryny (`wp_mail`); dane subskrybentów pozostają w Twojej bazie danych WordPress.

== Translations ==

Plogins Waitlist zawiera polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki. Domena tekstowa to `plogins-waitlist`, więc pakiety językowe z WordPress.org mogą też nadpisywać lub rozszerzać dołączone tłumaczenia.

== Changelog ==

= 1.0.4 =
* Dodano dołączone polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki.

= 1.0.3 =
* Porządki wewnętrzne: ujednolicono wewnętrzną tożsamość kodu na Waitlist (wcześniej Restock). Bez zmian w Twoich ustawieniach ani danych; starsze aliasy pozwalają starszym wersjom PRO działać podczas aktualizacji.

= 1.0.2 =
* Dodano na ekranie ustawień przegląd nadchodzących funkcji PRO.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.4.1 =
* Dokumentacja: dodano sekcję „Może Ci się też spodobać”, linkującą pozostałe darmowe wtyczki WooCommerce od WPPoland. Bez zmian funkcjonalnych.

= 0.4.0 =
* Nowość: opcjonalny wiersz dowodu społecznego nad formularzem, pokazujący, ilu klientów już czeka na produkt („12 klientów już czeka na ten produkt.”). Uwzględnia liczbę pojedynczą/mnogą, w pełni konfigurowalny tekst z symbolem zastępczym {count}, ukrywany, gdy nikt nie czeka.
* Nowość: usuwanie subskrybenta bezpośrednio z listy subskrybentów w panelu (zabezpieczone nonce, z potwierdzeniem).

= 0.3.3 =
* Nowość: widżet Elementora dla formularza listy oczekujących na ponowną dostępność, dzięki czemu można go umieścić w edytorze Elementora. Samozabezpieczający się: ładuje się tylko wtedy, gdy Elementor jest aktywny.

= 0.3.2 =
* Poprawka domeny tekstowej w trzech ciągach pomocy w panelu, aby pasowała do sluga wtyczki.

= 0.3.1 =
* Zmieniono nazwę na Plogins Waitlist dla WooCommerce, aby uzyskać odrębną, nieogólną nazwę katalogu. Bez zmian funkcjonalnych.

= 0.3.0 =
* Nowość: zakładka WooCommerce <strong>Listy oczekujących</strong> w sekcji Moje konto ze stanem magazynowym i akcją opuszczenia listy oczekujących.
* Nowość: zapisy na listę oczekujących uwzględniające warianty w produktach z wariantami (formularz pojawia się po wybraniu niedostępnego wariantu).
* Nowość: ustawienia etykiety menu Moje konto, komunikatu przy wyborze wariantu oraz komunikatu potwierdzającego rezygnację z subskrypcji.

= 0.2.0 =
* Nowość: shortcode `[restock_waitlist]` do ręcznego umieszczania formularza listy oczekujących (opcjonalny atrybut `id`, aby wskazać konkretny produkt).
* Nowość: opcjonalny nagłówek formularza i tekst wprowadzenia, konfigurowalne na stronie ustawień.
* Nowość: edytowalne komunikaty formularza na ekranie (powodzenie, nieprawidłowy e-mail, brak zgody, wymagane logowanie).
* Ulepszenie: strona ustawień udostępnia teraz każdą etykietę formularza, komunikat i tekst e-maila obsługiwany przez silnik, zamiast polegać na zakodowanych na stałe wartościach domyślnych.
* Ulepszenie: puste opcjonalne ustawienia poprawnie wracają teraz do wbudowanych wartości domyślnych.

= 0.1.0 =
* Pierwsze wydanie.
