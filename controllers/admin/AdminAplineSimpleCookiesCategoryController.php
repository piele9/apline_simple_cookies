<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * Admin CRUD for cookie categories. Enforces the "exactly one necessary
 * category" rule and a clean lowercase slug. Deleting a category cascades to
 * its cookie entries (handled in PHP — see processDelete).
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
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
                'title' => $this->trans('Slug', [], self::DOMAIN),
            ],
            'name_pl' => [
                'title' => $this->trans('Name (PL)', [], self::DOMAIN),
            ],
            'name_en' => [
                'title' => $this->trans('Name (EN)', [], self::DOMAIN),
            ],
            'is_necessary' => [
                'title' => $this->trans('Necessary', [], self::DOMAIN),
                'align' => 'center',
                'type' => 'bool',
                'callback' => 'printNecessary',
                'orderby' => false,
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Active', [], self::DOMAIN),
                'align' => 'center',
                'active' => 'active',
                'type' => 'bool',
                'orderby' => false,
            ],
            'position' => [
                'title' => $this->trans('Position', [], self::DOMAIN),
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
                'text' => $this->trans('Delete selected', [], 'Admin.Actions'),
                'confirm' => $this->trans('Delete selected items? Their cookie entries will also be removed.', [], self::DOMAIN),
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
            'desc' => $this->trans('Back to configuration', [], self::DOMAIN),
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
            ? '<span class="label label-info">' . $this->trans('Always on', [], self::DOMAIN) . '</span>'
            : '<span class="text-muted">&mdash;</span>';
    }

    public function renderList()
    {
        $list = parent::renderList();

        $back = '<div style="margin:10px 0;"><a class="btn btn-default" href="'
            . htmlspecialchars($this->getConfigUrl(), ENT_QUOTES)
            . '"><i class="icon-chevron-left"></i> '
            . $this->trans('Back to configuration', [], self::DOMAIN)
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
                'title' => $this->trans('Cookie category', [], self::DOMAIN),
                'icon' => 'icon-folder',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Slug', [], self::DOMAIN),
                    'name' => 'slug',
                    'required' => true,
                    'desc' => $this->trans('Lowercase identifier (a-z, 0-9, _). Used by the front-end and the Custom Scripts mapping. Must be unique. E.g. analytics, marketing.', [], self::DOMAIN),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Name (PL)', [], self::DOMAIN),
                    'name' => 'name_pl',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Name (EN)', [], self::DOMAIN),
                    'name' => 'name_en',
                    'required' => true,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Description (PL)', [], self::DOMAIN),
                    'name' => 'description_pl',
                    'required' => true,
                    'rows' => 3,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->trans('Description (EN)', [], self::DOMAIN),
                    'name' => 'description_en',
                    'required' => true,
                    'rows' => 3,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Necessary category', [], self::DOMAIN),
                    'name' => 'is_necessary',
                    'is_bool' => true,
                    'desc' => $this->trans('Only one category can be necessary. Always-on, no toggle in user preferences.', [], self::DOMAIN),
                    'values' => [
                        ['id' => 'is_necessary_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                        ['id' => 'is_necessary_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Active', [], self::DOMAIN),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
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
                    $this->errors[] = $this->trans('The category you are trying to edit does not exist.', [], self::DOMAIN);

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
            $this->errors[] = $this->trans('The field "Name (PL)" is required.', [], self::DOMAIN);
        }
        if ($nameEn === '') {
            $this->errors[] = $this->trans('The field "Name (EN)" is required.', [], self::DOMAIN);
        }
        if ($descPl === '') {
            $this->errors[] = $this->trans('The field "Description (PL)" is required.', [], self::DOMAIN);
        }
        if ($descEn === '') {
            $this->errors[] = $this->trans('The field "Description (EN)" is required.', [], self::DOMAIN);
        }

        // Length caps (reject).
        if (mb_strlen($namePl) > self::MAX_STRING || mb_strlen($nameEn) > self::MAX_STRING) {
            $this->errors[] = $this->trans('Category names must not exceed 255 characters.', [], self::DOMAIN);
        }

        // Slug: lowercase, starts with a letter, unique.
        if ($slug === '') {
            $this->errors[] = $this->trans('The field "Slug" is required.', [], self::DOMAIN);
        } elseif (mb_strlen($slug) > self::MAX_SLUG) {
            $this->errors[] = $this->trans('The slug must not exceed 64 characters.', [], self::DOMAIN);
        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $slug)) {
            $this->errors[] = $this->trans('The slug may only contain lowercase letters, digits and underscores, and must start with a letter.', [], self::DOMAIN);
        } elseif (AplineSimpleCookiesCategory::slugExists($slug, $currentId)) {
            $this->errors[] = $this->trans('This slug is already used by another category.', [], self::DOMAIN);
        }

        // Necessary singleton: at most one category may be necessary.
        if ($isNecessary && AplineSimpleCookiesCategory::countNecessary($currentId) > 0) {
            $this->errors[] = $this->trans('Only one necessary category is allowed. Disable the existing one first.', [], self::DOMAIN);
        }

        // Must never end up with zero necessary categories: block unchecking
        // the last one.
        if (!$isNecessary && $existing && (int) $existing->is_necessary === 1
            && AplineSimpleCookiesCategory::countNecessary($currentId) === 0
        ) {
            $this->errors[] = $this->trans('You cannot remove the necessary flag from the only necessary category. Mark another category as necessary first.', [], self::DOMAIN);
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
                $this->errors[] = $this->trans('You cannot delete the only necessary category.', [], self::DOMAIN);

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
