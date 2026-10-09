# APLINE Simple Cookies — baner zgody na cookies dla PrestaShop 9

![PrestaShop 9](https://img.shields.io/badge/PrestaShop-9.x-DF0067) ![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4) ![Wersja](https://img.shields.io/badge/wersja-1.2.2-2ea44f) ![Licencja](https://img.shields.io/badge/licencja-MIT-blue)

Lekki baner zgody na pliki cookies zgodny z RODO, wytycznymi UODO i dyrektywą ePrivacy. Zgoda według kategorii, blokada skryptów firm trzecich do czasu zgody i dziennik zgód na potrzeby rozliczalności. Dla sklepów, które używają Google Analytics 4, Google Tag Managera, Facebook Pixela albo Hotjara i potrzebują banera, który naprawdę spełnia wymagania — a nie tylko przycisku „Akceptuj”.

## Funkcje

- **Zgodny baner** — przyciski „Odrzuć wszystkie”, „Preferencje” i „Akceptuj wszystkie” o jednakowym rozmiarze i widoczności (wymóg CNIL); bez zwodniczych wzorców i bez domyślnie zaznaczonych zgód.
- **Zgoda według kategorii** — na start: niezbędne, funkcjonalne, analityczne i marketingowe; kategorie i listę cookies edytujesz w panelu. Klient akceptuje wszystko, odrzuca wszystko albo wybiera kategorie w oknie preferencji (z listą cookies każdej kategorii).
- **Blokada skryptów przed zgodą** — Google Analytics 4, Google Tag Manager, Facebook Pixel i Hotjar ładują się **dopiero po zgodzie** na przypisaną kategorię. Wpisujesz tylko identyfikator, moduł generuje skrypt.
- **Własne fragmenty kodu** — dowolny inny skrypt (np. LinkedIn, Pinterest) jako HTML/JS, osobno dla kategorii analitycznej, marketingowej i funkcjonalnej.
- **Google Consent Mode v2** (domyślnie włączony) — sygnał „domyślnie brak zgody” w `<head>` przed jakimkolwiek tagiem Google i aktualizacja po decyzji klienta (także przy kolejnej wizycie i przy odrzuceniu przez GPC).
- **Dziennik zgód** — każda decyzja z wersją polityki, językiem, źródłem i opcjonalnie skrótem SHA-256 adresu IP; automatyczne usuwanie starych wpisów, statystyki z 30 dni i eksport CSV.
- **Sygnał GPC** — automatyczne odrzucenie cookies innych niż niezbędne, gdy przeglądarka wysyła Global Privacy Control.
- **Ponowne pytanie o zgodę** — po upływie ustawionego okresu albo po podniesieniu wersji polityki.
- **Wygląd z panelu** — pasek na dole albo okno na środku ekranu, styl jasny lub ciemny, kolory każdego stylu i wszystkie teksty banera (PL + EN).
- **Szablon polityki cookies** — gotowy tekst PL/EN do skopiowania na stronę CMS.
- **Panel po polsku** z dużymi przyciskami najważniejszych akcji.

## Wymagania

- PrestaShop 9.x
- PHP 8.1+

## Instalacja

1. Pobierz `apline_simple_cookies.zip` z zakładki [Releases](../../releases/latest).
2. Panel PrestaShop → Moduły → Menedżer modułów → „Załaduj moduł” → wskaż ZIP.
3. Kliknij „Konfiguruj”.

Instalacja z Gita: sklonuj repozytorium do `modules/apline_simple_cookies` (nazwa folderu musi być równa nazwie modułu).

Moduł od razu zakłada cztery kategorie, kilka przykładowych cookies (sesja PrestaShop, cookies samego modułu, Google Analytics, Facebook Pixel) i bezpieczne ustawienia domyślne. Jeśli w sklepie jest moduł APLINE Simple Google Auth, do kategorii niezbędnych trafia też jego cookie `g_csrf_token`.

## Konfiguracja

Na górze strony konfiguracji jest lista **Pierwsze kroki**. Po kolei:

1. **Zarządzaj kategoriami cookies** — sprawdź nazwy i opisy kategorii (PL i EN). Pole **Identyfikator (slug)** (np. `analytics`) wiąże kategorię ze skryptami — nie zmieniaj go bez potrzeby. Dokładnie jedna kategoria musi mieć włączone **Kategoria niezbędna**.
2. **Zarządzaj pojedynczymi cookies** — dopisz cookies, których naprawdę używa Twój sklep: **Kategoria**, **Nazwa cookie**, **Dostawca**, **Cel (PL)**, **Cel (EN)**, **Ważność**, opcjonalnie **Domena**. Ta lista pokazuje się klientom w oknie preferencji.
3. **Wygląd i działanie banera**:
   - **Język domyślny** — dla sklepów w innym języku niż polski i angielski;
   - **Położenie banera** — „Pasek na dole strony” albo „Okno na środku ekranu”;
   - **Styl** — „Jasny” albo „Ciemny”;
   - **Wyróżniony przycisk** — który przycisk dostaje kolor wyróżnienia;
   - **Ponowne pytanie o zgodę po (dniach)** — zalecane 365;
   - **Respektuj sygnał GPC (Global Privacy Control) z przeglądarki**.
4. **Kolory banera** — dla stylu jasnego i ciemnego: tło banera, tekst, tekst pomocniczy, obramowania, wyróżniony przycisk i linki, tekst wyróżnionego przycisku, tło i tekst pozostałych przycisków. Kliknij pole, żeby wybrać kolor.
5. **Teksty banera (PL + EN)** — tytuł, treść, napisy na przyciskach i link w stopce. Teksty angielskie widzą klienci przeglądający sklep po angielsku.
6. **Skrypty firm trzecich (narzędzia śledzące)** — wpisz identyfikatory (**Google Analytics 4 — identyfikator pomiaru**, **Google Tag Manager — identyfikator kontenera**, **Facebook (Meta) Pixel — identyfikator piksela**, **Hotjar — identyfikator witryny (Site ID)**) i przy każdym wybierz kategorię zgody. Link **Jak to dodać** pokazuje, gdzie znaleźć identyfikator. Zostaw włączone **Włącz Google Consent Mode v2**, chyba że masz własne wdrożenie Consent Mode. Inne skrypty wklej w pola **Własny kod HTML/JS**.
7. **Strona polityki cookies** — kliknij **Pokaż przykładowy szablon**, skopiuj tekst (**Kopiuj do schowka**) do nowej strony CMS (Wygląd → Strony), uzupełnij pola w nawiasach kwadratowych i wpisz adres tej strony w **Adres polityki cookies**. Po każdej zmianie polityki podnieś **Wersja polityki** (np. do 1.0.1) — klienci zobaczą baner ponownie.
8. **Dziennik zgód** — **Zapisuj decyzje o zgodzie w bazie danych**, **Zapisuj skrót (hash) adresu IP**, **Usuwaj wpisy starsze niż (dni)**. Przycisk **Eksportuj dziennik zgód (CSV)** pobiera cały dziennik.
9. Kliknij **Zapisz ustawienia** i sprawdź baner w oknie prywatnym przeglądarki.

Klient może w każdej chwili wrócić do ustawień przez link **Ustawienia cookies** w stopce sklepu.

## Aktualizacja

Wgraj ZIP nowej wersji tak jak przy instalacji — PrestaShop uruchomi skrypty z `upgrade/` i zachowa ustawienia.

Aktualizacja z 1.1.0 do 1.2.0 zapisuje w konfiguracji dotychczasowe kolory banera (te, które wcześniej były wpisane w arkuszu CSS), więc baner wygląda tak samo jak przed aktualizacją; teksty banera, kategorie, cookies i dziennik zgód zostają bez zmian. Kolory możesz potem zmienić w sekcji **Kolory banera**.

## Odinstalowanie

Odinstaluj moduł w Menedżerze modułów. Znikają: trzy tabele modułu (kategorie, cookies, dziennik zgód — razem z zapisanymi zgodami), wszystkie ustawienia `ASCO_*` i dwie ukryte zakładki panelu. Cookies `asco_consent` i `asco_visitor` zostają w przeglądarkach klientów do wygaśnięcia. Odinstalowanie można bezpiecznie powtórzyć.

## Jak to działa

- **Hooki:** `displayHeader` (konfiguracja banera w `window.ASCO`, kolory jako zmienne CSS i — przy włączonym Consent Mode v2 — sygnał `gtag('consent','default', …)`), `actionFrontControllerSetMedia` (CSS i JS banera), `displayBeforeBodyClosingTag` (baner i okno preferencji), `displayFooterAfter` (link „Ustawienia cookies”).
- **Zapis zgody:** decyzja trafia do cookie `asco_consent` w przeglądarce i do kontrolera `consent` modułu, który zapisuje ją w dzienniku i zwraca listę skryptów do załadowania. Kategoria niezbędna jest zawsze włączona, nieznane kategorie są pomijane.
- **Tabele:** `asco_category`, `asco_entry`, `asco_consent_log` (z prefiksem bazy sklepu).
- **Kolory:** zmienne CSS (`--asco-bg`, `--asco-primary` itd.) na selektorze `.apline-simple-cookies.asco-banner` (i `.asco-style-dark`). Motyw może je nadpisać bardziej szczegółowym selektorem, np. `body .apline-simple-cookies.asco-banner`.
- **Prywatność:** w dzienniku jest anonimowy token wizyty, identyfikator klienta (jeśli zalogowany), decyzja, wersja polityki, język, źródło, przeglądarka i — jeśli włączone — skrót SHA-256 adresu IP z solą unikalną dla instalacji (adresu IP nie da się odtworzyć).
- **Zgodność:** moduł wspiera RODO (art. 4 pkt 11, art. 7 ust. 3, art. 5 ust. 2), art. 5 ust. 3 dyrektywy ePrivacy, wytyczne EROD o zwodniczych wzorcach oraz zalecenia UODO i CNIL. Treść polityki i listę cookies dopasowujesz sam — moduł jest narzędziem, a nie poradą prawną.

### Gdy coś nie działa

- **Baner się nie pokazuje** — sprawdź w oknie prywatnym (decyzja mogła być już zapisana) albo usuń cookie `asco_consent`.
- **Skrypt ładuje się mimo odrzucenia** — kod śledzący musi być wpisany w tym module, a nie na sztywno w motywie lub innym module.
- **Polskie znaki w CSV wyglądają źle** — plik ma znacznik UTF-8 (BOM); otwórz go w Excelu, który go obsługuje, albo zaimportuj jako UTF-8.

## Zmiany

Historia wersji: [CHANGELOG.md](CHANGELOG.md).

## Licencja i autor

MIT — pełny tekst w [LICENSE.md](LICENSE.md). Moduł możesz używać, zmieniać i rozpowszechniać, także komercyjnie; zachowaj tylko informację o prawach autorskich i licencji.

Arkadiusz Pielechowski · [pielechowski.pl](https://pielechowski.pl)
