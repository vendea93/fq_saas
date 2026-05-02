<?php

defined('BASEPATH') or exit('No direct script access allowed');

$marketplace_item = [
    'href'     => admin_url('apps/modules'),
    'name'     => _l('fq_saas_plugin_marketplace'),
    'icon'     => 'fa fa-plug',
    'position' => 6,
    'badge'    => [],
];

if (fq_saas_tenant_admin_modules_page_enabled()) {
    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('flowquest-plugin-marketplace', $marketplace_item);

        $CI->app_menu->add_setup_menu_item('modules-apps', [
            'href'     => admin_url('apps/modules'),
            'name'     => _l('fq_saas_plugin_marketplace'),
            'position' => 35,
            'badge'    => [],
        ]);
    }
}

// Top navbar link is rendered in application/views/admin/includes/header.php
// to keep styling consistent with adjacent default links (e.g., Settings).
