<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * A GDPR / UODO / ePrivacy compliant cookie consent banner. Bilingual
 * front-end (PL + EN, PL default), category-based consent, third-party
 * tracker gating (Google Analytics 4, Google Tag Manager, Facebook Pixel,
 * Hotjar), and a consent audit log for accountability.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/AplineSimpleCookiesCategory.php';
require_once __DIR__ . '/classes/AplineSimpleCookiesEntry.php';

class apline_simple_cookies extends Module
{
    const TABLE_CATEGORY = 'asco_category';
    const TABLE_ENTRY = 'asco_entry';
    const TABLE_LOG = 'asco_consent_log';

    const ADMIN_CATEGORY = 'AdminAplineSimpleCookiesCategory';
    const ADMIN_ENTRY = 'AdminAplineSimpleCookiesEntry';

    const SUBMIT_TOKEN = 'submitAscoConfig';

    /**
     * Hooks the module registers on install. Banner is global (compliance
     * requires it everywhere) so there is no configurable display hook.
     *
     * @var string[]
     */
    const HOOKS = [
        'actionFrontControllerSetMedia',
        'displayHeader',
        'displayBeforeBodyClosingTag',
        'displayFooterAfter',
    ];

    public function __construct()
    {
        $this->name = 'apline_simple_cookies';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'APLINE Arkadiusz Pielechowski';
        $this->need_instance = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('APLINE Simple Cookies for PrestaShop 9', [], 'Modules.Aplinesimplecookies.Admin');
        $this->description = $this->trans('GDPR/UODO compliant cookie consent banner (PL+EN) with category consent, third-party tracker blocking and a consent audit log.', [], 'Modules.Aplinesimplecookies.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module? All cookie categories, entries and the consent log will be deleted.', [], 'Modules.Aplinesimplecookies.Admin');

        $this->ps_versions_compliancy = ['min' => '9.0', 'max' => _PS_VERSION_];
    }

