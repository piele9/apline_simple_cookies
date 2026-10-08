<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * Admin CRUD for individual cookie entries (cookie name, provider, purpose
 * PL+EN, expiration, domain) attached to a category.
 *
 * @author    Arkadiusz Pielechowski
 * @copyright Arkadiusz Pielechowski
 * @license   MIT - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'apline_simple_cookies/classes/AplineSimpleCookiesEntry.php';
require_once _PS_MODULE_DIR_ . 'apline_simple_cookies/classes/AplineSimpleCookiesCategory.php';

class AdminAplineSimpleCookiesEntryController extends ModuleAdminController
{
    const MAX_STRING = 255;
    const MAX_EXPIRATION = 64;
    const DOMAIN = 'Modules.Aplinesimplecookies.Admin';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'asco_entry';
        $this->className = 'AplineSimpleCookiesEntry';
        $this->identifier = 'id_asco_entry';
        $this->position_identifier = 'id_asco_entry';
        $this->lang = false;
        $this->allow_export = false;

        parent::__construct();

        // Show the (Polish) category name on the list via a join.
        $this->_select = 'c.`name_pl` AS category_name';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'asco_category` c ON c.`id_asco_category` = a.`id_asco_category`';

        $this->fields_list = [
            'id_asco_entry' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'cookie_name' => [
                'title' => $this->trans('Nazwa cookie', [], self::DOMAIN),
            ],
            'provider' => [
                'title' => $this->trans('Dostawca', [], self::DOMAIN),
            ],
            'category_name' => [
                'title' => $this->trans('Kategoria', [], self::DOMAIN),
                'search' => false,
                'orderby' => false,
            ],
            'expiration' => [
                'title' => $this->trans('Ważność', [], self::DOMAIN),
                'search' => false,
            ],
            'position' => [
                'title' => $this->trans('Pozycja', [], self::DOMAIN),
                'align' => 'center',
                'position' => 'position',
                'search' => false,
            ],
        ];

