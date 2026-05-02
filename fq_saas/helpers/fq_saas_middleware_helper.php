<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('fq_saas_get_days_until')) {
    require_once __DIR__ . '/fq_saas_helper.php';
}

/**
 * Tenant Middleware function to handle tenant-related checks.
 *
 * This function performs various checks and validations for the tenant.
 * It checks if the tenant has an unpaid invoice, if the tenant is active,
 * if the requested module is allowed for the tenant, and if the requested controller
 * is restricted for the tenant. It also handles restricted routes in the settings controller.
 *
 * @throws Exception Throws an exception if an error occurs or if access is denied.
 */
function fq_saas_tenant_middleware()
{
    if (fq_saas_is_tenant()) {
        $tenant = fq_saas_tenant();
        if ($tenant) {
            // Get the current CodeIgniter instance
            $ci = &get_instance();

            if (!class_exists('Invoices_model', false)) {
                $ci->load->model('invoices_model');
            }

            $invoice = isset($tenant->package_invoice) ? $tenant->package_invoice : null;

            if (!$invoice || ($invoice && $invoice->status == Invoices_model::STATUS_CANCELLED)) {
                fq_saas_middleware_force_load_lang();
                fq_saas_show_tenant_error(
                    _l('fq_saas_permission_denied_mid'),
                    '<a href="' . fq_saas_default_base_url('clients/?subscription') . '" class="text-red-600">' . _l('fq_saas_no_invoice_client_for_client_mid') . '</a>',
                    403,
                    '404',
                    'invoice_not_fit'
                );
            }

            // Check for trial validity
            $on_trial = fq_saas_invoice_is_on_trial($invoice);
            if ($on_trial) {
                if ((int)fq_saas_get_days_until($invoice->duedate) <= 0) {

                    // Check for custom URL
                    $custom_trial_end_url = $tenant->saas_options['fq_saas_trial_expire_page_url'] ?? '';
                    if (!empty($custom_trial_end_url) && stripos($custom_trial_end_url, '://') !== false) {
                        $package_col = fq_saas_column('packageid');
                        $extra_info = "slug={$tenant->slug}&company={$tenant->name}&package={$invoice->name}&invoice_id={$invoice->id}&package_id={$invoice->$package_col}";
                        $custom_trial_end_url = strpos($custom_trial_end_url, '?') === false ? $custom_trial_end_url . '?' . $extra_info : $custom_trial_end_url . '&' . $extra_info;
                        header("Location: $custom_trial_end_url");
                        exit;
                    }

                    fq_saas_middleware_force_load_lang();
                    fq_saas_show_tenant_error(
                        _l('fq_saas_trial_invoice_over_not_mid'),
                        _l('fq_saas_trial_invoice_over_not_mid_body', [$invoice->name])
                            . '<br/>'
                            . '<a class="tw-my-5 text-white bg-red-600 py-2 px-2 my-2 inline-block rounded-lg" href="'
                            . fq_saas_default_base_url('clients/?subscription') . '">'
                            . _l('fq_saas_click_here_to_subscribe') . '</a>',
                        403,
                        '404',
                        'trial_invoice_over'
                    );
                }
            }

            // Check for an unpaid invoice
            if ($invoice && !$invoice->is_private && fq_saas_is_invoice_overdue_for_payment($invoice)) {
                $invoice_pay_endpoint = fq_saas_get_invoice_payment_endpoint($invoice);
                $payment_url = fq_saas_default_base_url($invoice_pay_endpoint);
                if (isset($_GET['paying_outstanding'])) {
                    header("Location: $payment_url");
                    exit;
                }

                fq_saas_middleware_force_load_lang();
                fq_saas_show_tenant_error(
                    _l('fq_saas_clear_unpaid_invoice_mid'),
                    _l('fq_saas_clear_unpaid_invoice_message_mid')
                        . '<br/>'
                        . '<a class="tw-my-5 text-white bg-red-600 py-2 px-2 my-2 inline-block rounded-lg" href="'
                        . $payment_url . '">'
                        . _l('fq_saas_clear_invoice_btn') . '</a>',
                    400,
                    '404',
                    'unpaid_invoice'
                );
            }

            // Check if the tenant is active
            if ($tenant->status != 'active') {
                fq_saas_middleware_force_load_lang();
                fq_saas_show_tenant_error(
                    ucfirst(_l('fq_saas_' . $tenant->status)),
                    _l('fq_saas_company_not_active_mid') . ' <a class="text-blue-600 text-bold tw-font-bold" href="' . fq_saas_default_base_url('clients/tickets?portal-message=' . $tenant->status) . '">' . _l('fq_saas_clich_here') . '</a>',
                    400,
                    '404',
                    'inactive_tenant'
                );
            }

            // Get the list of modules allowed for the tenant
            $modules = fq_saas_tenant_modules($tenant, false, false, false, true);

            // Get the active module and controller
            $activeModule = $ci->router->fetch_module();
            $controller = $ci->router->fetch_class();
            $method = $ci->router->fetch_method();

            // Check if the controller is 'settings'
            if ($controller === 'settings') {
                // Disable route for update|info from tenant setting
                if (in_array($ci->input->get('group'), ['update', 'info'])) {
                    fq_saas_middleware_force_load_lang();
                    fq_saas_show_tenant_error(
                        _l('fq_saas_permission_denied_mid'),
                        _l('fq_saas_restricted_settings_group_mid'),
                        403,
                        '404',
                        'system'
                    );
                }
            }

            // Check if the active module is allowed for the tenant
            $exempted = ($activeModule === FQ_SAAS_MODULE_NAME &&
                (
                    ($controller === 'companies' && $method === 'client_portal_bridge' && (fq_saas_tenant_is_enabled('client_bridge') || fq_saas_tenant_is_enabled('instance_switch'))) ||
                    ($controller === 'authentication' && $method === 'tenant_admin_magic_auth') ||
                    ($controller === 'tenant_modules_page')
                )
            );

            if ($activeModule && !in_array($activeModule, $modules) && !$exempted) {
                fq_saas_middleware_force_load_lang();
                fq_saas_show_tenant_error(
                    _l('fq_saas_permission_denied_mid'),
                    _l('fq_saas_restricted_module_mid'),
                    403,
                    '404',
                    'restricted'
                );
            }

            // Check if the controller is restricted
            $restricted_classes = ['mods'];
            if (in_array($controller, $restricted_classes)) {
                fq_saas_middleware_force_load_lang();
                $ci->session->set_flashdata('message-danger', _l('fq_saas_permission_denied_mid'));
                fq_saas_redirect_back();
                exit();
            }

            // Check if the default module is allowed for the tenant
            $disabled_default_modules = fq_saas_tenant_disabled_default_modules($tenant);
            if ($controller === 'reports' && $method === 'knowledge_base_articles') {
                $method = 'knowledge_base';
            }
            if (in_array($controller, $disabled_default_modules) || ($controller === 'clients' && in_array($method, $disabled_default_modules)) || ($controller === 'reports' && in_array($method, $disabled_default_modules))) {
                fq_saas_middleware_force_load_lang();
                fq_saas_show_tenant_error(
                    _l('fq_saas_permission_denied_mid'),
                    _l('fq_saas_restricted_module_mid'),
                    403,
                    '404',
                    'restricted'
                );
            }
        }
    }
}

