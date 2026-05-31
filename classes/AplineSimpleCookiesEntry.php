<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * ObjectModel for an individual cookie entry (e.g. _ga, _fbp, PHPSESSID)
 * belonging to a category. Bilingual purpose columns (PL + EN). Shown to
 * visitors in the Preferences modal cookie list and used by the cookie-policy
 * template.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AplineSimpleCookiesEntry extends ObjectModel
{
    /** @var int */
    public $id_asco_category;
    /** @var string */
    public $cookie_name;
    /** @var string */
    public $provider;
    /** @var string */
    public $purpose_pl;
    /** @var string */
    public $purpose_en;
    /** @var string */
    public $expiration;
    /** @var string|null */
    public $domain;
    /** @var int */
    public $position;
    /** @var string */
    public $date_add;
    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'asco_entry',
        'primary' => 'id_asco_entry',
        'multilang' => false,
        'fields' => [
            'id_asco_category' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'cookie_name' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'provider' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'purpose_pl' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'purpose_en' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'expiration' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 64],
            'domain' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Entries of a category, normalized to one language. Guarded so a missing
     * table never breaks the shop front.
     *
     * @param int    $idCategory
     * @param string $lang 'pl' or 'en'
     *
     * @return array
     */
    public static function getByCategory($idCategory, $lang = 'pl')
    {
        $lang = ($lang === 'en') ? 'en' : 'pl';

        try {
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'asco_entry`
                 WHERE `id_asco_category` = ' . (int) $idCategory . '
                 ORDER BY `position` ASC, `id_asco_entry` ASC'
            );
            if (!is_array($rows)) {
                return [];
            }

            $out = [];
            foreach ($rows as $row) {
                $out[] = [
                    'cookie_name' => $row['cookie_name'],
                    'provider' => $row['provider'],
                    'purpose' => $row['purpose_' . $lang],
                    'expiration' => $row['expiration'],
                    'domain' => $row['domain'],
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param int $idCategory
     *
     * @return int next free position within the category
     */
    public static function getNextPosition($idCategory)
    {
        try {
            $max = (int) Db::getInstance()->getValue(
                'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'asco_entry`
                 WHERE `id_asco_category` = ' . (int) $idCategory
            );

            return $max + 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * @see ObjectModel::add()
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->position)) {
            $this->position = self::getNextPosition((int) $this->id_asco_category);
        }

        return parent::add($auto_date, $null_values);
    }
}
