<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * ObjectModel for a cookie category (necessary / functional / analytics /
 * marketing, or any custom one the admin adds). Bilingual columns (PL + EN)
 * are stored directly on the row rather than via PrestaShop multilang, so the
 * admin edits both languages on one screen.
 *
 * @author    Arkadiusz Pielechowski
 * @copyright Arkadiusz Pielechowski
 * @license   MIT - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AplineSimpleCookiesCategory extends ObjectModel
{
    /** @var string */
    public $slug;
    /** @var string */
    public $name_pl;
    /** @var string */
    public $name_en;
    /** @var string */
    public $description_pl;
    /** @var string */
    public $description_en;
    /** @var bool */
    public $is_necessary;
    /** @var bool */
    public $active;
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
        'table' => 'asco_category',
        'primary' => 'id_asco_category',
        'multilang' => false,
        'fields' => [
            'slug' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
            'name_pl' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'name_en' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'description_pl' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'description_en' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'is_necessary' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Active categories ordered by position, normalized to one language, with
     * their cookie entries attached. Guarded so a missing table never breaks
     * the shop front.
     *
     * @param string $lang 'pl' or 'en'
     *
     * @return array
     */
    public static function getActiveCategories($lang = 'pl')
    {
        $lang = ($lang === 'en') ? 'en' : 'pl';

        try {
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'asco_category`
                 WHERE `active` = 1
                 ORDER BY `position` ASC, `id_asco_category` ASC'
            );
            if (!is_array($rows)) {
                return [];
            }

            $out = [];
            foreach ($rows as $row) {
                $out[] = [
                    'id' => (int) $row['id_asco_category'],
                    'slug' => $row['slug'],
                    'name' => $row['name_' . $lang],
                    'description' => $row['description_' . $lang],
                    'is_necessary' => (int) $row['is_necessary'],
                    'entries' => AplineSimpleCookiesEntry::getByCategory((int) $row['id_asco_category'], $lang),
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Number of categories flagged necessary, optionally excluding one id.
     *
     * @param int $excludeId
     *
     * @return int
     */
    public static function countNecessary($excludeId = 0)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'asco_category` WHERE `is_necessary` = 1';
            if ($excludeId > 0) {
                $sql .= ' AND `id_asco_category` <> ' . (int) $excludeId;
            }

            return (int) Db::getInstance()->getValue($sql);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Whether a slug is already used by another category.
     *
     * @param string $slug
     * @param int    $excludeId
     *
     * @return bool
     */
    public static function slugExists($slug, $excludeId = 0)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'asco_category`
                    WHERE `slug` = "' . pSQL($slug) . '"';
            if ($excludeId > 0) {
                $sql .= ' AND `id_asco_category` <> ' . (int) $excludeId;
            }

            return (int) Db::getInstance()->getValue($sql) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return int next free position
     */
    public static function getNextPosition()
    {
        try {
            $max = (int) Db::getInstance()->getValue(
                'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'asco_category`'
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
            $this->position = self::getNextPosition();
        }

        return parent::add($auto_date, $null_values);
    }
}
