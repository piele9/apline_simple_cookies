<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * Admin CRUD for cookie categories. Enforces the "exactly one necessary
 * category" rule and a clean lowercase slug. Deleting a category cascades to
 * its cookie entries (handled in PHP — see processDelete).
 *
 * @author    Arkadiusz Pielechowski
 * @copyright Arkadiusz Pielechowski
 * @license   MIT - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'apline_simple_cookies/classes/AplineSimpleCookiesCategory.php';

class AdminAplineSimpleCookiesCategoryController extends ModuleAdminController
{
    const MAX_STRING = 255;
    const MAX_SLUG = 64;
    const DOMAIN = 'Modules.Aplinesimplecookies.Admin';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'asco_category';
        $this->className = 'AplineSimpleCookiesCategory';
        $this->identifier = 'id_asco_category';
        $this->position_identifier = 'id_asco_category';
        $this->lang = false;
        $this->allow_export = false;

        parent::__construct();

        $this->fields_list = [
            'id_asco_category' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'slug' => [
                'title' => $this->trans('Identyfikator (slug)', [], self::DOMAIN),
            ],
            'name_pl' => [
                'title' => $this->trans('Nazwa (PL)', [], self::DOMAIN),
            ],
            'name_en' => [
                'title' => $this->trans('Nazwa (EN)', [], self::DOMAIN),
            ],
            'is_necessary' => [
                'title' => $this->trans('Niezbędna', [], self::DOMAIN),
                'align' => 'center',
                'type' => 'bool',
                'callback' => 'printNecessary',
                'orderby' => false,
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Aktywna', [], self::DOMAIN),
                'align' => 'center',
                'active' => 'active',
                'type' => 'bool',
                'orderby' => false,
            ],
            'position' => [
                'title' => $this->trans('Pozycja', [], self::DOMAIN),
                'align' => 'center',
                'position' => 'position',
                'search' => false,
            ],
        ];

        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Usuń zaznaczone', [], 'Admin.Actions'),
                'confirm' => $this->trans('Usunąć zaznaczone kategorie? Cookies przypisane do nich też zostaną usunięte.', [], self::DOMAIN),
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