/**
 * Filter modules to be loaded for the tenant.
 * It check with saas and remove modules that should not be loaded for the customer.
 * Succession of fq_saas_load_tenant_modules() method
 * @param array $activated_modules
 * @return array
 */
function  fq_saas_filter_tenant_loadable_modules($activated_modules)
{
    // Get the current tenant
    $tenant = fq_saas_tenant();

    if ($tenant && !empty($tenant->slug)) {

        // Get the list of modules allowed for the tenant
        $tenant_modules = fq_saas_tenant_modules($tenant, true, false, false);
        $global_extensions = fq_saas_registered_extensions($tenant, true);

        foreach ($activated_modules as $name => $module) {
            if (!in_array($name, $tenant_modules) || $name === FQ_SAAS_MODULE_NAME)
                unset($activated_modules[$name]);
        }

        // Load the SaaS module before any other modules for the tenant
        $file = APP_MODULES_PATH . FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '.php';
        require_once($file);

        // Load all Perfex SaaS module extensions
        foreach ($global_extensions as $ext_name) {
            // Check if have dedicated file
            $file = APP_MODULES_PATH . $ext_name . '/' . $ext_name . '_fq_saas.php';
            if (file_exists($file)) {
                require_once($file);
            } else if (!in_array($ext_name, $activated_modules)) {
                $file = APP_MODULES_PATH . $ext_name . '/' . $ext_name . '.php';
                if (file_exists($file))
                    require_once($file);
            }
        }
    }

    return $activated_modules;
}

