{*
 * APLINE Simple Cookies module for PrestaShop 9 — consent banner.
 * Reject / Preferences / Accept are kept at equal prominence (CNIL).
 * Hidden by default; banner.js reveals it when consent is needed.
 * @author APLINE Arkadiusz Pielechowski
 *}
<div id="asco-banner"
     class="apline-simple-cookies asco-banner asco-pos-{$asco_position|escape:'html':'UTF-8'} asco-style-{$asco_style|escape:'html':'UTF-8'} asco-hidden"
     role="dialog" aria-modal="true" aria-labelledby="asco-title" aria-describedby="asco-body">
  <div class="asco-overlay"></div>
  <div class="asco-banner-inner">

    {* ---- Main view ---- *}
    <div class="asco-main" data-asco-view="main">
      <h2 id="asco-title" class="asco-title">{$asco_copy.title|escape:'html':'UTF-8'}</h2>
      <p id="asco-body" class="asco-body">{$asco_copy.body|escape:'html':'UTF-8'|nl2br nofilter}</p>
      {if $asco_copy.policy_url}
        <p class="asco-policy-link">
          <a href="{$asco_copy.policy_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">
            {if $asco_lang == 'pl'}Polityka cookies{else}Cookie policy{/if}
          </a>
        </p>
      {/if}
      <div class="asco-actions">
        <button type="button" class="asco-btn asco-btn-reject{if $asco_primary == 'accept_all'} asco-btn-secondary{/if}" data-asco-action="reject_all">
          {$asco_copy.btn_reject|escape:'html':'UTF-8'}
        </button>
        <button type="button" class="asco-btn asco-btn-prefs asco-btn-secondary" data-asco-action="open_prefs">
          {$asco_copy.btn_prefs|escape:'html':'UTF-8'}
        </button>
        <button type="button" class="asco-btn asco-btn-accept{if $asco_primary == 'accept_all'} asco-btn-primary{/if}" data-asco-action="accept_all">
          {$asco_copy.btn_accept|escape:'html':'UTF-8'}
        </button>
      </div>
    </div>

    {* ---- Preferences view ---- *}
    <div class="asco-prefs asco-hidden" data-asco-view="prefs">
      <h2 class="asco-title">{if $asco_lang == 'pl'}Twoje preferencje cookies{else}Your cookie preferences{/if}</h2>

      {foreach from=$asco_categories item=cat}
        <div class="asco-prefs-cat" data-asco-cat="{$cat.slug|escape:'html':'UTF-8'}">
          <label class="asco-prefs-head">
            <input type="checkbox"
                   data-asco-cat-toggle="{$cat.slug|escape:'html':'UTF-8'}"
                   {if $cat.is_necessary}checked disabled{/if}>
            <strong>{$cat.name|escape:'html':'UTF-8'}</strong>
            {if $cat.is_necessary}
              <span class="asco-locked">{if $asco_lang == 'pl'}(zawsze aktywne){else}(always active){/if}</span>
            {/if}
          </label>
          <p class="asco-cat-desc">{$cat.description|escape:'html':'UTF-8'}</p>

          {if $cat.entries|@count}
            <details class="asco-cookie-list">
              <summary>{if $asco_lang == 'pl'}Lista cookies{else}Cookie list{/if} ({$cat.entries|@count})</summary>
              <table>
                <thead><tr>
                  <th>{if $asco_lang == 'pl'}Nazwa{else}Name{/if}</th>
                  <th>{if $asco_lang == 'pl'}Dostawca{else}Provider{/if}</th>
                  <th>{if $asco_lang == 'pl'}Cel{else}Purpose{/if}</th>
                  <th>{if $asco_lang == 'pl'}Ważność{else}Expires{/if}</th>
                </tr></thead>
                <tbody>
                  {foreach from=$cat.entries item=e}
                  <tr>
                    <td><code>{$e.cookie_name|escape:'html':'UTF-8'}</code></td>
                    <td>{$e.provider|escape:'html':'UTF-8'}</td>
                    <td>{$e.purpose|escape:'html':'UTF-8'}</td>
                    <td>{$e.expiration|escape:'html':'UTF-8'}</td>
                  </tr>
                  {/foreach}
                </tbody>
              </table>
            </details>
          {/if}
        </div>
      {/foreach}

      <div class="asco-actions">
        <button type="button" class="asco-btn asco-btn-reject asco-btn-secondary" data-asco-action="reject_all">
          {$asco_copy.btn_reject|escape:'html':'UTF-8'}
        </button>
        <button type="button" class="asco-btn asco-btn-save asco-btn-primary" data-asco-action="save_prefs">
          {$asco_copy.btn_save|escape:'html':'UTF-8'}
        </button>
        <button type="button" class="asco-btn asco-btn-accept asco-btn-secondary" data-asco-action="accept_all">
          {$asco_copy.btn_accept|escape:'html':'UTF-8'}
        </button>
      </div>
    </div>

  </div>
</div>
