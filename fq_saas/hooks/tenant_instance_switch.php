<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add tenant switch to top nav
if ($is_tenant && fq_saas_tenant_is_enabled('instance_switch')) {
    hooks()->add_action('admin_navbar_start', function () {
        if (is_admin()) {
            $tenant = fq_saas_tenant();
            $clientid = $tenant->clientid;
            $table = fq_saas_table('companies');
            $instances = fq_saas_raw_query("SELECT * from `$table` WHERE `clientid`='$clientid' AND `status`='active';", [], true);
            $instances = fq_saas_filter_visible_instances($instances, $tenant->slug ?? '');
            $theme = $tenant->package_invoice->metadata->client_theme ?? '';
            $can_create_new_instance = $theme === 'agency';
            if (count($instances) > 1 || $can_create_new_instance) {
                echo '<li class="icon header-tenant-switch tw-h-full tw-relative ltr:tw-mr-1.5 rtl:tw-ml-1.5" data-toggle="tooltip" data-title="' . _l('fq_saas_switch_app') . '">
                        <a href="#" id="tenant-switch" class="!tw-px-0 tw-group" data-toggle="dropdown" aria-expanded="true">
                            <span class="tw-ring-1 tw-ring-offset-1 tw-ring-primary-500 tw-rounded-lg tw-px-3 tw-py-2 tw-inline-block"> <i class="fa fa-random tw-mr-1"></i>' . $tenant->name . '</span>
                        </a>
                        <ul class="dropdown-menu animated fadeIn" id="tenant-switch-list">
                        ';
                foreach ($instances as $key => $instant) {
                    $instant->package_invoice = $tenant->package_invoice;
                    $url = '#';
                    if ($instant->slug !== $tenant->slug) {
                        // Dla demo tenantów przechodzimy od razu do panelu admin z autologowaniem.
                        if ((int)($tenant->clientid ?? 0) === 3 && (int)($instant->clientid ?? 0) === 3) {
                            $url = rtrim(fq_saas_tenant_base_url($instant), '/') . '/admin/authentication?demo_account=owner&autologin=1';
                        } else {
                            $url = admin_url('billing/my_account?redirect=' . fq_saas_tenant_base_url($instant) . '&target=tenant');
                        }
                    }
                    echo "<li><a href='$url'>$instant->name</a></li>";
                }
                if ($can_create_new_instance)
                    echo '
                        <li class="text-center w-full">
                        <a href="' . admin_url('billing/my_account?redirect=clients/?companies') . '" class="tw-mt-3 text-center"><span class="tw-text-primary-600"><i class="fa-regular fa-plus"></i> Add new</span></a>
                        </li>';
                echo '
                        </ul>
                    </li>';
            }
        }
    });
}