    /* --------------------------------------------------------------------- */
    /* Install / uninstall                                                   */
    /* --------------------------------------------------------------------- */

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->installDb()
            || !$this->installConfiguration()
            || !$this->installHooks()
            || !$this->installTabs()
            || !$this->seedDefaults()
        ) {
            // Roll back to a clean state so the shop is never left half-installed.
            $this->uninstall();
            $this->_errors[] = $this->trans('Installation failed and was rolled back. Please check database permissions and try again.', [], 'Modules.Aplinesimplecookies.Admin');

            return false;
        }

        return true;
    }

    public function uninstall()
    {
        // Each step is idempotent; uninstall must never fail because something
        // is already gone (safe to run twice).
        $this->uninstallTabs();

        $db = Db::getInstance();
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE_ENTRY . '`');
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE_CATEGORY . '`');
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE_LOG . '`');

        // Delete every ASCO_* configuration key (loop, not a hardcoded subset,
        // so a forgotten key never lingers across versions).
        foreach (array_keys($this->getConfigDefaults()) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    /**
     * @return bool
     */
    private function installDb()
    {
        $engine = _MYSQL_ENGINE_;
        $prefix = _DB_PREFIX_;

        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $prefix . self::TABLE_CATEGORY . '` (
            `id_asco_category` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(64) NOT NULL,
            `name_pl` VARCHAR(255) NOT NULL,
            `name_en` VARCHAR(255) NOT NULL,
            `description_pl` TEXT NOT NULL,
            `description_en` TEXT NOT NULL,
            `is_necessary` TINYINT(1) NOT NULL DEFAULT 0,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_asco_category`),
            UNIQUE KEY `uq_slug` (`slug`),
            KEY `idx_active_position` (`active`, `position`)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $prefix . self::TABLE_ENTRY . '` (
            `id_asco_entry` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_asco_category` INT UNSIGNED NOT NULL,
            `cookie_name` VARCHAR(255) NOT NULL,
            `provider` VARCHAR(255) NOT NULL,
            `purpose_pl` TEXT NOT NULL,
            `purpose_en` TEXT NOT NULL,
            `expiration` VARCHAR(64) NOT NULL,
            `domain` VARCHAR(255) DEFAULT NULL,
            `position` INT UNSIGNED NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_asco_entry`),
            KEY `idx_category` (`id_asco_category`, `position`)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;';

        // `decision` stored as TEXT (JSON content) and `source` as VARCHAR for
        // maximum portability across MySQL/MariaDB versions on shared hosting.
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $prefix . self::TABLE_LOG . '` (
            `id_asco_consent_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `visitor_token` VARCHAR(64) NOT NULL,
            `id_customer` INT UNSIGNED DEFAULT NULL,
            `decision` TEXT NOT NULL,
            `policy_version` VARCHAR(32) NOT NULL,
            `ip_hash` VARCHAR(64) DEFAULT NULL,
            `user_agent` VARCHAR(512) DEFAULT NULL,
            `language` VARCHAR(8) NOT NULL,
            `source` VARCHAR(16) NOT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_asco_consent_log`),
            KEY `idx_visitor` (`visitor_token`),
            KEY `idx_customer` (`id_customer`),
            KEY `idx_date` (`date_add`)
        ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4;';

        $db = Db::getInstance();
        foreach ($sql as $query) {
            if (!$db->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function installConfiguration()
    {
        foreach ($this->getConfigDefaults() as $key => $default) {
            // The IP-hash salt is generated once per install and never changed.
            if ($key === 'ASCO_LOG_IP_SALT') {
                $default = $this->generateSalt();
            }
            if (!Configuration::updateValue($key, $default)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function installHooks()
    {
        $ok = true;
        foreach (self::HOOKS as $hook) {
            $ok = $ok && $this->registerHook($hook);
        }

        return $ok;
    }

    /**
     * Register the two hidden admin tabs (Category + Entry controllers).
     * Managed from the module configuration page (id_parent = -1).
     *
     * @return bool
     */
    private function installTabs()
    {
        $tabs = [
            self::ADMIN_CATEGORY => 'Cookie Categories',
            self::ADMIN_ENTRY => 'Cookies',
        ];

        foreach ($tabs as $className => $label) {
            if (Tab::getIdFromClassName($className)) {
                continue;
            }
            $tab = new Tab();
            $tab->class_name = $className;
            $tab->module = $this->name;
            $tab->active = 1;
            $tab->id_parent = -1;
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[$lang['id_lang']] = $label;
            }
            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function uninstallTabs()
    {
        foreach ([self::ADMIN_CATEGORY, self::ADMIN_ENTRY] as $className) {
            $id = (int) Tab::getIdFromClassName($className);
            if (!$id) {
                continue;
            }
            try {
                $tab = new Tab($id);
                $tab->delete();
            } catch (\Throwable $e) {
                // Already gone — ignore so uninstall stays idempotent.
            }
        }

        return true;
    }

    /* --------------------------------------------------------------------- */
    /* Seed data                                                             */
    /* --------------------------------------------------------------------- */

    /**
     * Seed the default 4 categories and a handful of example cookie entries
     * so the admin sees a working banner immediately and learns the format.
     *
     * @return bool
     */
    private function seedDefaults()
    {
        $db = Db::getInstance();
        $now = date('Y-m-d H:i:s');

        $categoryIds = [];
        $position = 1;
        foreach ($this->getSeedCategories() as $cat) {
            $ok = $db->insert(self::TABLE_CATEGORY, [
                'slug' => pSQL($cat['slug']),
                'name_pl' => pSQL($cat['name_pl']),
                'name_en' => pSQL($cat['name_en']),
                'description_pl' => pSQL($cat['description_pl']),
                'description_en' => pSQL($cat['description_en']),
                'is_necessary' => (int) $cat['is_necessary'],
                'active' => 1,
                'position' => $position++,
                'date_add' => $now,
                'date_upd' => $now,
            ]);
            if (!$ok) {
                return false;
            }
            $categoryIds[$cat['slug']] = (int) $db->Insert_ID();
        }

        $position = 1;
        foreach ($this->getSeedEntries() as $entry) {
            if (!isset($categoryIds[$entry['category']])) {
                continue;
            }
            $ok = $db->insert(self::TABLE_ENTRY, [
                'id_asco_category' => (int) $categoryIds[$entry['category']],
                'cookie_name' => pSQL($entry['cookie_name']),
                'provider' => pSQL($entry['provider']),
                'purpose_pl' => pSQL($entry['purpose_pl']),
                'purpose_en' => pSQL($entry['purpose_en']),
                'expiration' => pSQL($entry['expiration']),
                'domain' => $entry['domain'] !== null ? pSQL($entry['domain']) : null,
                'position' => $position++,
                'date_add' => $now,
                'date_upd' => $now,
            ]);
            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function getSeedCategories()
    {
        return [
            [
                'slug' => 'necessary',
                'name_pl' => 'Niezbędne',
                'name_en' => 'Necessary',
                'description_pl' => 'Cookies wymagane do podstawowego działania sklepu. Bez nich strona nie funkcjonuje prawidłowo (np. utrzymanie zalogowanej sesji, zawartość koszyka). Nie można ich wyłączyć.',
                'description_en' => 'Cookies required for basic shop functionality. The site cannot work properly without them (e.g. logged-in session, cart contents). Cannot be disabled.',
                'is_necessary' => 1,
            ],
            [
                'slug' => 'functional',
                'name_pl' => 'Funkcjonalne',
                'name_en' => 'Functional',
                'description_pl' => 'Cookies pamiętające Twoje preferencje (np. wybrany język, ostatnio oglądane produkty). Ich wyłączenie nie zepsuje sklepu, ale stracisz część wygody.',
                'description_en' => 'Cookies that remember your preferences (e.g. selected language, recently viewed products). Disabling them won\'t break the shop, but you\'ll lose some convenience.',
                'is_necessary' => 0,
            ],
            [
                'slug' => 'analytics',
                'name_pl' => 'Analityczne',
                'name_en' => 'Analytics',
                'description_pl' => 'Cookies analizujące ruch na stronie (np. Google Analytics). Pomagają nam ulepszać sklep. Nie zawierają danych identyfikujących Cię osobiście.',
                'description_en' => 'Cookies that analyze traffic on the site (e.g. Google Analytics). Help us improve the shop. Don\'t contain personally identifying data.',
                'is_necessary' => 0,
            ],
            [
                'slug' => 'marketing',
                'name_pl' => 'Marketingowe',
                'name_en' => 'Marketing',
                'description_pl' => 'Cookies śledzące w celu pokazywania dopasowanych reklam (np. Facebook Pixel, Google Ads). Pozwalają reklamodawcom mierzyć skuteczność kampanii.',
                'description_en' => 'Tracking cookies for personalized ads (e.g. Facebook Pixel, Google Ads). Allow advertisers to measure campaign effectiveness.',
                'is_necessary' => 0,
            ],
        ];
    }

    /**
     * @return array
     */
    private function getSeedEntries()
    {
        return [
            // Necessary
            ['category' => 'necessary', 'cookie_name' => 'PHPSESSID', 'provider' => 'PrestaShop', 'purpose_pl' => 'Identyfikator sesji serwera, niezbędny do działania sklepu.', 'purpose_en' => 'Server session identifier, required for the shop to work.', 'expiration' => 'Sesja / Session', 'domain' => null],
            ['category' => 'necessary', 'cookie_name' => 'PrestaShop-*', 'provider' => 'PrestaShop', 'purpose_pl' => 'Utrzymanie zalogowania i zawartości koszyka.', 'purpose_en' => 'Keeps the logged-in session and cart contents.', 'expiration' => '20 dni / 20 days', 'domain' => null],
            ['category' => 'necessary', 'cookie_name' => 'asco_consent', 'provider' => 'APLINE Simple Cookies', 'purpose_pl' => 'Zapisuje Twoją decyzję dotyczącą cookies.', 'purpose_en' => 'Stores your cookie consent decision.', 'expiration' => '365 dni / 365 days', 'domain' => null],
            ['category' => 'necessary', 'cookie_name' => 'asco_visitor', 'provider' => 'APLINE Simple Cookies', 'purpose_pl' => 'Anonimowy token wizyty do powiązania decyzji zgody.', 'purpose_en' => 'Anonymous visitor token to link consent decisions.', 'expiration' => '365 dni / 365 days', 'domain' => null],
            // Analytics (placeholders — admin adjusts to their own setup)
            ['category' => 'analytics', 'cookie_name' => '_ga', 'provider' => 'Google LLC', 'purpose_pl' => 'Rozróżnianie użytkowników w Google Analytics.', 'purpose_en' => 'Distinguishes users in Google Analytics.', 'expiration' => '2 lata / 2 years', 'domain' => '.google-analytics.com'],
            ['category' => 'analytics', 'cookie_name' => '_gid', 'provider' => 'Google LLC', 'purpose_pl' => 'Rozróżnianie użytkowników (24h).', 'purpose_en' => 'Distinguishes users (24h).', 'expiration' => '24 godziny / 24 hours', 'domain' => '.google-analytics.com'],
            ['category' => 'analytics', 'cookie_name' => '_gat', 'provider' => 'Google LLC', 'purpose_pl' => 'Ograniczanie liczby żądań do Google Analytics.', 'purpose_en' => 'Throttles the request rate to Google Analytics.', 'expiration' => '1 minuta / 1 minute', 'domain' => '.google-analytics.com'],
            // Marketing (placeholder)
            ['category' => 'marketing', 'cookie_name' => '_fbp', 'provider' => 'Meta Platforms', 'purpose_pl' => 'Facebook Pixel — śledzenie konwersji i remarketing.', 'purpose_en' => 'Facebook Pixel — conversion tracking and remarketing.', 'expiration' => '90 dni / 90 days', 'domain' => '.facebook.com'],
        ];
    }

    /* --------------------------------------------------------------------- */
    /* Configuration defaults                                                */
    /* --------------------------------------------------------------------- */

    /**
     * Every ASCO_* configuration key with its default. Used both to seed the
     * configuration on install and to delete every key on uninstall.
     *
     * @return array
     */
    public function getConfigDefaults()
    {
        return [
            // Banner appearance & behavior
            'ASCO_DEFAULT_LANG' => 'pl',
            'ASCO_BANNER_POSITION' => 'bottom',
            'ASCO_BANNER_STYLE' => 'light',
            'ASCO_PRIMARY_BUTTON' => 'accept_all',
            'ASCO_REPROMPT_DAYS' => 365,
            'ASCO_RESPECT_GPC' => 1,
            // Audit log
            'ASCO_LOG_CONSENTS' => 1,
            'ASCO_LOG_IP' => 1,
            'ASCO_LOG_IP_SALT' => '',
            'ASCO_LOG_RETENTION_DAYS' => 730,
            // Cookie policy
            'ASCO_POLICY_URL' => '',
            'ASCO_POLICY_VERSION' => '1.0.0',
            // Third-party scripts
            'ASCO_GA4_ID' => '',
            'ASCO_GA4_CATEGORY' => 'analytics',
            'ASCO_GTM_ID' => '',
            'ASCO_GTM_CATEGORY' => 'analytics',
            'ASCO_FB_PIXEL_ID' => '',
            'ASCO_FB_PIXEL_CATEGORY' => 'marketing',
            'ASCO_HOTJAR_ID' => '',
            'ASCO_HOTJAR_CATEGORY' => 'analytics',
            'ASCO_CUSTOM_HEAD_ANALYTICS' => '',
            'ASCO_CUSTOM_HEAD_MARKETING' => '',
            'ASCO_CUSTOM_HEAD_FUNCTIONAL' => '',
            // Banner copy (PL + EN, editable in BO)
            'ASCO_COPY_TITLE_PL' => 'Ta strona używa plików cookies',
            'ASCO_COPY_TITLE_EN' => 'This site uses cookies',
            'ASCO_COPY_BODY_PL' => 'Ta strona korzysta z plików cookies, aby zapewnić Ci najlepsze doświadczenie zakupowe. Niektóre cookies są niezbędne do działania sklepu (np. logowanie, koszyk), inne pozwalają nam analizować ruch i pokazywać dopasowane reklamy. Możesz w każdej chwili zmienić swoje preferencje klikając „Ustawienia cookies" w stopce strony.',
            'ASCO_COPY_BODY_EN' => 'This site uses cookies to give you the best shopping experience. Some cookies are essential for the shop to work (e.g. login, cart), others help us analyze traffic and show personalized ads. You can change your preferences anytime by clicking "Cookie settings" in the footer.',
            'ASCO_COPY_BTN_ACCEPT_PL' => 'Akceptuj wszystkie',
            'ASCO_COPY_BTN_ACCEPT_EN' => 'Accept all',
            'ASCO_COPY_BTN_REJECT_PL' => 'Odrzuć wszystkie',
            'ASCO_COPY_BTN_REJECT_EN' => 'Reject all',
            'ASCO_COPY_BTN_PREFS_PL' => 'Preferencje',
            'ASCO_COPY_BTN_PREFS_EN' => 'Preferences',
            'ASCO_COPY_BTN_SAVE_PL' => 'Zapisz wybór',
            'ASCO_COPY_BTN_SAVE_EN' => 'Save choices',
            'ASCO_COPY_FOOTER_LINK_PL' => 'Ustawienia cookies',
            'ASCO_COPY_FOOTER_LINK_EN' => 'Cookie settings',
        ];
    }

    /**
     * Cryptographically-random salt for the consent-log IP hash.
     *
     * @return string 64 hex chars (32 bytes)
     */
    private function generateSalt()
    {
        try {
            return bin2hex(random_bytes(32));
        } catch (\Throwable $e) {
            return hash('sha256', uniqid((string) microtime(true), true));
        }
    }

    /* --------------------------------------------------------------------- */
    /* Configuration page (minimal in CP01, expanded in CP04)                */
    /* --------------------------------------------------------------------- */

    public function getContent()
    {
        // Stream the consent-log CSV and stop before any other output.
        if (Tools::isSubmit('exportLog')) {
            $this->exportConsentLog();
        }

        $output = '';

        if (Tools::isSubmit(self::SUBMIT_TOKEN)) {
            $errors = $this->saveConfiguration();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            } else {
                $output .= $this->displayConfirmation($this->trans('Settings updated.', [], 'Modules.Aplinesimplecookies.Admin'));
            }
        }

        $this->context->smarty->assign($this->getConfigTemplateVars());
        $output .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');

        return $output . $this->renderLikeBox() . $this->renderAplineFooter();
    }

    /**
     * URL of this module's configuration page (used as the form action and as
     * the base for the CSV export link).
     *
     * @param array $extra extra query params
     *
     * @return string
     */
    public function getConfigPageUrl(array $extra = [])
    {
        return $this->context->link->getAdminLink('AdminModules', true, [], array_merge([
            'configure' => $this->name,
            'module_name' => $this->name,
        ], $extra));
    }

    /**
     * Validate and persist the configuration form. Returns an array of error
     * messages (empty on success). Rejects invalid input — never silently
     * coerces a bad value.
     *
     * @return string[]
     */
    protected function saveConfiguration()
    {
        $errors = [];
        $d = 'Modules.Aplinesimplecookies.Admin';

        // --- Appearance & behavior ---
        $lang = (string) Tools::getValue('ASCO_DEFAULT_LANG');
        if (!in_array($lang, ['pl', 'en'], true)) {
            $errors[] = $this->trans('Invalid default language.', [], $d);
        }
        $position = (string) Tools::getValue('ASCO_BANNER_POSITION');
        if (!in_array($position, ['bottom', 'center_modal'], true)) {
            $errors[] = $this->trans('Invalid banner position.', [], $d);
        }
        $style = (string) Tools::getValue('ASCO_BANNER_STYLE');
        if (!in_array($style, ['light', 'dark'], true)) {
            $errors[] = $this->trans('Invalid banner style.', [], $d);
        }
        $primary = (string) Tools::getValue('ASCO_PRIMARY_BUTTON');
        if (!in_array($primary, ['accept_all', 'save_choices'], true)) {
            $errors[] = $this->trans('Invalid primary button.', [], $d);
        }
        $reprompt = (int) Tools::getValue('ASCO_REPROMPT_DAYS');
        if ($reprompt < 30 || $reprompt > 730) {
            $errors[] = $this->trans('Re-prompt period must be between 30 and 730 days.', [], $d);
        }
        $gpc = Tools::getValue('ASCO_RESPECT_GPC') ? 1 : 0;

        // --- Audit log ---
        $logConsents = Tools::getValue('ASCO_LOG_CONSENTS') ? 1 : 0;
        $logIp = Tools::getValue('ASCO_LOG_IP') ? 1 : 0;
        $retention = (int) Tools::getValue('ASCO_LOG_RETENTION_DAYS');
        if ($retention < 30 || $retention > 3650) {
            $errors[] = $this->trans('Log retention must be between 30 and 3650 days.', [], $d);
        }

        // --- Cookie policy ---
        $policyUrl = trim((string) Tools::getValue('ASCO_POLICY_URL'));
        if ($policyUrl !== '' && !Validate::isUrl($policyUrl) && !preg_match('#^/[\w\-/\.]*$#', $policyUrl)) {
            $errors[] = $this->trans('The cookie policy URL is not valid.', [], $d);
        }
        $policyVersion = trim((string) Tools::getValue('ASCO_POLICY_VERSION'));
        if (!preg_match('/^\d+\.\d+\.\d+$/', $policyVersion)) {
            $errors[] = $this->trans('The policy version must look like 1.0.0 (digits and dots).', [], $d);
        }

        // --- Banner copy (PL + EN) ---
        $copyKeys = [
            'ASCO_COPY_TITLE_PL', 'ASCO_COPY_TITLE_EN', 'ASCO_COPY_BODY_PL', 'ASCO_COPY_BODY_EN',
            'ASCO_COPY_BTN_ACCEPT_PL', 'ASCO_COPY_BTN_ACCEPT_EN', 'ASCO_COPY_BTN_REJECT_PL', 'ASCO_COPY_BTN_REJECT_EN',
            'ASCO_COPY_BTN_PREFS_PL', 'ASCO_COPY_BTN_PREFS_EN', 'ASCO_COPY_BTN_SAVE_PL', 'ASCO_COPY_BTN_SAVE_EN',
            'ASCO_COPY_FOOTER_LINK_PL', 'ASCO_COPY_FOOTER_LINK_EN',
        ];

        // Let module-specific extensions (Custom Scripts, CP05) add their own
        // validation/persistence without rewriting this method.
        $errors = array_merge($errors, $this->saveCustomScripts());

        if (!empty($errors)) {
            return $errors;
        }

        Configuration::updateValue('ASCO_DEFAULT_LANG', $lang);
        Configuration::updateValue('ASCO_BANNER_POSITION', $position);
        Configuration::updateValue('ASCO_BANNER_STYLE', $style);
        Configuration::updateValue('ASCO_PRIMARY_BUTTON', $primary);
        Configuration::updateValue('ASCO_REPROMPT_DAYS', $reprompt);
        Configuration::updateValue('ASCO_RESPECT_GPC', $gpc);
        Configuration::updateValue('ASCO_LOG_CONSENTS', $logConsents);
        Configuration::updateValue('ASCO_LOG_IP', $logIp);
        Configuration::updateValue('ASCO_LOG_RETENTION_DAYS', $retention);
        Configuration::updateValue('ASCO_POLICY_URL', $policyUrl);
        Configuration::updateValue('ASCO_POLICY_VERSION', $policyVersion);

        foreach ($copyKeys as $key) {
            // Copy is admin-trusted; store the raw trimmed value (escaped on render).
            Configuration::updateValue($key, trim((string) Tools::getValue($key)), true);
        }

        $this->persistCustomScripts();

        return [];
    }

    /**
     * Custom Scripts section keys (third-party trackers + raw custom HTML).
     * The IDs are normalized (Google IDs uppercased) and trimmed.
     *
     * @return array
     */
    protected function getSubmittedScripts()
    {
        return [
            'ASCO_GA4_ID' => strtoupper(trim((string) Tools::getValue('ASCO_GA4_ID'))),
            'ASCO_GA4_CATEGORY' => (string) Tools::getValue('ASCO_GA4_CATEGORY'),
            'ASCO_GTM_ID' => strtoupper(trim((string) Tools::getValue('ASCO_GTM_ID'))),
            'ASCO_GTM_CATEGORY' => (string) Tools::getValue('ASCO_GTM_CATEGORY'),
            'ASCO_FB_PIXEL_ID' => trim((string) Tools::getValue('ASCO_FB_PIXEL_ID')),
            'ASCO_FB_PIXEL_CATEGORY' => (string) Tools::getValue('ASCO_FB_PIXEL_CATEGORY'),
            'ASCO_HOTJAR_ID' => trim((string) Tools::getValue('ASCO_HOTJAR_ID')),
            'ASCO_HOTJAR_CATEGORY' => (string) Tools::getValue('ASCO_HOTJAR_CATEGORY'),
            'ASCO_CUSTOM_HEAD_ANALYTICS' => (string) Tools::getValue('ASCO_CUSTOM_HEAD_ANALYTICS'),
            'ASCO_CUSTOM_HEAD_MARKETING' => (string) Tools::getValue('ASCO_CUSTOM_HEAD_MARKETING'),
            'ASCO_CUSTOM_HEAD_FUNCTIONAL' => (string) Tools::getValue('ASCO_CUSTOM_HEAD_FUNCTIONAL'),
        ];
    }

    /**
     * Validate the Custom Scripts fields. Tracker IDs are checked against their
     * provider format; the chosen category must be an existing active slug.
     *
     * @return string[]
     */
    protected function saveCustomScripts()
    {
        $errors = [];
        $d = 'Modules.Aplinesimplecookies.Admin';
        $s = $this->getSubmittedScripts();
        $cats = array_keys($this->getCategorySlugOptions());

        if ($s['ASCO_GA4_ID'] !== '' && !preg_match('/^G-[A-Z0-9]+$/', $s['ASCO_GA4_ID'])) {
            $errors[] = $this->trans('Google Analytics 4 ID must look like G-XXXXXXXXXX.', [], $d);
        }
        if ($s['ASCO_GTM_ID'] !== '' && !preg_match('/^GTM-[A-Z0-9]+$/', $s['ASCO_GTM_ID'])) {
            $errors[] = $this->trans('Google Tag Manager ID must look like GTM-XXXXXXX.', [], $d);
        }
        if ($s['ASCO_FB_PIXEL_ID'] !== '' && !preg_match('/^\d{15,16}$/', $s['ASCO_FB_PIXEL_ID'])) {
            $errors[] = $this->trans('Facebook Pixel ID must be 15-16 digits.', [], $d);
        }
        if ($s['ASCO_HOTJAR_ID'] !== '' && !preg_match('/^\d+$/', $s['ASCO_HOTJAR_ID'])) {
            $errors[] = $this->trans('Hotjar Site ID must be numeric.', [], $d);
        }

        // A configured tracker must point at an existing active category.
        $pairs = [
            ['ASCO_GA4_ID', 'ASCO_GA4_CATEGORY'],
            ['ASCO_GTM_ID', 'ASCO_GTM_CATEGORY'],
            ['ASCO_FB_PIXEL_ID', 'ASCO_FB_PIXEL_CATEGORY'],
            ['ASCO_HOTJAR_ID', 'ASCO_HOTJAR_CATEGORY'],
        ];
        foreach ($pairs as $p) {
            if ($s[$p[0]] !== '' && !in_array($s[$p[1]], $cats, true)) {
                $errors[] = $this->trans('Please choose a valid active category for each configured tracker.', [], $d);
                break;
            }
        }

        return $errors;
    }

    /**
     * Persist the Custom Scripts fields after the main configuration validates.
     * Custom HTML textareas are stored allowing HTML (admin-trusted, like the
     * Edit CSS/JS module).
     */
    protected function persistCustomScripts()
    {
        $htmlKeys = ['ASCO_CUSTOM_HEAD_ANALYTICS', 'ASCO_CUSTOM_HEAD_MARKETING', 'ASCO_CUSTOM_HEAD_FUNCTIONAL'];
        foreach ($this->getSubmittedScripts() as $key => $value) {
            Configuration::updateValue($key, $value, in_array($key, $htmlKeys, true));
        }
    }

    /**
     * All values the configuration template needs.
     *
     * @return array
     */
    protected function getConfigTemplateVars()
    {
        $conf = Configuration::getMultiple(array_keys($this->getConfigDefaults()));

        return [
            'asco_conf' => $conf,
            'asco_form_action' => $this->getConfigPageUrl(),
            'asco_export_url' => $this->getConfigPageUrl(['exportLog' => 1]),
            'asco_category_url' => $this->context->link->getAdminLink(self::ADMIN_CATEGORY),
            'asco_entry_url' => $this->context->link->getAdminLink(self::ADMIN_ENTRY),
            'asco_stats' => $this->getConsentStats(),
            'asco_submit_token' => self::SUBMIT_TOKEN,
            'asco_category_options' => $this->getCategorySlugOptions(),
            'asco_module_uri' => __PS_BASE_URI__ . 'modules/' . $this->name . '/',
        ];
    }

    /**
     * Active category slugs with their EN name, for the Custom Scripts category
     * selects (CP05) and any other admin dropdown.
     *
     * @return array
     */
    public function getCategorySlugOptions()
    {
        $options = [];
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT `slug`, `name_en` FROM `' . _DB_PREFIX_ . self::TABLE_CATEGORY . '`
                 WHERE `active` = 1 ORDER BY `position` ASC'
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $options[$row['slug']] = $row['name_en'];
                }
            }
        } catch (\Throwable $e) {
            // empty — selects still render
        }

        return $options;
    }

    /**
     * Consent-log statistics for the last 30 days, broken down by source.
     * Robust GROUP BY (no JSON parsing) so it never errors on odd data.
     *
     * @return array
     */
    protected function getConsentStats()
    {
        $stats = ['total' => 0, 'by_source' => ['banner' => 0, 'prefs' => 0, 'footer_link' => 0, 'gpc' => 0]];

        try {
            $since = date('Y-m-d H:i:s', time() - 30 * 86400);
            $rows = Db::getInstance()->executeS(
                'SELECT `source`, COUNT(*) AS c FROM `' . _DB_PREFIX_ . self::TABLE_LOG . '`
                 WHERE `date_add` >= "' . pSQL($since) . '" GROUP BY `source`'
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $count = (int) $row['c'];
                    $stats['total'] += $count;
                    if (isset($stats['by_source'][$row['source']])) {
                        $stats['by_source'][$row['source']] = $count;
                    }
                }
            }
        } catch (\Throwable $e) {
            // table may be missing during a broken state — show zeros
        }

        return $stats;
    }

    /**
     * Stream the consent log as a CSV download and exit. Uses php://output so
     * a large log never exhausts memory.
     */
    protected function exportConsentLog()
    {
        $from = Tools::getValue('from');
        $to = Tools::getValue('to');

        $where = '1';
        if (Validate::isDate($from)) {
            $where .= ' AND `date_add` >= "' . pSQL($from) . ' 00:00:00"';
        }
        if (Validate::isDate($to)) {
            $where .= ' AND `date_add` <= "' . pSQL($to) . ' 23:59:59"';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="asco_consent_log_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM so Excel opens Polish characters correctly.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['id', 'date_add', 'visitor_token', 'id_customer', 'decision', 'policy_version', 'ip_hash', 'language', 'source', 'user_agent']);

        try {
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE_LOG . '` WHERE ' . $where . ' ORDER BY `date_add` DESC'
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row['id_asco_consent_log'], $row['date_add'], $row['visitor_token'], $row['id_customer'],
                        $row['decision'], $row['policy_version'], $row['ip_hash'], $row['language'], $row['source'], $row['user_agent'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // nothing to add — the header row is still downloaded
        }

        fclose($out);
        exit;
    }

    /**
     * APLINE attribution block. Required by the module license to stay visible
     * on the configuration page with a working link to https://apline.pl.
     * Rendered server-side as a standalone component (not CSS-only) so it
     * cannot be trivially stripped.
     *
     * @return string
     */
    public function renderAplineFooter()
    {
        return '
        <style>
            .apline-credit { margin-top: 24px; font-size: 12px; opacity: 0.9; }
            .apline-credit a { font-weight: 600; }
        </style>
        <div class="apline-credit">
            ' . $this->trans('Module created by', [], 'Modules.Aplinesimplecookies.Admin') . '
            <a href="https://apline.pl" target="_blank" rel="noopener noreferrer">APLINE</a>
        </div>';
    }

    /**
     * Subtle "need custom development?" box shown on the configuration page.
     *
     * @return string
     */
    public function renderLikeBox()
    {
        return '
        <div class="panel">
            <h3>&#9749; ' . $this->trans('Like this module?', [], 'Modules.Aplinesimplecookies.Admin') . '</h3>
            <p>' . $this->trans('Need custom PrestaShop development, performance optimization or integrations?', [], 'Modules.Aplinesimplecookies.Admin') . '</p>
            <a class="btn btn-default" href="https://apline.pl" target="_blank" rel="noopener noreferrer">&#8594; APLINE.PL</a>
        </div>';
    }

    /* --------------------------------------------------------------------- */
    /* Front-end render                                                      */
    /* --------------------------------------------------------------------- */

    public function hookActionFrontControllerSetMedia()
    {
        try {
            $this->context->controller->registerStylesheet(
                'apline-simple-cookies',
                'modules/' . $this->name . '/views/css/front.css'
            );
            $this->context->controller->registerJavascript(
                'apline-simple-cookies',
                'modules/' . $this->name . '/views/js/banner.js',
                ['position' => 'bottom', 'priority' => 30]
            );
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies setMedia: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Inline window.ASCO bootstrap in <head>: everything banner.js needs to
     * decide whether to show the banner and which trackers to load.
     */
    public function hookDisplayHeader($params)
    {
        try {
            return $this->renderInitScript();
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies header: ' . $e->getMessage(), 3);

            return '';
        }
    }

    /**
     * @return string the inline bootstrap <script>
     */
    private function renderInitScript()
    {
        $lang = $this->resolveLang();
        $existing = $this->readConsentCookie();

        $gpcAutoReject = false;
        if ((int) Configuration::get('ASCO_RESPECT_GPC')
            && isset($_SERVER['HTTP_SEC_GPC']) && (string) $_SERVER['HTTP_SEC_GPC'] === '1'
        ) {
            $gpcAutoReject = true;
        }

        $data = [
            'lang' => $lang,
            'categories' => AplineSimpleCookiesCategory::getActiveCategories($lang),
            'existing_consent' => $existing,
            'reprompt_days' => (int) Configuration::get('ASCO_REPROMPT_DAYS'),
            'policy_version' => (string) Configuration::get('ASCO_POLICY_VERSION'),
            'policy_url' => (string) Configuration::get('ASCO_POLICY_URL'),
            'banner_position' => (string) Configuration::get('ASCO_BANNER_POSITION'),
            'banner_style' => (string) Configuration::get('ASCO_BANNER_STYLE'),
            'primary_button' => (string) Configuration::get('ASCO_PRIMARY_BUTTON'),
            'copy' => $this->getCopyForLang($lang),
            'scripts' => $this->getScriptsConfig(),
            'gpc_auto_rejected' => $gpcAutoReject,
            'callback_url' => $this->context->link->getModuleLink($this->name, 'consent', [], true),
        ];

        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

        return '<script>window.ASCO = ' . $json . ';</script>';
    }

    /**
     * Render the banner + preferences modal at the end of the body.
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        try {
            $lang = $this->resolveLang();
            $this->context->smarty->assign([
                'asco_categories' => AplineSimpleCookiesCategory::getActiveCategories($lang),
                'asco_copy' => $this->getCopyForLang($lang),
                'asco_lang' => $lang,
                'asco_position' => (string) Configuration::get('ASCO_BANNER_POSITION'),
                'asco_style' => (string) Configuration::get('ASCO_BANNER_STYLE'),
                'asco_primary' => (string) Configuration::get('ASCO_PRIMARY_BUTTON'),
            ]);

            return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies banner: ' . $e->getMessage(), 3);

            return '';
        }
    }

    /**
     * "Cookie settings" link in the footer; reopens the banner.
     */
    public function hookDisplayFooterAfter($params)
    {
        try {
            $copy = $this->getCopyForLang($this->resolveLang());

            return '<a href="#" class="asco-footer-link" onclick="if(window.ASCO_openBanner){window.ASCO_openBanner();}return false;">'
                . htmlspecialchars($copy['footer_link'], ENT_QUOTES, 'UTF-8')
                . '</a>';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Resolve the banner language from the PrestaShop context, falling back to
     * ASCO_DEFAULT_LANG when the shop language is neither PL nor EN.
     *
     * @return string 'pl' or 'en'
     */
    public function resolveLang()
    {
        $iso = '';
        if (isset($this->context->language) && Validate::isLoadedObject($this->context->language)) {
            $iso = strtolower((string) $this->context->language->iso_code);
        }
        if ($iso === 'pl' || $iso === 'en') {
            return $iso;
        }

        return Configuration::get('ASCO_DEFAULT_LANG') === 'en' ? 'en' : 'pl';
    }

    /**
     * Banner copy for one language, resolved from the ASCO_COPY_* keys.
     *
     * @param string $lang 'pl' or 'en'
     *
     * @return array
     */
    public function getCopyForLang($lang)
    {
        $u = ($lang === 'en') ? 'EN' : 'PL';

        return [
            'title' => (string) Configuration::get('ASCO_COPY_TITLE_' . $u),
            'body' => (string) Configuration::get('ASCO_COPY_BODY_' . $u),
            'btn_accept' => (string) Configuration::get('ASCO_COPY_BTN_ACCEPT_' . $u),
            'btn_reject' => (string) Configuration::get('ASCO_COPY_BTN_REJECT_' . $u),
            'btn_prefs' => (string) Configuration::get('ASCO_COPY_BTN_PREFS_' . $u),
            'btn_save' => (string) Configuration::get('ASCO_COPY_BTN_SAVE_' . $u),
            'footer_link' => (string) Configuration::get('ASCO_COPY_FOOTER_LINK_' . $u),
            'policy_url' => (string) Configuration::get('ASCO_POLICY_URL'),
        ];
    }

    /**
     * The current visitor's consent cookie, decoded, or null if absent/invalid.
     *
     * @return array|null
     */
    public function readConsentCookie()
    {
        if (empty($_COOKIE['asco_consent'])) {
            return null;
        }
        $decoded = json_decode((string) $_COOKIE['asco_consent'], true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Third-party tracker configuration exposed to banner.js (IDs + the consent
     * category that unlocks each, plus the raw custom HTML snippets). The actual
     * gated injection lives in banner.js (see CP08).
     *
     * @return array
     */
    public function getScriptsConfig()
    {
        return [
            'ga4' => ['id' => (string) Configuration::get('ASCO_GA4_ID'), 'category' => (string) Configuration::get('ASCO_GA4_CATEGORY')],
            'gtm' => ['id' => (string) Configuration::get('ASCO_GTM_ID'), 'category' => (string) Configuration::get('ASCO_GTM_CATEGORY')],
            'fb' => ['id' => (string) Configuration::get('ASCO_FB_PIXEL_ID'), 'category' => (string) Configuration::get('ASCO_FB_PIXEL_CATEGORY')],
            'hotjar' => ['id' => (string) Configuration::get('ASCO_HOTJAR_ID'), 'category' => (string) Configuration::get('ASCO_HOTJAR_CATEGORY')],
            'custom' => [
                'analytics' => (string) Configuration::get('ASCO_CUSTOM_HEAD_ANALYTICS'),
                'marketing' => (string) Configuration::get('ASCO_CUSTOM_HEAD_MARKETING'),
                'functional' => (string) Configuration::get('ASCO_CUSTOM_HEAD_FUNCTIONAL'),
            ],
        ];
    }

    /* --------------------------------------------------------------------- */
    /* Consent persistence (called from the /consent front controller)       */
    /* --------------------------------------------------------------------- */

    /**
     * Persist a consent decision: refresh the consent cookie, keep a visitor
     * token, and (optionally) write an audit-log row. Returns the normalized
     * decision (necessary forced true, unknown categories dropped).
     *
     * @param array  $decision slug => bool
     * @param string $source   banner|prefs|footer_link|gpc
     *
     * @return array
     */
    public function saveConsent(array $decision, $source)
    {
        $token = $this->getOrCreateVisitorToken();
        $clean = $this->normalizeDecision($decision);
        $version = (string) Configuration::get('ASCO_POLICY_VERSION');

        $cookieValue = json_encode(['v' => $version, 'd' => $clean, 't' => time()]);
        $days = (int) Configuration::get('ASCO_REPROMPT_DAYS');
        $this->setConsentCookie('asco_consent', $cookieValue, time() + 86400 * max(1, $days));

        if ((int) Configuration::get('ASCO_LOG_CONSENTS')) {
            $this->logConsent($token, $clean, (string) $source);
        }

        return $clean;
    }

    /**
     * Keep only known active category slugs; necessary categories are always
     * granted regardless of what the client sent.
     *
     * @param array $decision
     *
     * @return array
     */
    private function normalizeDecision(array $decision)
    {
        $clean = [];
        foreach (AplineSimpleCookiesCategory::getActiveCategories('pl') as $cat) {
            $slug = $cat['slug'];
            $clean[$slug] = !empty($cat['is_necessary']) ? true : !empty($decision[$slug]);
        }

        return $clean;
    }

    /**
     * Read or create the anonymous visitor token (stored in the asco_visitor
     * cookie for one year).
     *
     * @return string
     */
    private function getOrCreateVisitorToken()
    {
        if (!empty($_COOKIE['asco_visitor']) && preg_match('/^[a-f0-9]{16,64}$/i', (string) $_COOKIE['asco_visitor'])) {
            return (string) $_COOKIE['asco_visitor'];
        }
        $token = $this->generateUuid();
        $this->setConsentCookie('asco_visitor', $token, time() + 86400 * 365);

        return $token;
    }

    /**
     * @return string 32 hex chars
     */
    private function generateUuid()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return md5(uniqid((string) microtime(true), true));
        }
    }

    /**
     * Set a front-end cookie with sane security attributes. Must be called
     * before any output (i.e. from the AJAX consent endpoint).
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     */
    private function setConsentCookie($name, $value, $expire)
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

        // JS must read asco_consent at runtime, so httponly stays false.
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => '/',
            'secure' => $secure,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$name] = $value;
    }

    /**
     * Write one consent audit-log row and prune expired rows.
     *
     * @param string $token
     * @param array  $decision
     * @param string $source
     */
    private function logConsent($token, array $decision, $source)
    {
        try {
            $ipHash = null;
            if ((int) Configuration::get('ASCO_LOG_IP') && !empty($_SERVER['REMOTE_ADDR'])) {
                $salt = (string) Configuration::get('ASCO_LOG_IP_SALT');
                $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] . $salt);
            }
            $idCustomer = ($this->context->customer && $this->context->customer->id)
                ? (int) $this->context->customer->id : null;

            Db::getInstance()->insert(self::TABLE_LOG, [
                'visitor_token' => pSQL($token),
                'id_customer' => $idCustomer,
                'decision' => pSQL(json_encode($decision)),
                'policy_version' => pSQL((string) Configuration::get('ASCO_POLICY_VERSION')),
                'ip_hash' => $ipHash ? pSQL($ipHash) : null,
                'user_agent' => pSQL(Tools::substr((string) (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''), 0, 512)),
                'language' => pSQL($this->resolveLang()),
                'source' => pSQL($source),
                'date_add' => date('Y-m-d H:i:s'),
            ]);

            $this->cleanupOldLogs();
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies log: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Delete consent-log rows older than the configured retention window.
     */
    private function cleanupOldLogs()
    {
        $days = (int) Configuration::get('ASCO_LOG_RETENTION_DAYS');
        if ($days <= 0) {
            return;
        }
        $cutoff = date('Y-m-d H:i:s', time() - $days * 86400);
        Db::getInstance()->delete(self::TABLE_LOG, '`date_add` < "' . pSQL($cutoff) . '"');
    }

    /**
     * Which third-party scripts may load for a granted decision. Returns a list
     * of descriptors ({type, id} for known trackers, {type:'custom', html:...}
     * for the raw snippets) — only for trackers whose consent category is
     * granted and that are actually configured. banner.js turns each descriptor
     * into a real <script>.
     *
     * @param array $decision slug => bool
     *
     * @return array
     */
    public function scriptsForGrantedConsent(array $decision)
    {
        $cfg = $this->getScriptsConfig();
        $out = [];

        foreach (['ga4', 'gtm', 'fb', 'hotjar'] as $type) {
            $id = isset($cfg[$type]['id']) ? $cfg[$type]['id'] : '';
            $category = isset($cfg[$type]['category']) ? $cfg[$type]['category'] : '';
            if ($id !== '' && !empty($decision[$category])) {
                $out[] = ['type' => $type, 'id' => $id];
            }
        }

        foreach (['analytics', 'marketing', 'functional'] as $category) {
            $html = isset($cfg['custom'][$category]) ? $cfg['custom'][$category] : '';
            if ($html !== '' && !empty($decision[$category])) {
                $out[] = ['type' => 'custom', 'html' => $html];
            }
        }

        return $out;
    }
}
