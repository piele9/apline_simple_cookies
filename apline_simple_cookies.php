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
        $categoryUrl = $this->context->link->getAdminLink(self::ADMIN_CATEGORY);
        $entryUrl = $this->context->link->getAdminLink(self::ADMIN_ENTRY);

        $output = '<div class="panel">'
            . '<h3><i class="icon-shield"></i> ' . $this->trans('APLINE Simple Cookies', [], 'Modules.Aplinesimplecookies.Admin') . '</h3>'
            . '<p>' . $this->trans('GDPR/UODO compliant cookie consent. Full configuration is added in a later step.', [], 'Modules.Aplinesimplecookies.Admin') . '</p>'
            . '<a class="btn btn-default" href="' . htmlspecialchars($categoryUrl, ENT_QUOTES) . '"><i class="icon-folder"></i> ' . $this->trans('Manage cookie categories', [], 'Modules.Aplinesimplecookies.Admin') . '</a> '
            . '<a class="btn btn-default" href="' . htmlspecialchars($entryUrl, ENT_QUOTES) . '"><i class="icon-list"></i> ' . $this->trans('Manage individual cookies', [], 'Modules.Aplinesimplecookies.Admin') . '</a>'
            . '</div>';

        return $output . $this->renderLikeBox() . $this->renderAplineFooter();
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
}
