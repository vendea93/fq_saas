<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This code fixes first time installation issue where error 500 might be experienced on some setup immediately after fresh install
if (!defined('FQ_SAAS_MODULE_NAME') && !isset($_GET['reload'])) {
    $url = ($_SERVER['REQUEST_URI'] ?? '');
    $url = (empty($_GET) ? explode('?', $url)[0] . '?reload=1' : $url . '&reload=1');
    header('Location: ' . $url);
    exit();
}

// Routes can load before fq_saas.php; core helpers must exist before fq_saas_parse_dsn / fq_saas_is_tenant below.
if (!function_exists('fq_saas_parse_dsn')) {
    require_once __DIR__ . '/../helpers/fq_saas_core_helper.php';
}

// Include the middlewares
require_once(__DIR__ . '/../helpers/fq_saas_middleware_helper.php');

/**
 * Detect the global tenant and define the database credential or use default db credentials.
 * Safe to call multiple times; no-ops until APP_DB_*_DEFAULT exist (my_routes can load before app-config.php).
 */
function fq_saas_apply_tenant_db_constants(): void
{
    static $applied = false;
    if ($applied) {
        return;
    }
    if (
        !defined('APP_DB_HOSTNAME_DEFAULT')
        || !defined('APP_DB_USERNAME_DEFAULT')
        || !defined('APP_DB_PASSWORD_DEFAULT')
        || !defined('APP_DB_NAME_DEFAULT')
    ) {
        return;
    }

    $GLOBALS['_encryption'] = load_class('Encryption');
    $dsn = ['host' => '', 'user' => '', 'password' => '', 'dbname' => ''];

    if (isset($GLOBALS[FQ_SAAS_MODULE_NAME . '_tenant'])) {
        try {
            $raw = $GLOBALS[FQ_SAAS_MODULE_NAME . '_tenant']->dsn ?? '';
            if (!empty($raw)) {
                $decrypted = $GLOBALS['_encryption']->decrypt($raw);
                $GLOBALS[FQ_SAAS_MODULE_NAME . '_tenant']->dsn = $decrypted;

                if (!empty($decrypted)) {
                    $dsn = (array)fq_saas_parse_dsn($decrypted);
                }
            }
        } catch (\Throwable $e) {
            // Fallback to default DB constants when DSN is empty/invalid.
            // This is important for multitenancy setups where the DSN field can be blank.
            if (function_exists('log_message')) {
                log_message('error', 'fq_saas_apply_tenant_db_constants: invalid DSN - ' . $e->getMessage());
            }
        }
    }

    if (!defined('APP_DB_HOSTNAME')) {
        define('APP_DB_HOSTNAME', empty($dsn['host']) ? APP_DB_HOSTNAME_DEFAULT : $dsn['host']);
    }
    if (!defined('APP_DB_USERNAME')) {
        define('APP_DB_USERNAME', empty($dsn['user']) ? APP_DB_USERNAME_DEFAULT : $dsn['user']);
    }
    if (!defined('APP_DB_PASSWORD')) {
        define('APP_DB_PASSWORD', empty($dsn['password']) ? APP_DB_PASSWORD_DEFAULT : $dsn['password']);
    }
    if (!defined('APP_DB_NAME')) {
        define('APP_DB_NAME', empty($dsn['dbname']) ? APP_DB_NAME_DEFAULT : $dsn['dbname']);
    }
    $GLOBALS['fq_saas_db_override'] = [
        'hostname' => empty($dsn['host']) ? APP_DB_HOSTNAME_DEFAULT : $dsn['host'],
        'username' => empty($dsn['user']) ? APP_DB_USERNAME_DEFAULT : $dsn['user'],
        'password' => empty($dsn['password']) ? APP_DB_PASSWORD_DEFAULT : $dsn['password'],
        'database' => empty($dsn['dbname']) ? APP_DB_NAME_DEFAULT : $dsn['dbname'],
    ];
    if (fq_saas_is_tenant() && !defined('APP_DB_PREFIX')) {
        define('APP_DB_PREFIX', fq_saas_tenant_db_prefix(fq_saas_tenant_slug()));
    }
    if (
        fq_saas_is_tenant()
        && defined('FQ_SAAS_TENANT_DB_NAME_PREFIX')
        && FQ_SAAS_TENANT_DB_NAME_PREFIX !== ''
        && !empty($GLOBALS['fq_saas_db_override']['database'])
    ) {
        $GLOBALS['fq_saas_db_override']['database'] = FQ_SAAS_TENANT_DB_NAME_PREFIX . $GLOBALS['fq_saas_db_override']['database'];
    }

    $applied = true;
}

fq_saas_apply_tenant_db_constants();

$fq_saas_hooks = function_exists('hooks') ? hooks() : null;
if ($fq_saas_hooks) {
    $fq_saas_hooks->add_action('app_init', 'fq_saas_apply_tenant_db_constants', PHP_INT_MIN);
}

// Run middlewares for the tenant. i.e permission and module control. Also add important hooks.
fq_saas_middleware();




/******************* EARLY TIME RQUIRED HOOKS **********************************/
/**
 * Early time hooks for email template.
 * Must be placed here in hooks to ensure its loaded with perfex email template loading.
 */
$fq_saas_hooks = function_exists('hooks') ? hooks() : null;
if ($fq_saas_hooks) {
    $fq_saas_hooks->add_filter('register_merge_fields', 'fq_saas_email_template_merge_fields');
}
function fq_saas_email_template_merge_fields($fields)
{
    $fields[] =  'fq_saas/merge_fields/fq_saas_company_merge_fields';
    return $fields;
}

/**
 * Media file folder.
 * Set max number for priority to ensure the function is more or less the last to be called.
 * However, we nee to set the hook in early part of execution to ensure its availability to other script using media folder.
 */
$fq_saas_hooks = function_exists('hooks') ? hooks() : null;
if ($fq_saas_hooks) {
    $fq_saas_hooks->add_filter('get_media_folder', 'fq_saas_set_media_folder_hook', PHP_INT_MAX);
}
function fq_saas_set_media_folder_hook($data)
{
    $tenant_slug = fq_saas_is_tenant() ? fq_saas_tenant_slug() : fq_saas_master_tenant_slug();
    if (empty($tenant_slug)) throw new \Exception("Media Error: Error Processing Request", 1);

    return $data . '/' . $tenant_slug;
}

/********OTHER MIDDLEWARE SPECIFIC HOOKS ******/
$folder_path = __DIR__ . '/my_hooks/';
$feature_hook_files = glob($folder_path . '*.php');
if ($fq_saas_hooks) {
    $feature_hook_files = $fq_saas_hooks->apply_filters('fq_saas_extra_middleware_hook_files', $feature_hook_files);
}
foreach ($feature_hook_files as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}
