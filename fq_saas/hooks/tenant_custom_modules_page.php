<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$is_tenant || !$is_admin || !(int)fq_saas_tenant_get_super_option('fq_saas_enable_tenant_admin_modules_page')) return;

$CI->app_menu->add_setup_menu_item('modules-apps', [
    'href'     => admin_url('apps/modules'),
    'name'     => _l('modules'),
    'position' => 35,
    'badge'    => [],
]);