<?php
/**
 * Upgrade to 1.1.0 — introduces Google Consent Mode v2.
 *
 * Adds the ASCO_CONSENT_MODE_V2 configuration key (default: enabled) for
 * shops that installed the module before this key existed. Nothing else
 * changes: the fputcsv() PHP 8.4 fix and the consent-mode script logic
 * ship in the module code itself and need no data migration.
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
function upgrade_module_1_1_0($module)
{
    if (Configuration::get('ASCO_CONSENT_MODE_V2') === false) {
        return Configuration::updateValue('ASCO_CONSENT_MODE_V2', 1);
    }

    return true;
}
