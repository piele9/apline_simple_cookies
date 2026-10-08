<?php
/**
 * Upgrade to 1.2.0 — Polish interface, large panel buttons, banner colours
 * moved from front.css to the module configuration.
 *
 * - Adds the 16 ASCO_COLOR_* keys with the palette that was hard-coded in
 *   front.css up to 1.1.0, so an upgraded shop keeps exactly the same banner
 *   look (only when a key does not exist yet; an existing value is never
 *   overwritten). New installs get the neutral defaults instead.
 * - Renames the two hidden admin tabs to Polish in every language.
 * - Leaves the banner copy (ASCO_COPY_*) untouched: the shop keeps its texts.
 *
 * The palette is a snapshot on purpose (not read from the module class), so
 * this script keeps working when later versions change their own constants.
 *
 * @author    Arkadiusz Pielechowski
 * @copyright Arkadiusz Pielechowski
 * @license   MIT - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param apline_simple_cookies $module
 *
 * @return bool
 */
function upgrade_module_1_2_0($module)
{
    $colorsBefore120 = [
        'ASCO_COLOR_LIGHT_BG' => '#ffffff',
        'ASCO_COLOR_LIGHT_TEXT' => '#1d2b36',
        'ASCO_COLOR_LIGHT_MUTED' => '#5b6b78',
        'ASCO_COLOR_LIGHT_BORDER' => '#e0e4e8',
        'ASCO_COLOR_LIGHT_PRIMARY' => '#2d7a46',
        'ASCO_COLOR_LIGHT_PRIMARY_TEXT' => '#ffffff',
        'ASCO_COLOR_LIGHT_SECONDARY' => '#eef1f4',
        'ASCO_COLOR_LIGHT_SECONDARY_TEXT' => '#1d2b36',
        'ASCO_COLOR_DARK_BG' => '#1d2530',
        'ASCO_COLOR_DARK_TEXT' => '#f3f5f7',
        'ASCO_COLOR_DARK_MUTED' => '#aeb9c4',
        'ASCO_COLOR_DARK_BORDER' => '#33404e',
        'ASCO_COLOR_DARK_PRIMARY' => '#4cae6a',
        'ASCO_COLOR_DARK_PRIMARY_TEXT' => '#0d1b12',
        'ASCO_COLOR_DARK_SECONDARY' => '#2b3744',
        'ASCO_COLOR_DARK_SECONDARY_TEXT' => '#f3f5f7',
    ];

    $ok = true;
    foreach ($colorsBefore120 as $key => $value) {
        if (Configuration::get($key) === false) {
            $ok = Configuration::updateValue($key, $value) && $ok;
        }
    }

    // Polish names of the hidden admin tabs (stored in the database). A failed
    // rename is cosmetic, so it is logged and never blocks the upgrade.
    $tabNames = [
        'AdminAplineSimpleCookiesCategory' => 'Kategorie cookies',
        'AdminAplineSimpleCookiesEntry' => 'Pliki cookies',
    ];
    foreach ($tabNames as $className => $label) {
        try {
            $idTab = (int) Tab::getIdFromClassName($className);
            if (!$idTab) {
                continue;
            }
            $tab = new Tab($idTab);
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $label;
            }
            $tab->update();
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies upgrade 1.2.0 (tab ' . $className . '): ' . $e->getMessage(), 2);
        }
    }

    return $ok;
}