        $this->_defaultOrderBy = 'id_asco_category';
        $this->_defaultOrderWay = 'ASC';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Usuń zaznaczone', [], 'Admin.Actions'),
                'confirm' => $this->trans('Usunąć zaznaczone cookies?', [], 'Admin.Notifications.Warning'),
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.sortable');
    }

    /**
     * @return string
     */
    private function getConfigUrl()
    {
        return $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => 'apline_simple_cookies',
            'module_name' => 'apline_simple_cookies',
        ]);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['back_to_config'] = [
            'href' => $this->getConfigUrl(),
            'desc' => $this->trans('Wróć do konfiguracji', [], self::DOMAIN),
            'icon' => 'process-icon-back',
        ];
    }

    public function renderList()
    {
        $list = parent::renderList();

        $back = '<div style="margin:10px 0;"><a class="btn btn-default" href="'
            . htmlspecialchars($this->getConfigUrl(), ENT_QUOTES)
            . '"><i class="icon-chevron-left"></i> '
            . $this->trans('Wróć do konfiguracji', [], self::DOMAIN)
            . '</a></div>';

        $credit = method_exists($this->module, 'renderAplineFooter')
            ? $this->module->renderAplineFooter()
            : '';

        return $back . $list . $credit;
    }

    /**
     * @return array category options for the form select
     */
    private function getCategoryOptions()
    {
        $options = [];
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT `id_asco_category`, `name_pl` FROM `' . _DB_PREFIX_ . 'asco_category`
                 ORDER BY `position` ASC'
            );
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $options[] = [
                        'id_asco_category' => (int) $row['id_asco_category'],
                        'name' => $row['name_pl'] . ' (' . $row['id_asco_category'] . ')',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // empty options — form still renders
        }

        return $options;
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Plik cookie', [], self::DOMAIN),
                'icon' => 'icon-cube',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->trans('Kategoria', [], self::DOMAIN),
                    'name' => 'id_asco_category',
                    'required' => true,
                    'options' => [
                        'query' => $this->getCategoryOptions(),
                        'id' => 'id_asco_category',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Nazwa cookie', [], self::DOMAIN),
                    'name' => 'cookie_name',
                    'required' => true,
                    'desc' => $this->trans('Np. _ga, _fbp, PHPSESSID.', [], self::DOMAIN),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Dostawca', [], self::DOMAIN),
                    'name' => 'provider',
                    'required' => true,
                    'desc' => $this->trans('Kto odczytuje cookie, np. Google LLC, Meta Platforms albo nazwa Twojej firmy.', [], self::DOMAIN),
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Cel (PL)', [], self::DOMAIN),
                    'name' => 'purpose_pl',
                    'required' => true,
                    'rows' => 2,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Cel (EN)', [], self::DOMAIN),
                    'name' => 'purpose_en',
                    'required' => true,
                    'rows' => 2,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Ważność', [], self::DOMAIN),
                    'name' => 'expiration',
                    'required' => true,
                    'desc' => $this->trans('Dowolny tekst, np. 2 lata / 2 years, 30 dni / 30 days, Sesja / Session.', [], self::DOMAIN),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Domena', [], self::DOMAIN),
                    'name' => 'domain',
                    'desc' => $this->trans('Opcjonalnie. Domena, która ustawia cookie, np. .google-analytics.com.', [], self::DOMAIN),
                ],
            ],
            'submit' => ['title' => $this->trans('Zapisz', [], 'Admin.Actions')],
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        $isAdd = Tools::isSubmit('submitAdd' . $this->table) && !Tools::getValue($this->identifier);
        $isUpdate = Tools::isSubmit('submitAdd' . $this->table) && Tools::getValue($this->identifier);

        if ($isAdd || $isUpdate) {
            if ($isUpdate) {
                $existing = new AplineSimpleCookiesEntry((int) Tools::getValue($this->identifier));
                if (!Validate::isLoadedObject($existing)) {
                    $this->errors[] = $this->trans('Edytowany plik cookie nie istnieje.', [], self::DOMAIN);

                    return false;
                }
            }

            if (!$this->validateSubmission()) {
                $this->display = $isUpdate ? 'edit' : 'add';

                return false;
            }
        }

        return parent::postProcess();
    }

    /**
     * Reject invalid input (never silently truncate).
     *
     * @return bool
     */
    private function validateSubmission()
    {
        $idCategory = (int) Tools::getValue('id_asco_category');
        $cookieName = trim((string) Tools::getValue('cookie_name'));
        $provider = trim((string) Tools::getValue('provider'));
        $purposePl = trim((string) Tools::getValue('purpose_pl'));
        $purposeEn = trim((string) Tools::getValue('purpose_en'));
        $expiration = trim((string) Tools::getValue('expiration'));
        $domain = trim((string) Tools::getValue('domain'));

        if ($cookieName === '') {
            $this->errors[] = $this->trans('Pole „Nazwa cookie” jest wymagane.', [], self::DOMAIN);
        }
        if ($provider === '') {
            $this->errors[] = $this->trans('Pole „Dostawca” jest wymagane.', [], self::DOMAIN);
        }
        if ($purposePl === '') {
            $this->errors[] = $this->trans('Pole „Cel (PL)” jest wymagane.', [], self::DOMAIN);
        }
        if ($purposeEn === '') {
            $this->errors[] = $this->trans('Pole „Cel (EN)” jest wymagane.', [], self::DOMAIN);
        }
        if ($expiration === '') {
            $this->errors[] = $this->trans('Pole „Ważność” jest wymagane.', [], self::DOMAIN);
        }

        foreach (['Nazwa cookie' => $cookieName, 'Dostawca' => $provider, 'Domena' => $domain] as $label => $value) {
            if (mb_strlen($value) > self::MAX_STRING) {
                $this->errors[] = $this->trans('Pole „%s” może mieć najwyżej 255 znaków.', [$label], self::DOMAIN);
            }
        }
        if (mb_strlen($expiration) > self::MAX_EXPIRATION) {
            $this->errors[] = $this->trans('Pole „Ważność” może mieć najwyżej 64 znaki.', [], self::DOMAIN);
        }

        // Category must exist.
        $category = new AplineSimpleCookiesCategory($idCategory);
        if (!$idCategory || !Validate::isLoadedObject($category)) {
            $this->errors[] = $this->trans('Wybierz istniejącą kategorię.', [], self::DOMAIN);
        }

        if (!empty($this->errors)) {
            return false;
        }

        $_POST['cookie_name'] = $cookieName;
        $_POST['provider'] = $provider;
        $_POST['expiration'] = $expiration;
        $_POST['domain'] = $domain;

        return true;
    }

    public function ajaxProcessUpdatePositions()
    {
        $positions = Tools::getValue($this->table);

        if (!is_array($positions)) {
            die(json_encode(['success' => false]));
        }

        $pos = 1;
        foreach ($positions as $value) {
            $parts = explode('_', (string) $value);
            $id = (int) end($parts);
            if (!$id) {
                continue;
            }
            Db::getInstance()->update(
                'asco_entry',
                ['position' => $pos++],
                'id_asco_entry = ' . $id
            );
        }

        die(json_encode(['success' => true]));
    }
}
