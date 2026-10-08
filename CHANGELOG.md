# Historia zmian

Wszystkie istotne zmiany modułu APLINE Simple Cookies dla PrestaShop 9.
Format opiera się na [Keep a Changelog](https://keepachangelog.com/pl/),
a numeracja wersji na [wersjonowaniu semantycznym](https://semver.org/lang/pl/).

## [1.2.0] – 2026-10-08

### Zmieniono
- **Panel po polsku.** Wszystkie teksty panelu (konfiguracja, listy i formularze
  kategorii oraz cookies, komunikaty błędów, okna pomocy „Jak to dodać”, nazwa
  i opis modułu) są teraz pisane po polsku jako tekst źródłowy — wyświetlają się
  zawsze, niezależnie od pamięci podręcznej tłumaczeń PrestaShop 9. Okna pomocy
  mają już tylko wersję polską.
- **Duże przyciski najważniejszych akcji:** „Zarządzaj kategoriami cookies”,
  „Zarządzaj pojedynczymi cookies”, „Eksportuj dziennik zgód (CSV)” i „Zapisz
  ustawienia”. Arkusz `views/css/admin.css` ładuje się teraz przez
  `addCSS()` w `getContent()`.
- Ukryte zakładki panelu nazywają się „Kategorie cookies” i „Pliki cookies”
  (we wszystkich językach).
- Listy wyboru kategorii w panelu pokazują polskie nazwy kategorii.
- Nowe, neutralne domyślne teksty banera po polsku (tylko przy nowej
  instalacji — istniejący sklep zachowuje swoje teksty).
- Poprawki językowe w polskim szablonie polityki cookies.
- `ps_versions_compliancy`: od `9.0.0` do bieżącej wersji PrestaShop.

### Dodano
- **Kolory banera w konfiguracji.** Nowa sekcja „Kolory banera”: osobno dla
  stylu jasnego i ciemnego tło, tekst, tekst pomocniczy, obramowania,
  wyróżniony przycisk (i linki), jego tekst oraz tło i tekst pozostałych
  przycisków — 16 kluczy `ASCO_COLOR_{LIGHT|DARK}_*`. Moduł wypisuje je
  w nagłówku strony jako zmienne CSS o tej samej szczegółowości selektora co
  dotąd, więc nadpisania kolorów w motywie lub innym module nadal działają.
  Nowa instalacja dostaje neutralną paletę (biały baner z ciemnoszarym
  przyciskiem; styl ciemny w odcieniach szarości).
- `upgrade/upgrade-1.2.0.php`: ustawia kolory banera na wartości z wersji
  1.1.0 (tylko gdy klucz jeszcze nie istnieje), żeby wygląd sklepu się nie
  zmienił, i zmienia nazwy ukrytych zakładek na polskie.
- Ochronny `index.php` w katalogu `upgrade/`.

## [1.1.0] – 2026-09-15

### Dodano
- **Google Consent Mode v2.** Nowe ustawienie `ASCO_CONSENT_MODE_V2`
  (domyślnie włączone): jak najwcześniej w `<head>`, zanim załaduje się
  jakikolwiek tag Google, moduł wysyła `gtag('consent','default', {ad_storage:
  'denied', ad_user_data: 'denied', ad_personalization: 'denied',
  analytics_storage: 'denied', functionality_storage: 'granted',
  security_storage: 'granted', wait_for_update: 500})`. Gdy odwiedzający
  podejmie decyzję — a także przy kolejnej wizycie z ważną zapisaną decyzją
  albo przy automatycznym odrzuceniu przez GPC — `banner.js` przekazuje ją
  przez `gtag('consent','update', …)`: kategoria `analytics` steruje
  `analytics_storage`, a `marketing` — `ad_storage`, `ad_user_data`
  i `ad_personalization`. Dotychczasowa blokada skryptów (żaden skrypt nie
  ładuje się przed zgodą) działa bez zmian, dodatkowo. Wyłącz to ustawienie,
  jeśli masz własne wdrożenie Consent Mode (np. szablon w Google Tag Managerze).
- `upgrade/upgrade-1.1.0.php`: włącza `ASCO_CONSENT_MODE_V2` w sklepach,
  które zainstalowały moduł przed tą wersją.

### Naprawiono
- Oba wywołania `fputcsv()` w `exportConsentLog()` przekazują jawnie
  separator, ogranicznik i znak ucieczki (`,`, `"`, `\`). PHP 8.4 oznacza
  poleganie na domyślnym znaku ucieczki jako przestarzałe — plik CSV jest
  identyczny jak wcześniej, a ostrzeżenie znika.

## [1.0.0] – 2026-05-31

### Dodano
- Pierwsze wydanie.
- Baner zgody na cookies zgodny z RODO, wytycznymi UODO i dyrektywą ePrivacy,
  dwujęzyczny PL + EN (domyślnie polski), z przyciskami „Odrzuć”,
  „Preferencje” i „Akceptuj” o jednakowej widoczności.
- Trzy tabele w bazie: kategorie cookies, pojedyncze cookies i dziennik zgód.
- Dwa ekrany panelu (kategorie cookies i cookies) z pełną edycją, walidacją
  odrzucającą błędne dane, zmianą kolejności przeciąganiem i zasadą jednej
  kategorii niezbędnej.
- Strona konfiguracji modułu: wygląd i działanie banera, edytor tekstów PL/EN
  obok siebie, ustawienia dziennika zgód ze statystykami z 30 dni i eksportem
  CSV strumieniowo.
- Obsługa narzędzi śledzących (Google Analytics 4, Google Tag Manager,
  Facebook Pixel, Hotjar) z oknami pomocy „Jak to dodać” przy każdym polu oraz
  własne fragmenty HTML/JS dla każdej kategorii zgody.
- Blokada skryptów: żaden skrypt firmy trzeciej nie trafia na stronę przed
  zgodą; `banner.js` wstrzykuje każde narzędzie dopiero po zgodzie na jego
  kategorię.
- Dziennik zgód ze skrótem SHA-256 adresu IP (sól unikalna dla instalacji),
  automatycznym usuwaniem starych wpisów i eksportem CSV na potrzeby
  rozliczalności.
- Obsługa sygnału GPC (Global Privacy Control), automatyczne zwinięcie okna
  preferencji po 5 sekundach, ponowne pytanie o zgodę po zmianie wersji
  polityki lub wygaśnięciu zgody.
- Okno „Pokaż przykładowy szablon” z gotowym szablonem polityki cookies PL/EN.
- Opcjonalne wykrywanie modułu APLINE Simple Google Auth (dodaje jego cookie
  `g_csrf_token` do kategorii niezbędnych).
