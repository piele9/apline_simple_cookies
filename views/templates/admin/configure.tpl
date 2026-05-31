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

  {* ---- Custom Scripts placeholder — populated in CP05 ---- *}
  {if isset($asco_custom_scripts_section)}{$asco_custom_scripts_section nofilter}{/if}

  {* ---- Cookie policy ---- *}
  <div class="panel">
    <h3><i class="icon-file-text-o"></i> {l s='Cookie policy page' d='Modules.Aplinesimplecookies.Admin'}</h3>
    <div class="form-group">
      <label>{l s='Cookie policy URL' d='Modules.Aplinesimplecookies.Admin'}</label>
      <input type="text" name="ASCO_POLICY_URL" class="form-control" value="{$asco_conf.ASCO_POLICY_URL|escape:'html':'UTF-8'}" placeholder="/content/8-polityka-cookies">
      <p class="help-block">{l s='URL to your CMS page with the full cookie policy.' d='Modules.Aplinesimplecookies.Admin'}</p>
      {if isset($asco_policy_template_button)}{$asco_policy_template_button nofilter}{/if}
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
