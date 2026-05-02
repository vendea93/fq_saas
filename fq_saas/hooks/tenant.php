<?php

defined('BASEPATH') or exit('No direct script access allowed');

/********TENANT/INSTANCE SPECIFIC HOOKS ******/
if ($is_tenant) {
    // Call the function to register the limitation filters
    fq_saas_register_limitation_filters();

    // Override instant options with shared option in package where applicable
    fq_saas_init_shared_options();

    /**
     * Hook for tenant instance settings page.
     * It attempt to mask settings and  removes upgrade and system info links.
     *
     * @return void
     */
    hooks()->add_action('settings_group_end', 'fq_saas_mask_buffer_content');

    // Prevent saving enforced shared fields by overriding with master value
    hooks()->add_filter('before_settings_updated', 'fq_saas_before_settings_updated_hook');
    function fq_saas_before_settings_updated_hook($data)
    {
        $tenant = fq_saas_tenant();
        $is_demo_instance = function_exists('fq_saas_tenant_is_demo_instance') && fq_saas_tenant_is_demo_instance();
        if (!$is_demo_instance) {
            $tenant_slug = strtolower((string) ($tenant->slug ?? ''));
            $demo_like_slugs = [
                'demo',
                'beauty',
                'hotel',
                'warsztat',
                'nieruchomosc',
                'nieruchomosci',
                'logistyka',
                'ecommerce',
                'kursy',
                'serwiswww',
                'oze',
                'agencja',
                'rekrutacja',
                'medycyna',
                'eventy',
                'gastronomia',
            ];
            $is_demo_instance = ((int) ($tenant->clientid ?? 0) === 3) || in_array($tenant_slug, $demo_like_slugs, true);
        }

        // Enforced fields
        $enforced_fields = array_merge(FQ_SAAS_ENFORCED_SHARED_FIELDS, (array) ($tenant->package_invoice->metadata->shared_settings->enforced ?? []));

        // In demo instances allow tenant admin to update branding assets from settings.
        // Without this, settings save flow reverts logo/favicon back to master values.
        if ($is_demo_instance) {
            $enforced_fields = array_values(array_filter($enforced_fields, function ($field) {
                return !in_array($field, ['company_logo', 'company_logo_dark', 'favicon'], true);
            }));
        }

        $enforced_settings = fq_saas_master_shared_settings($enforced_fields);
        foreach ($enforced_settings as $setting) {

            $name = $setting->name;
            $val = $setting->value;

            // Consider some special settings case . See application/models/Settings_model.php for more info
            if ($name == 'default_contact_permissions' && is_string($val) && !empty($val)) {
                $val = unserialize($val);
            } elseif ($name == 'lead_unique_validation' && is_string($val) && !empty($val)) {
                $val = json_decode($val);
            } elseif ($name == 'visible_customer_profile_tabs' && is_string($val) && !empty($val) && $val !== 'all') {
                $val = unserialize($val);
            } elseif ($name == 'default_tax' && is_string($val) && !empty($val)) {
                $val = unserialize($val);
            } elseif ($name == 'staff_notify_completed_but_not_billed_tasks' || $name == 'reminder_for_completed_but_not_billed_tasks_days') {
                if (is_string($val) && !empty($val)) {
                    $val = json_decode($val);
                }
            }

            // Override with master value
            $data['settings'][$name] = $val;
        }

        // Prevent saving masked string of setting values
        $masked_fields = (array) ($tenant->package_invoice->metadata->shared_settings->masked ?? []);
        $shared_secret_master_settings = fq_saas_master_shared_settings($masked_fields);
        foreach ($shared_secret_master_settings as $setting) {
            if (
                isset($data['settings'][$setting->name]) &&
                fq_saas_get_starred_string($setting->value) === fq_saas_get_starred_string($data['settings'][$setting->name])
            ) {
                $data['settings'][$setting->name] = "";
            }
        }

        return $data;
    }

    // Add limitation statistic widget to dashboard
    hooks()->add_action('before_start_render_dashboard_content', 'fq_saas_dashboard_hook', 8);
    function fq_saas_dashboard_hook()
    {
        $CI = &get_instance();
        if (is_admin()) {
            $CI->load->model('invoices_model');
            $invoice = fq_saas_tenant()->package_invoice;
            $on_trial = fq_saas_invoice_is_on_trial($invoice);
            $days_left = $on_trial ? (int)fq_saas_get_days_until($invoice->duedate) : '';
            $invoice_days_left = $invoice ? fq_saas_get_days_until($invoice->duedate) : '';

            $CI->load->view(
                FQ_SAAS_MODULE_NAME . '/client/includes/invoice_notification',
                [
                    'invoice' => $invoice,
                    'days_left' => $days_left,
                    'on_trial' => $on_trial,
                    'invoice_days_left' => $invoice_days_left
                ]
            );

            // Demo reset heads-up banner (added in 0.3.8). Shown only when the tenant is
            // registered as a demo instance by the super admin.
            $demo_info = fq_saas_tenant_demo_reset_info();
            $demo_info = hooks()->apply_filters('fq_saas_demo_reset_notice', $demo_info);
            if (!empty($demo_info) && !empty($demo_info['is_demo'])) {
                $CI->load->view(FQ_SAAS_MODULE_NAME . '/client/includes/demo_reset_notice', ['info' => $demo_info]);
            }
        }
    }
    hooks()->add_filter('get_dashboard_widgets', function ($widgets) {
        return array_merge([['path' => FQ_SAAS_MODULE_NAME . '/includes/quota_stats', 'container' => 'top-12']], $widgets);
    });

    // Load custom css
    hooks()->add_action('app_admin_assets_added', function () use ($CI) {
        if (is_admin()) {
            $CI->app_css->add('saas-admin', fq_saas_asset_url('css/tenant-admin.css'));
            $CI->app_scripts->add('tenant-script', fq_saas_asset_url('js/tenant-admin.js'));
        }
    });

    // Check for upload path
    hooks()->add_filter('get_upload_path_by_type', function ($path, $type) {

        $tenant_upload_folder = str_ireplace(FCPATH, '', FQ_SAAS_TENANT_UPLOAD_BASE_FOLDER);
        if (
            strpos($path, $tenant_upload_folder) === false
        ) {
            $path = str_ireplace(FCPATH, '', $path);
            $pos = strpos($path, FQ_SAAS_UPLOAD_BASE_DIR);
            if ($pos !== false)
                $path = substr_replace($path, '', $pos, strlen(FQ_SAAS_UPLOAD_BASE_DIR));
            $path = $tenant_upload_folder . $path;
        }

        return $path;
    }, PHP_INT_MAX, 2);
}
