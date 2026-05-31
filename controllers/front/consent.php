<?php
/**
 * APLINE Simple Cookies module for PrestaShop 9.
 *
 * AJAX endpoint that records a visitor's consent decision and returns the list
 * of third-party scripts that may now load. Always responds with JSON and
 * never throws a 500 to the visitor.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AplineSimpleCookiesConsentModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $ajax = true;

    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $decision = json_decode((string) Tools::getValue('decision'), true);
            $source = (string) Tools::getValue('source');

            if (!is_array($decision) || !in_array($source, ['banner', 'prefs', 'footer_link', 'gpc'], true)) {
                http_response_code(400);
                die(json_encode(['ok' => false, 'error' => 'bad_request']));
            }

            $clean = $this->module->saveConsent($decision, $source);

            die(json_encode([
                'ok' => true,
                'decision' => $clean,
                'scripts_to_load' => $this->module->scriptsForGrantedConsent($clean),
            ]));
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_simple_cookies consent: ' . $e->getMessage(), 3);
            http_response_code(500);
            die(json_encode(['ok' => false, 'error' => 'internal']));
        }
    }
}
