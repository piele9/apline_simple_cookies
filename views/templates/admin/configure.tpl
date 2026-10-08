{*
 * APLINE Simple Cookies module for PrestaShop 9.
 * @author APLINE Arkadiusz Pielechowski
 *}
<div class="panel">
  <h3><i class="icon-check-square-o"></i> {l s='Pierwsze kroki' d='Modules.Aplinesimplecookies.Admin'}</h3>
  <ol style="margin-left:18px;">
    <li>{l s='Sprawdź kategorie cookies (na start są cztery: niezbędne, funkcjonalne, analityczne i marketingowe).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Uzupełnij listę cookies w każdej kategorii.' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Dodaj identyfikatory narzędzi śledzących (Google Analytics, Tag Manager, Facebook Pixel, Hotjar).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Ustaw wygląd, kolory i teksty banera (PL + EN).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Utwórz stronę CMS z polityką cookies (możesz skorzystać z przykładowego szablonu).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Sprawdź baner w oknie prywatnym (incognito).' d='Modules.Aplinesimplecookies.Admin'}</li>
  </ol>
  <a class="btn btn-primary btn-lg apline-btn-duzy" href="{$asco_category_url|escape:'html':'UTF-8'}"><i class="icon-folder"></i> {l s='Zarządzaj kategoriami cookies' d='Modules.Aplinesimplecookies.Admin'}</a>
  <a class="btn btn-primary btn-lg apline-btn-duzy" href="{$asco_entry_url|escape:'html':'UTF-8'}"><i class="icon-list"></i> {l s='Zarządzaj pojedynczymi cookies' d='Modules.Aplinesimplecookies.Admin'}</a>
</div>

<form id="asco-config-form" method="post" action="{$asco_form_action|escape:'html':'UTF-8'}">

  {* ---- Banner appearance & behavior ---- *}
  <div class="panel">
    <h3><i class="icon-cogs"></i> {l s='Wygląd i działanie banera' d='Modules.Aplinesimplecookies.Admin'}</h3>

    <div class="form-group">
      <label>{l s='Język domyślny' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_DEFAULT_LANG" class="form-control fixed-width-lg">
        <option value="pl" {if $asco_conf.ASCO_DEFAULT_LANG == 'pl'}selected{/if}>{l s='Polski (PL)' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="en" {if $asco_conf.ASCO_DEFAULT_LANG == 'en'}selected{/if}>{l s='Angielski (EN)' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
      <p class="help-block">{l s='Używany, gdy językiem sklepu nie jest ani polski, ani angielski.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Położenie banera' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_BANNER_POSITION" class="form-control fixed-width-lg">
        <option value="bottom" {if $asco_conf.ASCO_BANNER_POSITION == 'bottom'}selected{/if}>{l s='Pasek na dole strony' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="center_modal" {if $asco_conf.ASCO_BANNER_POSITION == 'center_modal'}selected{/if}>{l s='Okno na środku ekranu' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
    </div>

    <div class="form-group">
      <label>{l s='Styl' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_BANNER_STYLE" class="form-control fixed-width-lg">
        <option value="light" {if $asco_conf.ASCO_BANNER_STYLE == 'light'}selected{/if}>{l s='Jasny' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="dark" {if $asco_conf.ASCO_BANNER_STYLE == 'dark'}selected{/if}>{l s='Ciemny' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
      <p class="help-block">{l s='Kolory każdego stylu ustawisz niżej, w sekcji „Kolory banera”.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Wyróżniony przycisk' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_PRIMARY_BUTTON" class="form-control fixed-width-lg">
        <option value="accept_all" {if $asco_conf.ASCO_PRIMARY_BUTTON == 'accept_all'}selected{/if}>{l s='Akceptuj wszystkie' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="save_choices" {if $asco_conf.ASCO_PRIMARY_BUTTON == 'save_choices'}selected{/if}>{l s='Zapisz wybór' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
      <p class="help-block">{l s='Przyciski „Odrzuć” i „Akceptuj” mają zawsze ten sam rozmiar i widoczność (wymóg CNIL) — to ustawienie zmienia tylko kolor wyróżnienia.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Ponowne pytanie o zgodę po (dniach)' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="number" name="ASCO_REPROMPT_DAYS" class="form-control fixed-width-sm" min="30" max="730" value="{$asco_conf.ASCO_REPROMPT_DAYS|escape:'html':'UTF-8'}">
      <p class="help-block">{l s='Zalecane: 365. Okres krótszy niż 180 dni UODO i CNIL mogą uznać za zwodniczy wzorzec (dark pattern).' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_RESPECT_GPC" value="1" {if $asco_conf.ASCO_RESPECT_GPC}checked{/if}>
        {l s='Respektuj sygnał GPC (Global Privacy Control) z przeglądarki' d='Modules.Aplinesimplecookies.Admin'}
      </label>
      <p class="help-block">{l s='Automatycznie odrzuca cookies inne niż niezbędne, gdy przeglądarka wysyła nagłówek Sec-GPC: 1. Zalecane przez UODO.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
  </div>

  {* ---- Banner colours ---- *}
  <div class="panel">
    <h3><i class="icon-tint"></i> {l s='Kolory banera' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Baner używa kolorów stylu wybranego w polu „Styl”. Kliknij pole, żeby wybrać kolor z palety.' d='Modules.Aplinesimplecookies.Admin'}</p>
    <table class="table asco-colors-table">
      <thead>
        <tr>
          <th style="width:40%;"></th>
          <th>{l s='Styl jasny' d='Modules.Aplinesimplecookies.Admin'}</th>
          <th>{l s='Styl ciemny' d='Modules.Aplinesimplecookies.Admin'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$asco_color_rows item=row}
          <tr>
            <td><strong>{$row.label|escape:'html':'UTF-8'}</strong></td>
            <td><input type="color" class="asco-color-input" name="{$row.light_key|escape:'html':'UTF-8'}" value="{$asco_conf[$row.light_key]|escape:'html':'UTF-8'}"></td>
            <td><input type="color" class="asco-color-input" name="{$row.dark_key|escape:'html':'UTF-8'}" value="{$asco_conf[$row.dark_key]|escape:'html':'UTF-8'}"></td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  </div>

  {* ---- Banner copy (PL + EN) ---- *}
  <div class="panel">
    <h3><i class="icon-edit"></i> {l s='Teksty banera (PL + EN)' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Teksty angielskie widzą klienci, którzy przeglądają sklep po angielsku.' d='Modules.Aplinesimplecookies.Admin'}</p>
    <table class="table">
      <thead>
        <tr><th style="width:18%;"></th><th>{l s='Polski (PL)' d='Modules.Aplinesimplecookies.Admin'}</th><th>{l s='Angielski (EN)' d='Modules.Aplinesimplecookies.Admin'}</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>{l s='Tytuł' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_TITLE_PL" value="{$asco_conf.ASCO_COPY_TITLE_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_TITLE_EN" value="{$asco_conf.ASCO_COPY_TITLE_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Treść' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><textarea class="form-control" rows="4" name="ASCO_COPY_BODY_PL">{$asco_conf.ASCO_COPY_BODY_PL|escape:'html':'UTF-8'}</textarea></td>
          <td><textarea class="form-control" rows="4" name="ASCO_COPY_BODY_EN">{$asco_conf.ASCO_COPY_BODY_EN|escape:'html':'UTF-8'}</textarea></td>
        </tr>
        <tr>
          <td><strong>{l s='Przycisk „Akceptuj”' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_ACCEPT_PL" value="{$asco_conf.ASCO_COPY_BTN_ACCEPT_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_ACCEPT_EN" value="{$asco_conf.ASCO_COPY_BTN_ACCEPT_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Przycisk „Odrzuć”' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_REJECT_PL" value="{$asco_conf.ASCO_COPY_BTN_REJECT_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_REJECT_EN" value="{$asco_conf.ASCO_COPY_BTN_REJECT_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Przycisk „Preferencje”' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_PREFS_PL" value="{$asco_conf.ASCO_COPY_BTN_PREFS_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_PREFS_EN" value="{$asco_conf.ASCO_COPY_BTN_PREFS_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Przycisk „Zapisz wybór”' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_SAVE_PL" value="{$asco_conf.ASCO_COPY_BTN_SAVE_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_SAVE_EN" value="{$asco_conf.ASCO_COPY_BTN_SAVE_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Link w stopce' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_FOOTER_LINK_PL" value="{$asco_conf.ASCO_COPY_FOOTER_LINK_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_FOOTER_LINK_EN" value="{$asco_conf.ASCO_COPY_FOOTER_LINK_EN|escape:'html':'UTF-8'}"></td>
        </tr>
      </tbody>
    </table>
  </div>

  {* ---- Third-party scripts (Custom Scripts) ---- *}
  <div class="panel">
    <h3><i class="icon-plug"></i> {l s='Skrypty firm trzecich (narzędzia śledzące)' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Wpisz tylko identyfikator usługi — moduł sam wygeneruje skrypt i załaduje go dopiero wtedy, gdy klient zgodzi się na wybraną kategorię. Nie wiesz, gdzie znaleźć identyfikator? Kliknij „Jak to dodać”.' d='Modules.Aplinesimplecookies.Admin'}</p>

    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_CONSENT_MODE_V2" value="1" {if $asco_conf.ASCO_CONSENT_MODE_V2}checked{/if}>
        {l s='Włącz Google Consent Mode v2' d='Modules.Aplinesimplecookies.Admin'}
      </label>
      <p class="help-block">{l s='Wysyła do Google wymagany sygnał „domyślnie brak zgody” (gtag consent default) w nagłówku strony, zanim załaduje się jakikolwiek tag Google, a po decyzji klienta aktualizuje go (consent update) — także przy kolejnej wizycie z zapisaną decyzją. Google wymaga tego dla tagów GA4 i Google Ads od marca 2024 r. w EOG, Wielkiej Brytanii i Szwajcarii. Zostaw włączone, chyba że masz własne wdrożenie Consent Mode (np. w Google Tag Managerze).' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    {* Google Analytics 4 *}
    <div class="form-group">
      <label>{l s='Google Analytics 4 — identyfikator pomiaru' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="ga4"><i class="icon-question-circle"></i> {l s='Jak to dodać' d='Modules.Aplinesimplecookies.Admin'}</a>
      </label>
      <div class="row">
        <div class="col-lg-6"><input type="text" class="form-control" name="ASCO_GA4_ID" value="{$asco_conf.ASCO_GA4_ID|escape:'html':'UTF-8'}" placeholder="G-XXXXXXXXXX"></div>
        <div class="col-lg-6">
          <select name="ASCO_GA4_CATEGORY" class="form-control">
            {foreach from=$asco_category_options key=slug item=cname}
              <option value="{$slug|escape:'html':'UTF-8'}" {if $asco_conf.ASCO_GA4_CATEGORY == $slug}selected{/if}>{$cname|escape:'html':'UTF-8'} ({$slug|escape:'html':'UTF-8'})</option>
            {/foreach}
          </select>
        </div>
      </div>
    </div>

    {* Google Tag Manager *}
    <div class="form-group">
      <label>{l s='Google Tag Manager — identyfikator kontenera' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="gtm"><i class="icon-question-circle"></i> {l s='Jak to dodać' d='Modules.Aplinesimplecookies.Admin'}</a>
      </label>
      <div class="row">
        <div class="col-lg-6"><input type="text" class="form-control" name="ASCO_GTM_ID" value="{$asco_conf.ASCO_GTM_ID|escape:'html':'UTF-8'}" placeholder="GTM-XXXXXXX"></div>
        <div class="col-lg-6">
          <select name="ASCO_GTM_CATEGORY" class="form-control">
            {foreach from=$asco_category_options key=slug item=cname}
              <option value="{$slug|escape:'html':'UTF-8'}" {if $asco_conf.ASCO_GTM_CATEGORY == $slug}selected{/if}>{$cname|escape:'html':'UTF-8'} ({$slug|escape:'html':'UTF-8'})</option>
            {/foreach}
          </select>
        </div>
      </div>
    </div>

    {* Facebook Pixel *}
    <div class="form-group">
      <label>{l s='Facebook (Meta) Pixel — identyfikator piksela' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="fb"><i class="icon-question-circle"></i> {l s='Jak to dodać' d='Modules.Aplinesimplecookies.Admin'}</a>
      </label>
      <div class="row">
        <div class="col-lg-6"><input type="text" class="form-control" name="ASCO_FB_PIXEL_ID" value="{$asco_conf.ASCO_FB_PIXEL_ID|escape:'html':'UTF-8'}" placeholder="123456789012345"></div>
        <div class="col-lg-6">
          <select name="ASCO_FB_PIXEL_CATEGORY" class="form-control">
            {foreach from=$asco_category_options key=slug item=cname}
              <option value="{$slug|escape:'html':'UTF-8'}" {if $asco_conf.ASCO_FB_PIXEL_CATEGORY == $slug}selected{/if}>{$cname|escape:'html':'UTF-8'} ({$slug|escape:'html':'UTF-8'})</option>
            {/foreach}
          </select>
        </div>
      </div>
    </div>

    {* Hotjar *}
    <div class="form-group">
      <label>{l s='Hotjar — identyfikator witryny (Site ID)' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="hotjar"><i class="icon-question-circle"></i> {l s='Jak to dodać' d='Modules.Aplinesimplecookies.Admin'}</a>
      </label>
      <div class="row">
        <div class="col-lg-6"><input type="text" class="form-control" name="ASCO_HOTJAR_ID" value="{$asco_conf.ASCO_HOTJAR_ID|escape:'html':'UTF-8'}" placeholder="1234567"></div>
        <div class="col-lg-6">
          <select name="ASCO_HOTJAR_CATEGORY" class="form-control">
            {foreach from=$asco_category_options key=slug item=cname}
              <option value="{$slug|escape:'html':'UTF-8'}" {if $asco_conf.ASCO_HOTJAR_CATEGORY == $slug}selected{/if}>{$cname|escape:'html':'UTF-8'} ({$slug|escape:'html':'UTF-8'})</option>
            {/foreach}
          </select>
        </div>
      </div>
    </div>

    <hr>
    <p class="help-block"><strong>{l s='Własne fragmenty kodu' d='Modules.Aplinesimplecookies.Admin'}</strong> &mdash;
      {l s='Dla narzędzi spoza listy powyżej (np. LinkedIn Insight Tag, Pinterest Tag). Wklej zwykły kod HTML/JS — trafi na stronę dopiero po zgodzie klienta na daną kategorię.' d='Modules.Aplinesimplecookies.Admin'}
    </p>
    <div class="alert alert-warning">{l s='Wklejony tu kod uruchomi się w sklepie, gdy klient zgodzi się na tę kategorię. Wklejaj tylko kod, któremu ufasz.' d='Modules.Aplinesimplecookies.Admin'}</div>

    <div class="form-group">
      <label>{l s='Własny kod HTML/JS — zgoda na cookies analityczne' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_ANALYTICS">{$asco_conf.ASCO_CUSTOM_HEAD_ANALYTICS|escape:'html':'UTF-8'}</textarea>
    </div>
    <div class="form-group">
      <label>{l s='Własny kod HTML/JS — zgoda na cookies marketingowe' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_MARKETING">{$asco_conf.ASCO_CUSTOM_HEAD_MARKETING|escape:'html':'UTF-8'}</textarea>
    </div>
    <div class="form-group">
      <label>{l s='Własny kod HTML/JS — zgoda na cookies funkcjonalne' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_FUNCTIONAL">{$asco_conf.ASCO_CUSTOM_HEAD_FUNCTIONAL|escape:'html':'UTF-8'}</textarea>
    </div>
  </div>

  {* ---- Cookie policy ---- *}
  <div class="panel">
    <h3><i class="icon-file-text-o"></i> {l s='Strona polityki cookies' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <div class="form-group">
      <label>{l s='Adres polityki cookies' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="text" name="ASCO_POLICY_URL" class="form-control" value="{$asco_conf.ASCO_POLICY_URL|escape:'html':'UTF-8'}" placeholder="/content/8-polityka-cookies">
      <p class="help-block">{l s='Adres strony CMS z pełną polityką cookies. Link do niej pojawi się na banerze.' d='Modules.Aplinesimplecookies.Admin'}</p>
      <button type="button" class="btn btn-default" id="asco-show-policy"><i class="icon-clipboard"></i> {l s='Pokaż przykładowy szablon' d='Modules.Aplinesimplecookies.Admin'}</button>
    </div>
    <div class="form-group">
      <label>{l s='Wersja polityki' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="text" name="ASCO_POLICY_VERSION" class="form-control fixed-width-sm" value="{$asco_conf.ASCO_POLICY_VERSION|escape:'html':'UTF-8'}">
      <p class="help-block">{l s='Podnieś numer (np. do 1.0.1), gdy zmienisz politykę — przy następnej wizycie wszyscy klienci zobaczą baner ponownie.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
  </div>

  {* ---- Audit log ---- *}
  <div class="panel">
    <h3><i class="icon-archive"></i> {l s='Dziennik zgód' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_LOG_CONSENTS" value="1" {if $asco_conf.ASCO_LOG_CONSENTS}checked{/if}>
        {l s='Zapisuj decyzje o zgodzie w bazie danych' d='Modules.Aplinesimplecookies.Admin'}
      </label>
    </div>
    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_LOG_IP" value="1" {if $asco_conf.ASCO_LOG_IP}checked{/if}>
        {l s='Zapisuj skrót (hash) adresu IP' d='Modules.Aplinesimplecookies.Admin'}
      </label>
      <p class="help-block">{l s='Zapisuje skrót SHA-256 adresu IP z losową solą tej instalacji. Z takiego skrótu nie da się odtworzyć adresu IP.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
    <div class="form-group">
      <label>{l s='Usuwaj wpisy starsze niż (dni)' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="number" name="ASCO_LOG_RETENTION_DAYS" class="form-control fixed-width-sm" min="30" max="3650" value="{$asco_conf.ASCO_LOG_RETENTION_DAYS|escape:'html':'UTF-8'}">
    </div>

    <div class="alert alert-info">
      <strong>{l s='Statystyki (ostatnie 30 dni)' d='Modules.Aplinesimplecookies.Admin'}</strong>
      <ul style="margin:8px 0 0 18px;">
        <li>{l s='Wszystkie decyzje' d='Modules.Aplinesimplecookies.Admin'}: <strong>{$asco_stats.total|intval}</strong></li>
        <li>{l s='Z banera' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.banner|intval}</li>
        <li>{l s='Z okna preferencji' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.prefs|intval}</li>
        <li>{l s='Z linku w stopce' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.footer_link|intval}</li>
        <li>{l s='Odrzucone automatycznie (GPC)' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.gpc|intval}</li>
      </ul>
    </div>

    <a class="btn btn-primary btn-lg apline-btn-duzy" href="{$asco_export_url|escape:'html':'UTF-8'}"><i class="icon-download"></i> {l s='Eksportuj dziennik zgód (CSV)' d='Modules.Aplinesimplecookies.Admin'}</a>
  </div>

  <div class="panel">
    <button type="submit" name="{$asco_submit_token|escape:'html':'UTF-8'}" class="btn btn-primary btn-lg apline-btn-duzy pull-right">
      <i class="icon-save"></i> {l s='Zapisz ustawienia' d='Modules.Aplinesimplecookies.Admin'}
    </button>
    <div class="clearfix"></div>
  </div>

</form>

{* ============================ Help modals ============================ *}
<div class="asco-modal" id="asco-modal-ga4">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close aria-label="{l s='Zamknij' d='Modules.Aplinesimplecookies.Admin'}">&times;</button>
    <h3>Google Analytics 4 (GA4)</h3>
    <h4>{l s='Jak znaleźć i wkleić identyfikator pomiaru' d='Modules.Aplinesimplecookies.Admin'}</h4>
    <ol>
      <li>Wejdź na <code>https://analytics.google.com</code> i zaloguj się.</li>
      <li>Kliknij <strong>Administracja</strong> (koło zębate) → <strong>Strumienie danych</strong> → wybierz strumień internetowy.</li>
      <li>Skopiuj <strong>Identyfikator pomiaru</strong> — wygląda tak: <code>G-XXXXXXXXXX</code>.</li>
      <li>Wklej go w pole <strong>Google Analytics 4 — identyfikator pomiaru</strong> i wybierz kategorię zgody (domyślnie <code>analytics</code>).</li>
      <li>Zapisz ustawienia. GA4 załaduje się dopiero wtedy, gdy klient zaakceptuje tę kategorię.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-gtm">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close aria-label="{l s='Zamknij' d='Modules.Aplinesimplecookies.Admin'}">&times;</button>
    <h3>Google Tag Manager (GTM)</h3>
    <h4>{l s='Jak znaleźć identyfikator kontenera' d='Modules.Aplinesimplecookies.Admin'}</h4>
    <ol>
      <li>Wejdź na <code>https://tagmanager.google.com</code> i wybierz konto oraz kontener.</li>
      <li>Identyfikator <code>GTM-XXXXXXX</code> widać u góry, obok nazwy kontenera.</li>
      <li>Wklej go w pole <strong>Google Tag Manager — identyfikator kontenera</strong>, wybierz kategorię (domyślnie <code>analytics</code>) i zapisz ustawienia.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-fb">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close aria-label="{l s='Zamknij' d='Modules.Aplinesimplecookies.Admin'}">&times;</button>
    <h3>Facebook (Meta) Pixel</h3>
    <h4>{l s='Jak znaleźć identyfikator piksela' d='Modules.Aplinesimplecookies.Admin'}</h4>
    <ol>
      <li>Wejdź na <code>https://business.facebook.com</code> → <strong>Menedżer zdarzeń</strong>.</li>
      <li>Wybierz swój piksel → <strong>Ustawienia</strong>. Identyfikator piksela to ciąg 15–16 cyfr.</li>
      <li>Wklej go w pole <strong>Facebook (Meta) Pixel — identyfikator piksela</strong> (domyślna kategoria <code>marketing</code>) i zapisz ustawienia.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-hotjar">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close aria-label="{l s='Zamknij' d='Modules.Aplinesimplecookies.Admin'}">&times;</button>
    <h3>Hotjar</h3>
    <h4>{l s='Jak znaleźć identyfikator witryny (Site ID)' d='Modules.Aplinesimplecookies.Admin'}</h4>
    <ol>
      <li>Wejdź na <code>https://insights.hotjar.com</code> → <strong>Settings</strong> → <strong>Sites &amp; Organizations</strong> (panel Hotjara jest po angielsku).</li>
      <li><strong>Site ID</strong> to liczba (np. <code>1234567</code>).</li>
      <li>Wklej ją w pole <strong>Hotjar — identyfikator witryny (Site ID)</strong> i zapisz ustawienia.</li>
    </ol>
  </div>
</div>

{* ====================== Cookie policy template ====================== *}
<div class="asco-modal" id="asco-modal-policy">
  <div class="asco-modal-box asco-modal-lg">
    <button type="button" class="asco-modal-close" data-asco-close aria-label="{l s='Zamknij' d='Modules.Aplinesimplecookies.Admin'}">&times;</button>
    <h3>{l s='Polityka cookies — przykładowy szablon' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Skopiuj tekst do nowej strony CMS (Wygląd → Strony), uzupełnij pola w nawiasach kwadratowych i wklej adres tej strony w pole „Adres polityki cookies”. Szablon jest punktem wyjścia, a nie poradą prawną.' d='Modules.Aplinesimplecookies.Admin'}</p>
    <ul class="nav nav-tabs asco-tabs">
      <li class="active"><a href="#" data-asco-tab="pl">{l s='Polski (PL)' d='Modules.Aplinesimplecookies.Admin'}</a></li>
      <li><a href="#" data-asco-tab="en">{l s='Angielski (EN)' d='Modules.Aplinesimplecookies.Admin'}</a></li>
    </ul>
    <div class="asco-tabpane" data-asco-tabpane="pl">
      <button type="button" class="btn btn-default asco-copy-btn" data-asco-copy="asco-policy-pl"><i class="icon-copy"></i> {l s='Kopiuj do schowka' d='Modules.Aplinesimplecookies.Admin'}</button>
      <textarea id="asco-policy-pl" class="form-control asco-policy-text" readonly rows="18">{include file="./policy-template-pl.tpl"}</textarea>
    </div>
    <div class="asco-tabpane" data-asco-tabpane="en" style="display:none;">
      <button type="button" class="btn btn-default asco-copy-btn" data-asco-copy="asco-policy-en"><i class="icon-copy"></i> {l s='Kopiuj do schowka' d='Modules.Aplinesimplecookies.Admin'}</button>
      <textarea id="asco-policy-en" class="form-control asco-policy-text" readonly rows="18">{include file="./policy-template-en.tpl"}</textarea>
    </div>
  </div>
</div>

<script src="{$asco_module_uri|escape:'html':'UTF-8'}views/js/admin.js"></script>