    /**
     * @param int $value
     *
     * @return string
     */
    public function printNecessary($value, $row)
    {
        return $value
            ? '<span class="label label-info">' . $this->trans('Zawsze włączona', [], self::DOMAIN) . '</span>'
            : '<span class="text-muted">&mdash;</span>';
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

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Kategoria cookies', [], self::DOMAIN),
                'icon' => 'icon-folder',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Identyfikator (slug)', [], self::DOMAIN),
                    'name' => 'slug',
                    'required' => true,
                    'desc' => $this->trans('Małe litery, cyfry i podkreślenia (a–z, 0–9, _), na początku litera; musi być unikalny. Używają go baner i przypisanie skryptów do kategorii, np. analytics, marketing.', [], self::DOMAIN),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Nazwa (PL)', [], self::DOMAIN),
                    'name' => 'name_pl',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Nazwa (EN)', [], self::DOMAIN),
                    'name' => 'name_en',
                    'required' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Opis (PL)', [], self::DOMAIN),
                    'name' => 'description_pl',
                    'required' => true,
                    'rows' => 3,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Opis (EN)', [], self::DOMAIN),
                    'name' => 'description_en',
                    'required' => true,
                    'rows' => 3,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Kategoria niezbędna', [], self::DOMAIN),
                    'name' => 'is_necessary',
                    'is_bool' => true,
                    'desc' => $this->trans('Tylko jedna kategoria może być niezbędna. Jest zawsze włączona i nie ma przełącznika w preferencjach klienta.', [], self::DOMAIN),
                    'values' => [
                        ['id' => 'is_necessary_on', 'value' => 1, 'label' => $this->trans('Tak', [], 'Admin.Global')],
                        ['id' => 'is_necessary_off', 'value' => 0, 'label' => $this->trans('Nie', [], 'Admin.Global')],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Aktywna', [], self::DOMAIN),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Tak', [], 'Admin.Global')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('Nie', [], 'Admin.Global')],
                    ],
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
            $existing = null;
            if ($isUpdate) {
                $existing = new AplineSimpleCookiesCategory((int) Tools::getValue($this->identifier));
                if (!Validate::isLoadedObject($existing)) {
                    $this->errors[] = $this->trans('Edytowana kategoria nie istnieje.', [], self::DOMAIN);

                    return false;
                }
            }

            if (!$this->validateSubmission($existing)) {
                $this->display = $isUpdate ? 'edit' : 'add';

                return false;
            }
        }

        return parent::postProcess();
    }

    /**
     * Reject invalid input (never silently truncate). On success, normalize a
     * few fields back into $_POST for the standard ObjectModel save.
     *
     * @param AplineSimpleCookiesCategory|null $existing
     *
     * @return bool
     */
    private function validateSubmission($existing)
    {
        $slug = strtolower(trim((string) Tools::getValue('slug')));
        $namePl = trim((string) Tools::getValue('name_pl'));
        $nameEn = trim((string) Tools::getValue('name_en'));
        $descPl = trim((string) Tools::getValue('description_pl'));
        $descEn = trim((string) Tools::getValue('description_en'));
        $isNecessary = (int) Tools::getValue('is_necessary');
        $currentId = $existing ? (int) $existing->id : 0;

        // Required text fields.
        if ($namePl === '') {
            $this->errors[] = $this->trans('Pole „Nazwa (PL)” jest wymagane.', [], self::DOMAIN);
        }
        if ($nameEn === '') {
            $this->errors[] = $this->trans('Pole „Nazwa (EN)” jest wymagane.', [], self::DOMAIN);
        }
        if ($descPl === '') {
            $this->errors[] = $this->trans('Pole „Opis (PL)” jest wymagane.', [], self::DOMAIN);
        }
        if ($descEn === '') {
            $this->errors[] = $this->trans('Pole „Opis (EN)” jest wymagane.', [], self::DOMAIN);
        }

        // Length caps (reject).
        if (mb_strlen($namePl) > self::MAX_STRING || mb_strlen($nameEn) > self::MAX_STRING) {
            $this->errors[] = $this->trans('Nazwa kategorii może mieć najwyżej 255 znaków.', [], self::DOMAIN);
        }

        // Slug: lowercase, starts with a letter, unique.
        if ($slug === '') {
            $this->errors[] = $this->trans('Pole „Identyfikator (slug)” jest wymagane.', [], self::DOMAIN);
        } elseif (mb_strlen($slug) > self::MAX_SLUG) {
            $this->errors[] = $this->trans('Identyfikator może mieć najwyżej 64 znaki.', [], self::DOMAIN);
        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $slug)) {
            $this->errors[] = $this->trans('Identyfikator może zawierać tylko małe litery, cyfry i podkreślenia i musi zaczynać się literą.', [], self::DOMAIN);
        } elseif (AplineSimpleCookiesCategory::slugExists($slug, $currentId)) {
            $this->errors[] = $this->trans('Ten identyfikator ma już inna kategoria.', [], self::DOMAIN);
        }

        // Necessary singleton: at most one category may be necessary.
        if ($isNecessary && AplineSimpleCookiesCategory::countNecessary($currentId) > 0) {
            $this->errors[] = $this->trans('Może być tylko jedna kategoria niezbędna. Najpierw zdejmij to oznaczenie z obecnej.', [], self::DOMAIN);
        }

        // Must never end up with zero necessary categories: block unchecking
        // the last one.
        if (!$isNecessary && $existing && (int) $existing->is_necessary === 1
            && AplineSimpleCookiesCategory::countNecessary($currentId) === 0
        ) {
            $this->errors[] = $this->trans('Nie można zdjąć oznaczenia „niezbędna” z jedynej niezbędnej kategorii. Najpierw oznacz jako niezbędną inną kategorię.', [], self::DOMAIN);
        }

        if (!empty($this->errors)) {
            return false;
        }

        $_POST['slug'] = $slug;
        $_POST['name_pl'] = $namePl;
        $_POST['name_en'] = $nameEn;

        return true;
    }

    /**
     * Cascade-delete cookie entries before removing the category (no DB FK is
     * relied on, since some hosts run MyISAM where FKs are ignored).
     */
    public function processDelete()
    {
        $obj = $this->loadObject(true);
        if (Validate::isLoadedObject($obj)) {
            if ((int) $obj->is_necessary === 1 && AplineSimpleCookiesCategory::countNecessary((int) $obj->id) === 0) {
                $this->errors[] = $this->trans('Nie można usunąć jedynej kategorii niezbędnej.', [], self::DOMAIN);

                return false;
            }
            Db::getInstance()->delete('asco_entry', 'id_asco_category = ' . (int) $obj->id);
        }

        return parent::processDelete();
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
                'asco_category',
                ['position' => $pos++],
                'id_asco_category = ' . $id
            );
        }

        die(json_encode(['success' => true]));
    }
}
