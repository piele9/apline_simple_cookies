{*
 * APLINE Simple Cookies module for PrestaShop 9.
 * @author APLINE Arkadiusz Pielechowski
 *}
<div class="panel">
  <h3><i class="icon-check-square-o"></i> {l s='Setup checklist' d='Modules.Aplinesimplecookies.Admin'}</h3>
  <ol style="margin-left:18px;">
    <li>{l s='Edit cookie categories (4 seeded: necessary / functional / analytics / marketing).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Edit the individual cookies in each category.' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Add your tracking IDs (Google Analytics, Tag Manager, Facebook Pixel, Hotjar).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Configure the banner appearance and copy (PL + EN).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Create your cookie policy CMS page (use the provided template).' d='Modules.Aplinesimplecookies.Admin'}</li>
    <li>{l s='Test the banner in an incognito window.' d='Modules.Aplinesimplecookies.Admin'}</li>
  </ol>
  <a class="btn btn-default" href="{$asco_category_url|escape:'html':'UTF-8'}"><i class="icon-folder"></i> {l s='Manage cookie categories' d='Modules.Aplinesimplecookies.Admin'}</a>
  <a class="btn btn-default" href="{$asco_entry_url|escape:'html':'UTF-8'}"><i class="icon-list"></i> {l s='Manage individual cookies' d='Modules.Aplinesimplecookies.Admin'}</a>
</div>

<form id="asco-config-form" method="post" action="{$asco_form_action|escape:'html':'UTF-8'}">

  {* ---- Banner appearance & behavior ---- *}
  <div class="panel">
    <h3><i class="icon-cogs"></i> {l s='Banner appearance & behavior' d='Modules.Aplinesimplecookies.Admin'}</h3>

    <div class="form-group">
      <label>{l s='Default language' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_DEFAULT_LANG" class="form-control fixed-width-lg">
        <option value="pl" {if $asco_conf.ASCO_DEFAULT_LANG == 'pl'}selected{/if}>Polski (PL)</option>
        <option value="en" {if $asco_conf.ASCO_DEFAULT_LANG == 'en'}selected{/if}>English (EN)</option>
      </select>
      <p class="help-block">{l s='Used when the shop language is neither PL nor EN.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Banner position' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_BANNER_POSITION" class="form-control fixed-width-lg">
        <option value="bottom" {if $asco_conf.ASCO_BANNER_POSITION == 'bottom'}selected{/if}>{l s='Bottom bar' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="center_modal" {if $asco_conf.ASCO_BANNER_POSITION == 'center_modal'}selected{/if}>{l s='Center modal' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
    </div>

    <div class="form-group">
      <label>{l s='Style' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_BANNER_STYLE" class="form-control fixed-width-lg">
        <option value="light" {if $asco_conf.ASCO_BANNER_STYLE == 'light'}selected{/if}>{l s='Light' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="dark" {if $asco_conf.ASCO_BANNER_STYLE == 'dark'}selected{/if}>{l s='Dark' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
    </div>

    <div class="form-group">
      <label>{l s='Primary (highlighted) button' d='Modules.Aplinesimplecookies.Admin'}</label>
      <select name="ASCO_PRIMARY_BUTTON" class="form-control fixed-width-lg">
        <option value="accept_all" {if $asco_conf.ASCO_PRIMARY_BUTTON == 'accept_all'}selected{/if}>{l s='Accept all' d='Modules.Aplinesimplecookies.Admin'}</option>
        <option value="save_choices" {if $asco_conf.ASCO_PRIMARY_BUTTON == 'save_choices'}selected{/if}>{l s='Save choices' d='Modules.Aplinesimplecookies.Admin'}</option>
      </select>
      <p class="help-block">{l s='Reject and Accept always keep equal size and prominence (CNIL requirement); only the highlight color follows this setting.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label>{l s='Re-prompt period (days)' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="number" name="ASCO_REPROMPT_DAYS" class="form-control fixed-width-sm" min="30" max="730" value="{$asco_conf.ASCO_REPROMPT_DAYS|escape:'html':'UTF-8'}">
      <p class="help-block">{l s='Recommended: 365. Below 180 may be flagged as a dark pattern by UODO/CNIL.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>

    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_RESPECT_GPC" value="1" {if $asco_conf.ASCO_RESPECT_GPC}checked{/if}>
        {l s='Honor the GPC (Global Privacy Control) browser signal' d='Modules.Aplinesimplecookies.Admin'}
      </label>
      <p class="help-block">{l s='Auto-reject non-essential cookies when the browser sends Sec-GPC: 1. Recommended by UODO.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
  </div>

  {* ---- Banner copy (PL + EN) ---- *}
  <div class="panel">
    <h3><i class="icon-edit"></i> {l s='Banner copy (PL + EN)' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <table class="table">
      <thead>
        <tr><th style="width:18%;"></th><th>Polski (PL)</th><th>English (EN)</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>{l s='Title' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_TITLE_PL" value="{$asco_conf.ASCO_COPY_TITLE_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_TITLE_EN" value="{$asco_conf.ASCO_COPY_TITLE_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Body' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><textarea class="form-control" rows="4" name="ASCO_COPY_BODY_PL">{$asco_conf.ASCO_COPY_BODY_PL|escape:'html':'UTF-8'}</textarea></td>
          <td><textarea class="form-control" rows="4" name="ASCO_COPY_BODY_EN">{$asco_conf.ASCO_COPY_BODY_EN|escape:'html':'UTF-8'}</textarea></td>
        </tr>
        <tr>
          <td><strong>{l s='Accept button' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_ACCEPT_PL" value="{$asco_conf.ASCO_COPY_BTN_ACCEPT_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_ACCEPT_EN" value="{$asco_conf.ASCO_COPY_BTN_ACCEPT_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Reject button' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_REJECT_PL" value="{$asco_conf.ASCO_COPY_BTN_REJECT_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_REJECT_EN" value="{$asco_conf.ASCO_COPY_BTN_REJECT_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Preferences button' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_PREFS_PL" value="{$asco_conf.ASCO_COPY_BTN_PREFS_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_PREFS_EN" value="{$asco_conf.ASCO_COPY_BTN_PREFS_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Save button' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_SAVE_PL" value="{$asco_conf.ASCO_COPY_BTN_SAVE_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_BTN_SAVE_EN" value="{$asco_conf.ASCO_COPY_BTN_SAVE_EN|escape:'html':'UTF-8'}"></td>
        </tr>
        <tr>
          <td><strong>{l s='Footer link' d='Modules.Aplinesimplecookies.Admin'}</strong></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_FOOTER_LINK_PL" value="{$asco_conf.ASCO_COPY_FOOTER_LINK_PL|escape:'html':'UTF-8'}"></td>
          <td><input type="text" class="form-control" name="ASCO_COPY_FOOTER_LINK_EN" value="{$asco_conf.ASCO_COPY_FOOTER_LINK_EN|escape:'html':'UTF-8'}"></td>
        </tr>
      </tbody>
    </table>
  </div>

  {* ---- Third-party scripts (Custom Scripts) ---- *}
  <div class="panel">
    <h3><i class="icon-plug"></i> {l s='Third-party scripts (trackers)' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Enter only the ID for each service — the module generates the script and loads it only after the visitor consents to the chosen category. Not sure where to find an ID? Click "How to add this".' d='Modules.Aplinesimplecookies.Admin'}</p>

    {* Google Analytics 4 *}
    <div class="form-group">
      <label>{l s='Google Analytics 4 — Measurement ID' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="ga4"><i class="icon-question-circle"></i> {l s='How to add this' d='Modules.Aplinesimplecookies.Admin'}</a>
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
      <label>{l s='Google Tag Manager — Container ID' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="gtm"><i class="icon-question-circle"></i> {l s='How to add this' d='Modules.Aplinesimplecookies.Admin'}</a>
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
      <label>{l s='Facebook (Meta) Pixel — Pixel ID' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="fb"><i class="icon-question-circle"></i> {l s='How to add this' d='Modules.Aplinesimplecookies.Admin'}</a>
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
      <label>{l s='Hotjar — Site ID' d='Modules.Aplinesimplecookies.Admin'}
        <a href="#" class="asco-help-link" data-asco-help="hotjar"><i class="icon-question-circle"></i> {l s='How to add this' d='Modules.Aplinesimplecookies.Admin'}</a>
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
    <p class="help-block"><strong>{l s='Custom snippets' d='Modules.Aplinesimplecookies.Admin'}</strong> &mdash;
      {l s='For anything that is not in the list above (e.g. LinkedIn Insight Tag, Pinterest Tag). Paste plain HTML/JS; it is injected into the page only after the visitor consents to that category.' d='Modules.Aplinesimplecookies.Admin'}
    </p>
    <div class="alert alert-warning">{l s='Anything you paste here will run on the front-end after the user consents to this category. Only paste code you trust.' d='Modules.Aplinesimplecookies.Admin'}</div>

    <div class="form-group">
      <label>{l s='Custom HTML/JS — Analytics consent' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_ANALYTICS">{$asco_conf.ASCO_CUSTOM_HEAD_ANALYTICS|escape:'html':'UTF-8'}</textarea>
    </div>
    <div class="form-group">
      <label>{l s='Custom HTML/JS — Marketing consent' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_MARKETING">{$asco_conf.ASCO_CUSTOM_HEAD_MARKETING|escape:'html':'UTF-8'}</textarea>
    </div>
    <div class="form-group">
      <label>{l s='Custom HTML/JS — Functional consent' d='Modules.Aplinesimplecookies.Admin'}</label>
      <textarea class="form-control" rows="3" name="ASCO_CUSTOM_HEAD_FUNCTIONAL">{$asco_conf.ASCO_CUSTOM_HEAD_FUNCTIONAL|escape:'html':'UTF-8'}</textarea>
    </div>
  </div>

  {* ---- Cookie policy ---- *}
  <div class="panel">
    <h3><i class="icon-file-text-o"></i> {l s='Cookie policy page' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <div class="form-group">
      <label>{l s='Cookie policy URL' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="text" name="ASCO_POLICY_URL" class="form-control" value="{$asco_conf.ASCO_POLICY_URL|escape:'html':'UTF-8'}" placeholder="/content/8-polityka-cookies">
      <p class="help-block">{l s='URL to your CMS page with the full cookie policy.' d='Modules.Aplinesimplecookies.Admin'}</p>
      <button type="button" class="btn btn-default" id="asco-show-policy"><i class="icon-clipboard"></i> {l s='Show example template' d='Modules.Aplinesimplecookies.Admin'}</button>
    </div>
    <div class="form-group">
      <label>{l s='Policy version' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="text" name="ASCO_POLICY_VERSION" class="form-control fixed-width-sm" value="{$asco_conf.ASCO_POLICY_VERSION|escape:'html':'UTF-8'}">
      <p class="help-block">{l s='Bump this (e.g. 1.0.1) when you change your policy. All visitors will see the banner again on their next visit.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
  </div>

  {* ---- Audit log ---- *}
  <div class="panel">
    <h3><i class="icon-archive"></i> {l s='Consent audit log' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_LOG_CONSENTS" value="1" {if $asco_conf.ASCO_LOG_CONSENTS}checked{/if}>
        {l s='Log consent decisions to the database' d='Modules.Aplinesimplecookies.Admin'}
      </label>
    </div>
    <div class="form-group">
      <label class="control-label">
        <input type="checkbox" name="ASCO_LOG_IP" value="1" {if $asco_conf.ASCO_LOG_IP}checked{/if}>
        {l s='Hash and log the IP address' d='Modules.Aplinesimplecookies.Admin'}
      </label>
      <p class="help-block">{l s='Stores a SHA256 hash of the IP plus a per-install salt. Cannot be reversed to the original IP.' d='Modules.Aplinesimplecookies.Admin'}</p>
    </div>
    <div class="form-group">
      <label>{l s='Auto-delete logs older than (days)' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="number" name="ASCO_LOG_RETENTION_DAYS" class="form-control fixed-width-sm" min="30" max="3650" value="{$asco_conf.ASCO_LOG_RETENTION_DAYS|escape:'html':'UTF-8'}">
    </div>

    <div class="alert alert-info">
      <strong>{l s='Statistics (last 30 days)' d='Modules.Aplinesimplecookies.Admin'}</strong>
      <ul style="margin:8px 0 0 18px;">
        <li>{l s='Total decisions' d='Modules.Aplinesimplecookies.Admin'}: <strong>{$asco_stats.total|intval}</strong></li>
        <li>{l s='From the banner' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.banner|intval}</li>
        <li>{l s='From preferences' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.prefs|intval}</li>
        <li>{l s='From the footer link' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.footer_link|intval}</li>
        <li>{l s='Auto-rejected via GPC' d='Modules.Aplinesimplecookies.Admin'}: {$asco_stats.by_source.gpc|intval}</li>
      </ul>
    </div>

    <a class="btn btn-default" href="{$asco_export_url|escape:'html':'UTF-8'}"><i class="icon-download"></i> {l s='Export consent log (CSV)' d='Modules.Aplinesimplecookies.Admin'}</a>
  </div>

  <div class="panel">
    <button type="submit" name="{$asco_submit_token|escape:'html':'UTF-8'}" class="btn btn-primary pull-right">
      <i class="icon-save"></i> {l s='Save' d='Admin.Actions'}
    </button>
    <div class="clearfix"></div>
  </div>

</form>

{* ============================ Help modals ============================ *}
<div class="asco-modal" id="asco-modal-ga4">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close>&times;</button>
    <h3>Google Analytics 4 (GA4)</h3>
    <h4>PL — Jak znaleźć i wkleić Identyfikator pomiaru</h4>
    <ol>
      <li>Wejdź na <code>https://analytics.google.com</code> i zaloguj się.</li>
      <li>Kliknij <strong>Administracja</strong> (koło zębate) → <strong>Strumienie danych</strong> → wybierz strumień typu <em>Web</em>.</li>
      <li>Skopiuj <strong>Identyfikator pomiaru</strong> — wygląda tak: <code>G-XXXXXXXXXX</code>.</li>
      <li>Wklej go w pole <strong>Google Analytics 4 — Measurement ID</strong> i wybierz kategorię zgody (domyślnie <code>analytics</code>).</li>
      <li>Zapisz. GA4 załaduje się dopiero, gdy odwiedzający zaakceptuje tę kategorię.</li>
    </ol>
    <h4>EN — How to find and paste the Measurement ID</h4>
    <ol>
      <li>Go to <code>https://analytics.google.com</code> and sign in.</li>
      <li>Click <strong>Admin</strong> (gear) → <strong>Data Streams</strong> → pick your <em>Web</em> stream.</li>
      <li>Copy the <strong>Measurement ID</strong> — it looks like <code>G-XXXXXXXXXX</code>.</li>
      <li>Paste it into the <strong>Google Analytics 4</strong> field and choose a consent category (default <code>analytics</code>).</li>
      <li>Save. GA4 loads only after the visitor consents to that category.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-gtm">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close>&times;</button>
    <h3>Google Tag Manager (GTM)</h3>
    <h4>PL — Jak znaleźć Identyfikator kontenera</h4>
    <ol>
      <li>Wejdź na <code>https://tagmanager.google.com</code> i wybierz konto/kontener.</li>
      <li>U góry, obok nazwy kontenera, znajdziesz identyfikator <code>GTM-XXXXXXX</code>.</li>
      <li>Wklej go w pole <strong>Google Tag Manager</strong>, wybierz kategorię (domyślnie <code>analytics</code>) i zapisz.</li>
    </ol>
    <h4>EN — How to find the Container ID</h4>
    <ol>
      <li>Go to <code>https://tagmanager.google.com</code> and select your account/container.</li>
      <li>The container ID <code>GTM-XXXXXXX</code> is shown next to the container name.</li>
      <li>Paste it into the <strong>Google Tag Manager</strong> field, pick a category and save.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-fb">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close>&times;</button>
    <h3>Facebook (Meta) Pixel</h3>
    <h4>PL — Jak znaleźć Identyfikator pixela</h4>
    <ol>
      <li>Wejdź na <code>https://business.facebook.com</code> → <strong>Menedżer zdarzeń</strong> (Events Manager).</li>
      <li>Wybierz swój Pixel → <strong>Ustawienia</strong>. Identyfikator pixela to ciąg 15–16 cyfr.</li>
      <li>Wklej go w pole <strong>Facebook (Meta) Pixel</strong> (domyślna kategoria <code>marketing</code>) i zapisz.</li>
    </ol>
    <h4>EN — How to find the Pixel ID</h4>
    <ol>
      <li>Go to <code>https://business.facebook.com</code> → <strong>Events Manager</strong>.</li>
      <li>Select your Pixel → <strong>Settings</strong>. The Pixel ID is a 15–16 digit number.</li>
      <li>Paste it into the <strong>Facebook (Meta) Pixel</strong> field (default category <code>marketing</code>) and save.</li>
    </ol>
  </div>
</div>

<div class="asco-modal" id="asco-modal-hotjar">
  <div class="asco-modal-box">
    <button type="button" class="asco-modal-close" data-asco-close>&times;</button>
    <h3>Hotjar</h3>
    <h4>PL — Jak znaleźć Site ID</h4>
    <ol>
      <li>Wejdź na <code>https://insights.hotjar.com</code> → <strong>Settings</strong> → <strong>Sites &amp; Organizations</strong>.</li>
      <li><strong>Site ID</strong> to liczba (np. <code>1234567</code>).</li>
      <li>Wklej ją w pole <strong>Hotjar</strong> i zapisz.</li>
    </ol>
    <h4>EN — How to find the Site ID</h4>
    <ol>
      <li>Go to <code>https://insights.hotjar.com</code> → <strong>Settings</strong> → <strong>Sites &amp; Organizations</strong>.</li>
      <li>The <strong>Site ID</strong> is a number (e.g. <code>1234567</code>).</li>
      <li>Paste it into the <strong>Hotjar</strong> field and save.</li>
    </ol>
  </div>
</div>

{* ====================== Cookie policy template ====================== *}
<div class="asco-modal" id="asco-modal-policy">
  <div class="asco-modal-box asco-modal-lg">
    <button type="button" class="asco-modal-close" data-asco-close>&times;</button>
    <h3>{l s='Cookie policy — example template' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <p class="help-block">{l s='Copy this into a new CMS page (Design → Pages), fill in the placeholders in brackets, then paste the page URL into the field above.' d='Modules.Aplinesimplecookies.Admin'}</p>
    <ul class="nav nav-tabs asco-tabs">
      <li class="active"><a href="#" data-asco-tab="pl">Polski (PL)</a></li>
      <li><a href="#" data-asco-tab="en">English (EN)</a></li>
    </ul>
    <div class="asco-tabpane" data-asco-tabpane="pl">
      <button type="button" class="btn btn-default asco-copy-btn" data-asco-copy="asco-policy-pl"><i class="icon-copy"></i> {l s='Copy to clipboard' d='Modules.Aplinesimplecookies.Admin'}</button>
      <textarea id="asco-policy-pl" class="form-control asco-policy-text" readonly rows="18">{include file="./policy-template-pl.tpl"}</textarea>
    </div>
    <div class="asco-tabpane" data-asco-tabpane="en" style="display:none;">
      <button type="button" class="btn btn-default asco-copy-btn" data-asco-copy="asco-policy-en"><i class="icon-copy"></i> {l s='Copy to clipboard' d='Modules.Aplinesimplecookies.Admin'}</button>
      <textarea id="asco-policy-en" class="form-control asco-policy-text" readonly rows="18">{include file="./policy-template-en.tpl"}</textarea>
    </div>
  </div>
</div>

<link rel="stylesheet" href="{$asco_module_uri|escape:'html':'UTF-8'}views/css/admin.css">
<script src="{$asco_module_uri|escape:'html':'UTF-8'}views/js/admin.js"></script>