/**
 * Attach Hooks function to register and attach hooks for specific actions.
 *
 * This function registers hooks for various actions and attaches the corresponding
 * middleware or module loading functions to those hooks.
 */
function fq_saas_attach_hooks()
{
    $fq_saas_hooks = function_exists('hooks') ? hooks() : null;
    if (!$fq_saas_hooks) {
        return;
    }

    // Register hooks for middleware
    $fq_saas_hooks->add_action('app_init',  'fq_saas_tenant_middleware');

    // Register hook for module loading filter
    $fq_saas_hooks->add_filter('modules_to_load', 'fq_saas_filter_tenant_loadable_modules', PHP_INT_MAX);

    // Override app_modules
    $fq_saas_hooks->add_filter('modules_to_load', function ($activated_modules) {
        $CI = &get_instance();
        $CI->load->library(FQ_SAAS_MODULE_NAME . '/saas_app_modules');
        $CI->app_modules = $CI->saas_app_modules;
        return $activated_modules;
    });
}


/**
 * Perfex SAAS Middleware function.
 *
 * This function serves as a middleware entry point for Perfex SAAS. It calls the
 * `fq_saas_attach_hooks()` function to register and attach hooks for various actions.
 */
function fq_saas_middleware()
{
    fq_saas_attach_hooks();

    // Ensure db prefix constant defined (my_routes can run before app-config defines APP_DB_*_DEFAULT; app_init applies tenant DB then).
    $defaultsReady = defined('APP_DB_HOSTNAME_DEFAULT')
        && defined('APP_DB_USERNAME_DEFAULT')
        && defined('APP_DB_PASSWORD_DEFAULT')
        && defined('APP_DB_NAME_DEFAULT');
    if (fq_saas_is_tenant() && !defined('APP_DB_PREFIX') && $defaultsReady) {
        fq_saas_middleware_force_load_lang();
        fq_saas_show_tenant_error(
            _l('fq_saas_permission_denied_mid'),
            "Invalid initialization",
            403,
            '404',
            'system'
        );
    }
}

/**
 * Forces the loading of language files related to the Perfex SaaS module.
 * 
 * Checks if it's necessary to load the language files related to the Perfex SaaS module.
 * If the function 'register_language_files' doesn't exist, it loads the 'module' helper.
 * It then registers the language files for the Perfex SaaS module and triggers the 'after_load_admin_language' action hook.
 *
 * @return void
 */
function fq_saas_middleware_force_load_lang()
{
    // Check if it's necessary to load the language
    if (_l(FQ_SAAS_MODULE_NAME) == FQ_SAAS_MODULE_NAME) {

        if (!function_exists('register_language_files'))
            get_instance()->load->helper('module');

        register_language_files(FQ_SAAS_MODULE_NAME, [FQ_SAAS_MODULE_NAME]);
        if (function_exists('hooks') && hooks()) {
            hooks()->do_action('after_load_admin_language');
        }
    }
}

/**
 * Check if invoice requires or due for payment.
 * Should be used in blocking access to instances based on the global saas settings 
 *
 * @param object $invoice
 * @return bool
 */
function fq_saas_is_invoice_overdue_for_payment($invoice)
{
    if (!isset($invoice->status)) return false;

    $CI = &get_instance();
    if (!class_exists('Invoices_model', false)) {
        $CI->load->model('invoices_model');
    }

    $settings_key = 'fq_saas_require_invoice_payment_status';
    $payment_access_statuses = fq_saas_is_tenant() ? (fq_saas_tenant()->saas_options[$settings_key] ?? []) : get_option($settings_key);
    if (is_string($payment_access_statuses)) {
        $payment_access_statuses = (array)json_decode($payment_access_statuses);
    }

    $payment_access_statuses = array_filter($payment_access_statuses);
    if (empty($payment_access_statuses)) {
        $payment_access_statuses = [Invoices_model::STATUS_OVERDUE];
    }

    if (in_array($invoice->status, $payment_access_statuses))
        return true;

    // Always check for overdue as default even when status are empty
    if (!function_exists('is_invoice_overdue'))
        $CI->load->helper('invoices');

    return is_invoice_overdue($invoice);
}
