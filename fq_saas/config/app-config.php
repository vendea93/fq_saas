<?php

defined('BASEPATH') or exit('No direct script access allowed');

try {

    // Allow room for config customization and constants for saas
    $saas_custom_config_file = APPPATH . 'config/my_saas_config.php';
    if (file_exists($saas_custom_config_file))
        include_once($saas_custom_config_file);

    /**
     * Require the root helper file (CI-independent early helpers).
     * If the module was partially deleted, skip SaaS so application/config/app-config.php can continue
     * (recovery, uninstall, or normal operation without the module folder).
     */
    $fq_saas_core_helper = __DIR__ . '/../helpers/fq_saas_core_helper.php';
    if (!function_exists('fq_saas_init')) {
        if (!is_file($fq_saas_core_helper)) {
            return;
        }
        if (!include_once $fq_saas_core_helper) {
            throw new \Exception('Error loading FQ SAAS core helper', 1);
        }
    }

    /**
     * Init perfex saas and detect the active tenant if any
     * This method call with set $GLOBALS[FQ_SAAS_MODULE_NAME . '_tenant'] and can be used henceforth as session is not ready for use here.
     */
    fq_saas_init();


    /**
     * SaaS Initiated. Load tenants relative contstants including storage control.
     * We need this to ensure control of storage constant definitions
     */
    $fq_saas_my_constants = __DIR__ . '/my_constants.php';
    if (!defined('APP_MODULES_PATH') && is_file($fq_saas_my_constants) && !include_once $fq_saas_my_constants) {
        throw new \Exception('Error loading FQ SAAS my_constants file', 1);
    }

    // bootstraping successful
} catch (\Throwable $th) {

    exit('SaaS bootstrapping error: ' . $th->getMessage() . '<br/><br/>' . $th->getTraceAsString());
}
