<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Ensure module constants exist before middleware (avoids first-request race when routes load before fq_saas.php).
if (!defined('FQ_SAAS_MODULE_NAME')) {
    require_once __DIR__ . '/constants.php';
}

require_once __DIR__ . '/../helpers/fq_saas_php8_polyfill_helper.php';

require_once('middleware_hooks.php');

// Must match application/config/constants.php (ADMIN_URI / CUSTOM_ADMIN_URL) — same segment as admin_url() uses, or admin SaaS URLs 404.
$fq_saas_admin_uri = (defined('ADMIN_URI') && (string) ADMIN_URI !== '') ? trim((string) ADMIN_URI, '/') : 'admin';
if ($fq_saas_admin_uri === '') {
    $fq_saas_admin_uri = 'admin';
}

if (fq_saas_is_tenant()) {

    $tenant = fq_saas_tenant();

    $route[$fq_saas_admin_uri . '/billing/my_account'] = 'fq_saas/admin/companies/client_portal_bridge';

    $route['billing/my_account/magic_auth'] = 'fq_saas/authentication/tenant_admin_magic_auth';

    // Core Perfex still registers admin/modules as "admin/..."; custom ADMIN_URI may differ — unset both.
    foreach (array_unique(['admin', $fq_saas_admin_uri]) as $fq_saas_adm) {
        unset($route[$fq_saas_adm . '/modules'], $route[$fq_saas_adm . '/modules/(:any)'], $route[$fq_saas_adm . '/modules/(:any)/(:any)']);
    }

    $route[$fq_saas_admin_uri . '/apps/modules'] = 'fq_saas/admin/tenant_modules_page';
    $route[$fq_saas_admin_uri . '/apps/modules/(:any)'] = 'fq_saas/admin/tenant_modules_page/$1';
    $route[$fq_saas_admin_uri . '/apps/modules/(:any)/(:any)/(:any)'] = 'fq_saas/admin/tenant_modules_page/$1/$2/$3';

    // Ensure this custom routes is defined if the tenant is identified by request uri segment
    if ($tenant->http_identification_type === FQ_SAAS_TENANT_MODE_PATH) {
        $tenant_slug = $tenant->slug;
        $tenant_route_sig = fq_saas_tenant_url_signature($tenant_slug); //i.e $tenant_route_sig

        // Clone existing static routes with saas id prefix
        foreach ($route as $key => $value) {
            $new_key = fq_saas_tenant_url_signature($tenant_slug) . "/" . ($key == '/' ? '' : $key);
            $route[$new_key] = $value;
        }
    }
}

/*
 * FQ SaaS admin + API routes — registered for master and tenant.
 * Previously these lived only inside if (!fq_saas_is_tenant()), so any false-positive tenant detection
 * or tenant admin hitting admin/fq_saas/* caused 404. ADMIN_URI fixes mismatch with admin_url().
 */
$a = $fq_saas_admin_uri;
$route[$a . '/perfex_saas/pricing'] = 'fq_saas/admin/packages/pricing';
$route[$a . '/perfex_saas/(:any)'] = 'fq_saas/admin/$1';
$route[$a . '/perfex_saas/(:any)/(:any)'] = 'fq_saas/admin/$1/$2';
$route[$a . '/perfex_saas/(:any)/(:any)/(:any)'] = 'fq_saas/admin/$1/$2/$3';
$route[$a . '/perfex_saas/(:any)/(:any)/(:any)/(:any)'] = 'fq_saas/admin/$1/$2/$3/$4';

$route[$a . '/' . FQ_SAAS_ROUTE_NAME . '/pricing'] = 'fq_saas/admin/packages/pricing';
$route[$a . '/' . FQ_SAAS_ROUTE_NAME . '/(:any)'] = 'fq_saas/admin/$1';
$route[$a . '/' . FQ_SAAS_ROUTE_NAME . '/(:any)/(:any)'] = 'fq_saas/admin/$1/$2';
$route[$a . '/' . FQ_SAAS_ROUTE_NAME . '/(:any)/(:any)/(:any)'] = 'fq_saas/admin/$1/$2/$3';
$route[$a . '/' . FQ_SAAS_ROUTE_NAME . '/(:any)/(:any)/(:any)/(:any)'] = 'fq_saas/admin/$1/$2/$3/$4';

$route[FQ_SAAS_ROUTE_NAME . '/api/(:any)'] = 'fq_saas/api/api/$1';
$route[FQ_SAAS_ROUTE_NAME . '/api/(:any)/(:any)'] = 'fq_saas/api/api/$1/$2';
$route[FQ_SAAS_ROUTE_NAME . '/api/(:any)/(:any)/(:any)'] = 'fq_saas/api/api/$1/$2/$3';

if (!fq_saas_is_tenant()) {

    /** Landing page handling */
    $landing_options = fq_saas_get_options(['fq_saas_landing_page_url', 'fq_saas_landing_builtin_slug']);
    $landing_page_url = $landing_options['fq_saas_landing_page_url'] ?? '';
    $builtin_slug      = trim((string) ($landing_options['fq_saas_landing_builtin_slug'] ?? ''));
    if ($builtin_slug !== '' && (empty($landing_page_url) || !filter_var($landing_page_url, FILTER_VALIDATE_URL))) {
        $route['/']                  = 'fq_saas/landing/builtin/' . $builtin_slug;
        $route['default_controller'] = 'fq_saas/landing/builtin/' . $builtin_slug;
        $route['404_override']       = 'fq_saas/landing/show_404';

        hooks()->add_action('after_contact_login', function () {
            $CI = &get_instance();
            if (!$CI->session->has_userdata('red_url')) {
                $CI->session->set_userdata([
                    'red_url' => site_url('clients/'),
                ]);
            }
        });
    } elseif ($landing_page_url && filter_var($landing_page_url, FILTER_VALIDATE_URL)) {
        $method = 'proxy';
        $route['/'] = 'fq_saas/landing/' . $method;
        $route['default_controller'] = 'fq_saas/landing/' . $method;
        $route['404_override']         = 'fq_saas/landing/show_404';

        // ensure the user is redirected to client portal after logging in and not landing page
        hooks()->add_action('after_contact_login', function () {
            $CI = &get_instance();
            if (!$CI->session->has_userdata('red_url')) {
                $CI->session->set_userdata([
                    'red_url' => site_url('clients/'),
                ]);
            }
        });
    }
    /** Ends Landing page handling */

    // Client routes
    $route['clients/packages/(:any)/select'] = 'fq_saas/fq_saas_client/subscribe/$1';
    $route['clients/my_account'] = 'fq_saas/fq_saas_client/my_account';
    $route['clients/my_account/cancel_subscription'] = 'fq_saas/fq_saas_client/cancel_saas_subscription';
    $route['clients/my_account/resume_subscription'] = 'fq_saas/fq_saas_client/resume_saas_subscription';
    $route['clients/companies'] = 'fq_saas/fq_saas_client/companies';
    $route['clients/companies/(:any)'] = 'fq_saas/fq_saas_client/$1';
    $route['clients/companies/(:any)/(:any)'] = 'fq_saas/fq_saas_client/$1/$2';

    $route['clients/ps_magic/(:any)'] = 'fq_saas/authentication/magic_auth/$1';
    $route['clients/ps_magic/(:any)/(:any)'] = 'fq_saas/authentication/magic_auth/$1/$2';

    $route['billing/my_account/magic_auth'] = 'fq_saas/authentication/client_magic_auth';
}
