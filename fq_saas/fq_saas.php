<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: FQ SAAS
Description: FlowQuest SaaS engine for Perfex CRM — multi-tenant provisioning, billing (Stripe), and instance management.
Version: 0.4.2
Requires at least: 3.1.*
Author: FlowQuest
Author URI: https://flowquest.app
*/
defined('FQ_SAAS_VERSION_NUMBER') or define('FQ_SAAS_VERSION_NUMBER', '0.4.2');

require_once __DIR__ . '/helpers/fq_saas_php8_polyfill_helper.php';

// Global common module constants
require_once('config/constants.php');
require_once(__DIR__ . '/libraries/Fq_saas_kernel.php');
Fq_saas_kernel::boot();

$CI = &get_instance();

/**
 * Load models
 */
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_model');
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_stripe_model');
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_cron_model');

/**
 * Load the module helper
 */
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME);
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_core');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_setup');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_usage_limit');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_api');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_billing');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_coupon');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_affiliate');
require_once __DIR__ . '/hooks/demo_login_panel.php';
if (!fq_saas_is_tenant()) {
    fq_saas_billing_register_hooks();
    fq_saas_coupon_register_filters();
    fq_saas_affiliate_register_hooks();
}


/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FQ_SAAS_MODULE_NAME, [FQ_SAAS_MODULE_NAME]);

hooks()->do_action('fq_saas_loaded');
hooks()->do_action('perfex_saas_loaded'); // @deprecated compatibility alias; remove after downstream migrates

/**
 * DEMO autologin (dla landingów i list demo) — bez dodatkowych klików.
 * Działa wyłącznie na publicznych tenantach demo (clientid=3) i tylko gdy jest:
 * `?demo_account=owner|employee|client&autologin=1`.
 *
 * Dane kont pobierane są z tenant->metadata->login_panel->accounts (wypełniane w demo factory).
 */
hooks()->add_action('admin_auth_init', 'fq_saas_demo_prepare_login_switch', PHP_INT_MIN);
hooks()->add_action('clients_authentication_constructor', 'fq_saas_demo_prepare_login_switch', PHP_INT_MIN);
hooks()->add_action('admin_auth_init', 'fq_saas_demo_direct_login_admin', PHP_INT_MIN + 1);
hooks()->add_action('clients_authentication_constructor', 'fq_saas_demo_direct_login_client', PHP_INT_MIN + 1);
hooks()->add_action('admin_auth_init', 'fq_saas_demo_autologin_admin', PHP_INT_MIN + 1);
hooks()->add_action('clients_authentication_constructor', 'fq_saas_demo_autologin_client', PHP_INT_MIN + 1);
hooks()->add_action('fq_saas_after_tenant_seeding', 'fq_saas_demo_normalize_default_configuration', PHP_INT_MAX);
hooks()->add_action('after_admin_login_form_start', 'fq_saas_demo_render_logo_override_script', PHP_INT_MAX);
hooks()->add_action('clients_login_form_start', 'fq_saas_demo_render_logo_override_script', PHP_INT_MAX);

function fq_saas_demo_autologin_enabled(): bool
{
    if (!isset($_GET['autologin']) || (string)$_GET['autologin'] !== '1') {
        return false;
    }
    if (empty($_GET['demo_account'])) {
        return false;
    }
    return true;
}

function fq_saas_demo_autologin_account_key(): string
{
    $key = strtolower((string)($_GET['demo_account'] ?? ''));
    if (!in_array($key, ['admin', 'owner', 'employee', 'client'], true)) {
        return '';
    }
    return $key;
}

function fq_saas_demo_autologin_accounts($tenant): array
{
    $slug = (string) ($tenant->slug ?? 'demo');
    
    $accounts = fq_saas_demo_accounts($slug);
    
    $fallback = [
        'admin' => ['target' => 'admin', 'email' => $accounts['admin']['email'], 'password' => $accounts['admin']['password']],
        'owner' => ['target' => 'admin', 'email' => $accounts['owner']['email'], 'password' => $accounts['owner']['password']],
        'employee' => ['target' => 'admin', 'email' => $accounts['employee']['email'], 'password' => $accounts['employee']['password']],
        'client' => ['target' => 'client', 'email' => $accounts['client']['email'], 'password' => $accounts['client']['password']],
    ];

    if (!$tenant || empty($tenant->metadata) || !is_object($tenant->metadata)) {
        return $fallback;
    }
    $panel = $tenant->metadata->login_panel ?? null;
    if (!$panel || !is_object($panel)) {
        return $fallback;
    }
    $accounts = $panel->accounts ?? null;
    if (!$accounts || !is_array((array)$accounts)) {
        return $fallback;
    }

    $merged = [];
    foreach ($fallback as $key => $defaults) {
        $merged[$key] = $defaults;
    }

    return $merged;
}

function fq_saas_demo_autologin_allowed($tenant, array $accounts): bool
{
    if (!$tenant || (int)($tenant->clientid ?? 0) !== 3) {
        return false;
    }
    return !empty($accounts);
}

function fq_saas_demo_prepare_login_switch(): void
{
    $CI = &get_instance();
    if (!$CI || empty($CI->input) || empty($CI->session)) {
        return;
    }

    if ((string) $CI->input->post('fq_demo_login') !== '1') {
        return;
    }

    $role = strtolower((string) $CI->input->post('fq_demo_login_role'));
    if (!in_array($role, ['admin', 'owner', 'employee', 'client'], true)) {
        return;
    }

    $tenant = function_exists('fq_saas_demo_login_panel_tenant') ? fq_saas_demo_login_panel_tenant() : null;
    if (!$tenant || (int) ($tenant->clientid ?? 0) !== 3) {
        return;
    }

    $slug = function_exists('fq_saas_demo_login_panel_host_slug') ? fq_saas_demo_login_panel_host_slug() : '';
    if ($slug === '') {
        $slug = strtolower(trim((string) ($tenant->slug ?? 'demo')));
    }

    $accounts = function_exists('fq_saas_demo_accounts') ? fq_saas_demo_accounts($slug) : [];
    if (!empty($accounts[$role]['email']) && !empty($accounts[$role]['password'])) {
        $_POST['email'] = $accounts[$role]['email'];
        $_POST['password'] = $accounts[$role]['password'];
        $_REQUEST['email'] = $accounts[$role]['email'];
        $_REQUEST['password'] = $accounts[$role]['password'];
    }

    $CI->session->unset_userdata('staff_user_id');
    $CI->session->unset_userdata('staff_logged_in');
    $CI->session->unset_userdata('client_user_id');
    $CI->session->unset_userdata('contact_user_id');
    $CI->session->unset_userdata('client_logged_in');
    $CI->session->unset_userdata('_two_factor_auth_established');
    $CI->session->unset_userdata('_two_factor_auth_staff_email');
    $CI->session->unset_userdata('tfa_staffid');
}

function fq_saas_demo_selected_login_payload(string $expectedTarget = ''): array
{
    $CI = &get_instance();
    if (!$CI || empty($CI->input)) {
        return [];
    }

    if ((string) $CI->input->post('fq_demo_login') !== '1') {
        return [];
    }

    $role = strtolower((string) $CI->input->post('fq_demo_login_role'));
    if (!in_array($role, ['admin', 'owner', 'employee', 'client'], true)) {
        return [];
    }

    $tenant = function_exists('fq_saas_demo_login_panel_tenant') ? fq_saas_demo_login_panel_tenant() : null;
    if (!$tenant || (int) ($tenant->clientid ?? 0) !== 3) {
        return [];
    }

    $slug = function_exists('fq_saas_demo_login_panel_host_slug') ? fq_saas_demo_login_panel_host_slug() : '';
    if ($slug === '') {
        $slug = strtolower(trim((string) ($tenant->slug ?? 'demo')));
    }

    $roles = function_exists('fq_saas_demo_login_panel_default_roles')
        ? fq_saas_demo_login_panel_default_roles($slug)
        : [];
    $account = $roles[$role] ?? [];
    if (!$account || ($expectedTarget !== '' && ($account['target'] ?? '') !== $expectedTarget)) {
        return [];
    }

    return $account;
}

function fq_saas_demo_direct_login_admin(): void
{
    $account = fq_saas_demo_selected_login_payload('admin');
    if (empty($account['email']) || empty($account['password'])) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('Authentication_model');
    $data = $CI->Authentication_model->login((string) $account['email'], (string) $account['password'], false, true);
    if ($data === false || (is_array($data) && (isset($data['memberinactive']) || isset($data['two_factor_auth'])))) {
        return;
    }

    hooks()->do_action('after_staff_login');
    redirect(admin_url());
}

function fq_saas_demo_direct_login_client(): void
{
    $account = fq_saas_demo_selected_login_payload('client');
    if (empty($account['email']) || empty($account['password'])) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('Authentication_model');
    $success = $CI->Authentication_model->login((string) $account['email'], (string) $account['password'], false, false);
    if ($success === false || (is_array($success) && isset($success['memberinactive']))) {
        return;
    }

    hooks()->do_action('after_contact_login');
    redirect(site_url());
}

function fq_saas_demo_default_slugs(): array
{
    $fallback = [
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

    $option = get_option('fq_saas_demo_instance');
    $slugs = is_string($option) ? json_decode($option, true) : (array) $option;
    $slugs = array_values(array_filter(array_map('strval', (array) $slugs)));

    if (empty($slugs)) {
        return $fallback;
    }

    return array_values(array_unique(array_merge($fallback, $slugs)));
}

function fq_saas_demo_render_logo_override_script(): void
{
    // Keep tenant logo configurable from settings.
    // Historical override forced one global logo for demo tenants and blocked manual branding changes.
    return;

    if (!function_exists('fq_saas_tenant')) {
        return;
    }

    $tenant = fq_saas_tenant();
    if (!$tenant || (int)($tenant->clientid ?? 0) !== 3) {
        return;
    }

    $slug = strtolower(trim((string)($tenant->slug ?? 'demo')));
    if ($slug === '') {
        return;
    }

    $logoFile = 'flowquest_logo_global.png';
    if (!file_exists(FCPATH . 'uploads/company/' . $logoFile)) {
        return;
    }

    $logoUrl = base_url('uploads/company/' . $logoFile) . '?v=' . time();
    echo '<script>(function(){';
    echo 'var desired=' . json_encode($logoUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';';
    echo 'if(!desired){return;}';
    echo 'var applyLogo=function(){';
    echo 'var img=document.querySelector(\".company-logo img\");';
    echo 'if(img){img.src=desired;return;}';
    echo 'var form=document.querySelector(\"form.login-form\");';
    echo 'if(!form){return;}';
    echo 'var holder=document.createElement(\"div\");';
    echo 'holder.className=\"company-logo text-center\";';
    echo 'holder.style.margin=\"0 0 16px\";';
    echo 'var created=document.createElement(\"img\");';
    echo 'created.className=\"img-responsive\";';
    echo 'created.alt=\"FlowQuest\";';
    echo 'created.style.maxHeight=\"48px\";';
    echo 'created.style.margin=\"0 auto\";';
    echo 'created.src=desired;';
    echo 'holder.appendChild(created);';
    echo 'var separator=form.querySelector(\".hr-panel-separator\");';
    echo 'if(separator&&separator.parentNode){separator.parentNode.insertBefore(holder, separator.nextSibling);}else{form.insertBefore(holder, form.firstChild);}';
    echo '};';
    echo 'var probe=new Image();';
    echo 'probe.onload=applyLogo;';
    echo 'probe.src=desired;';
    echo 'if(document.readyState===\"loading\"){document.addEventListener(\"DOMContentLoaded\",applyLogo);}else{applyLogo();}';
    echo '})();</script>';
}

function fq_saas_demo_tenant_admin_marketplace_enabled($tenant = null): bool
{
    if (!fq_saas_is_tenant()) {
        return false;
    }

    $tenant = $tenant ?: fq_saas_tenant();
    $slug = strtolower((string)($tenant->slug ?? fq_saas_tenant_slug() ?: ''));
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    $is_demo_slug = in_array($slug, fq_saas_demo_default_slugs(), true);
    $is_demo_host = $slug !== '' && (
        $host === $slug . '.flowquest.pl'
        || $host === $slug . '.demo.pl'
        || str_ends_with($host, '.' . $slug . '.demo.pl')
    );

    return $is_demo_slug && ((int)($tenant->clientid ?? 0) === 3 || $is_demo_host);
}

function fq_saas_demo_current_staff_is_platform_admin($tenant = null): bool
{
    if (!fq_saas_demo_tenant_admin_marketplace_enabled($tenant) || !is_staff_logged_in()) {
        return false;
    }

    $tenant = $tenant ?: fq_saas_tenant();
    $slug = strtolower((string)($tenant->slug ?? fq_saas_tenant_slug() ?: 'demo'));
    $accounts = function_exists('fq_saas_demo_accounts') ? fq_saas_demo_accounts($slug) : [];
    $admin_email = strtolower((string)($accounts['admin']['email'] ?? ''));

    if ($admin_email === '') {
        return false;
    }

    $staff = function_exists('get_staff') ? get_staff(get_staff_user_id()) : null;
    $staff_email = strtolower((string)($staff->email ?? ''));

    if ($staff_email === $admin_email || $staff_email === 'admin@demo.pl') {
        return true;
    }

    $firstname = strtolower(trim((string)($staff->firstname ?? '')));
    $lastname = strtolower(trim((string)($staff->lastname ?? '')));

    return (int)($staff->admin ?? 0) === 1
        && $firstname === 'administrator'
        && $lastname !== 'właściciel'
        && $lastname !== 'wlasciciel';
}

function fq_saas_demo_current_staff_role_key($tenant = null): string
{
    if (!fq_saas_demo_tenant_admin_marketplace_enabled($tenant) || !is_staff_logged_in()) {
        return '';
    }

    $tenant = $tenant ?: fq_saas_tenant();
    $slug = strtolower((string)($tenant->slug ?? fq_saas_tenant_slug() ?: 'demo'));
    $accounts = function_exists('fq_saas_demo_accounts') ? fq_saas_demo_accounts($slug) : [];
    $staff = function_exists('get_staff') ? get_staff(get_staff_user_id()) : null;
    $staff_email = strtolower((string)($staff->email ?? ''));

    foreach (['admin', 'owner', 'employee'] as $role) {
        $role_email = strtolower((string)($accounts[$role]['email'] ?? ''));
        if ($role_email !== '' && $staff_email === $role_email) {
            return $role;
        }
    }

    $firstname = strtolower(trim((string)($staff->firstname ?? '')));
    if ($firstname === 'administrator') {
        return 'admin';
    }
    if ($firstname === 'właściciel' || $firstname === 'wlasciciel') {
        return 'owner';
    }
    if ($firstname === 'pracownik') {
        return 'employee';
    }

    return '';
}

function fq_saas_tenant_admin_modules_page_enabled(): bool
{
    if (!fq_saas_is_tenant() || !is_staff_logged_in()) {
        return false;
    }

    $staff = function_exists('get_staff') ? get_staff(get_staff_user_id()) : null;
    $email = strtolower(trim((string)($staff->email ?? '')));
    $firstname = strtolower(trim((string)($staff->firstname ?? '')));

    // Always allow full technical admins.
    if (is_admin()) {
        return true;
    }

    // Some demo accounts are "administrator@..." without proper admin flag.
    if ($email !== '' && (str_starts_with($email, 'administrator@') || str_starts_with($email, 'admin@'))) {
        return true;
    }

    if ($firstname === 'administrator') {
        return true;
    }

    // Demo policy: only explicit "Administrator" account can access marketplace.
    if (fq_saas_demo_tenant_admin_marketplace_enabled()) {
        return fq_saas_demo_current_staff_role_key() === 'admin';
    }

    return false;
}

function fq_saas_demo_default_brand_label(string $slug): string
{
    $map = [
        'demo' => 'Demo',
        'beauty' => 'Beauty',
        'hotel' => 'Hotel',
        'warsztat' => 'Warsztat',
        'nieruchomosc' => 'Nieruchomości',
        'nieruchomosci' => 'Nieruchomości',
        'logistyka' => 'Logistyka',
        'ecommerce' => 'E-commerce',
        'kursy' => 'Kursy',
        'serwiswww' => 'Serwis WWW',
        'oze' => 'OZE',
        'agencja' => 'Agencja',
        'rekrutacja' => 'Rekrutacja',
        'medycyna' => 'Medycyna',
        'eventy' => 'Eventy',
        'gastronomia' => 'Gastronomia',
    ];

    return $map[$slug] ?? ucfirst($slug);
}

function fq_saas_demo_default_password_hash(): string
{
    static $hash = null;

    if ($hash === null) {
        $hash = password_hash('FlowQuest123!', PASSWORD_BCRYPT, ['cost' => 8]);
    }

    return $hash;
}

function fq_saas_demo_normalize_default_configuration($data): void
{
    $tenant = $data['company'] ?? null;
    $dsn = $data['dsn'] ?? [];

    if (!$tenant || empty($tenant->slug) || !is_array($dsn)) {
        return;
    }

    $slug = strtolower((string) $tenant->slug);
    if (!in_array($slug, fq_saas_demo_default_slugs(), true)) {
        return;
    }

    $tenant_dbprefix = fq_saas_tenant_db_prefix($slug);
    $staff_table = $tenant_dbprefix . 'staff';
    $roles_table = $tenant_dbprefix . 'roles';
    $contacts_table = $tenant_dbprefix . 'contacts';
    $clients_table = $tenant_dbprefix . 'clients';
    $staff_permissions_table = $tenant_dbprefix . 'staff_permissions';
    $contact_permissions_table = $tenant_dbprefix . 'contact_permissions';
    $options_table = $tenant_dbprefix . 'options';
    $now = date('Y-m-d H:i:s');
    $brand = fq_saas_demo_default_brand_label($slug);
    $company_name = 'FlowQuest ' . $brand;
    $client_phone = '+48 500 100 201';
    $staff_phone = '+48 600 700 800';
    $accounts = fq_saas_demo_accounts($slug);
    $owner_permissions = fq_saas_demo_owner_permissions();
    $employee_permissions = fq_saas_demo_employee_permissions();
    $client_permission_ids = fq_saas_demo_client_permission_ids();
    $owner_role_permissions = [];
    foreach ($owner_permissions as [$feature, $capability]) {
        $owner_role_permissions[$feature] = $owner_role_permissions[$feature] ?? [];
        if (!in_array($capability, $owner_role_permissions[$feature], true)) {
            $owner_role_permissions[$feature][] = $capability;
        }
    }

    $employee_role_permissions = [];
    foreach ($employee_permissions as [$feature, $capability]) {
        $employee_role_permissions[$feature] = $employee_role_permissions[$feature] ?? [];
        if (!in_array($capability, $employee_role_permissions[$feature], true)) {
            $employee_role_permissions[$feature][] = $capability;
        }
    }

    // Ujednolicenie widocznego brandingu demo.
    fq_saas_raw_query(
        "UPDATE `$options_table` SET `value` = " . get_instance()->db->escape($company_name) . " WHERE `name` = 'companyname'",
        $dsn
    );

    $owner_role_permissions_serialized = serialize($owner_role_permissions);
    $employee_role_permissions_serialized = serialize($employee_role_permissions);
    $employee_admin_flag = $slug === 'demo' ? '1' : '0';
    fq_saas_raw_query(
        "INSERT INTO `$roles_table` (`roleid`, `name`, `permissions`)
         VALUES ('1', 'Właściciel demo', " . get_instance()->db->escape($owner_role_permissions_serialized) . ")
         ON DUPLICATE KEY UPDATE `name`='Właściciel demo', `permissions`=VALUES(`permissions`)",
        $dsn
    );
    fq_saas_raw_query(
        "INSERT INTO `$roles_table` (`roleid`, `name`, `permissions`)
         VALUES ('2', 'Pracownik demo', " . get_instance()->db->escape($employee_role_permissions_serialized) . ")
         ON DUPLICATE KEY UPDATE `name`='Pracownik demo', `permissions`=VALUES(`permissions`)",
        $dsn
    );

    // Administrator: pełny dostęp techniczny.
    $admin = fq_saas_raw_query_row(
        "SELECT `staffid` FROM `$staff_table`
         WHERE `email` IN (" . get_instance()->db->escape($accounts['admin']['email']) . ", 'admin@demo.pl')
            OR `admin` = '1'
         ORDER BY CASE WHEN `email` = " . get_instance()->db->escape($accounts['admin']['email']) . " THEN 0 WHEN `email` = 'admin@demo.pl' THEN 1 ELSE 2 END, `staffid` ASC
         LIMIT 1",
        $dsn,
        true
    );
    $admin_hash = password_hash($accounts['admin']['password'], PASSWORD_BCRYPT, ['cost' => 8]);
    if (empty($admin->staffid)) {
        fq_saas_raw_query(
            "INSERT INTO `$staff_table` (`email`, `firstname`, `lastname`, `phonenumber`, `password`, `datecreated`, `admin`, `role`, `active`, `default_language`, `is_not_staff`, `hourly_rate`, `two_factor_auth_enabled`, `last_password_change`)
             VALUES (" . get_instance()->db->escape($accounts['admin']['email']) . ", 'Administrator', " . get_instance()->db->escape($brand) . ", '', " . get_instance()->db->escape($admin_hash) . ", " . get_instance()->db->escape($now) . ", '1', '1', '1', 'polish', '0', '0.00', '0', " . get_instance()->db->escape($now) . ")",
            $dsn
        );
        $admin = fq_saas_raw_query_row("SELECT `staffid` FROM `$staff_table` WHERE `email` = " . get_instance()->db->escape($accounts['admin']['email']) . " ORDER BY `staffid` ASC LIMIT 1", $dsn, true);
    } else {
        fq_saas_raw_query(
            "UPDATE `$staff_table`
             SET `firstname`='Administrator',
                 `lastname`=" . get_instance()->db->escape($brand) . ",
                 `email`=" . get_instance()->db->escape($accounts['admin']['email']) . ",
                 `phonenumber`='',
                 `password`=" . get_instance()->db->escape($admin_hash) . ",
                 `admin`='1',
                 `role`='1',
                 `active`='1',
                 `default_language`='polish',
                 `two_factor_auth_enabled`='0',
                 `last_password_change`=" . get_instance()->db->escape($now) . "
             WHERE `staffid`=" . (int) $admin->staffid,
            $dsn
        );
    }

    // Właściciel demo ma pełny dostęp konfiguracyjny w swojej instancji.
    $owner = fq_saas_raw_query_row(
        "SELECT `staffid` FROM `$staff_table`
         WHERE `email` = " . get_instance()->db->escape($accounts['owner']['email']) . "
         ORDER BY `staffid` ASC LIMIT 1",
        $dsn,
        true
    );
    $owner_hash = password_hash($accounts['owner']['password'], PASSWORD_BCRYPT, ['cost' => 8]);
    if (empty($owner->staffid)) {
        fq_saas_raw_query(
            "INSERT INTO `$staff_table` (`email`, `firstname`, `lastname`, `phonenumber`, `password`, `datecreated`, `admin`, `role`, `active`, `default_language`, `is_not_staff`, `hourly_rate`, `two_factor_auth_enabled`, `last_password_change`)
             VALUES (" . get_instance()->db->escape($accounts['owner']['email']) . ", 'Właściciel', " . get_instance()->db->escape($brand) . ", " . get_instance()->db->escape($staff_phone) . ", " . get_instance()->db->escape($owner_hash) . ", " . get_instance()->db->escape($now) . ", '1', '1', '1', 'polish', '0', '120.00', '0', " . get_instance()->db->escape($now) . ")",
            $dsn
        );
        $owner = fq_saas_raw_query_row("SELECT `staffid` FROM `$staff_table` WHERE `email` = " . get_instance()->db->escape($accounts['owner']['email']) . " ORDER BY `staffid` ASC LIMIT 1", $dsn, true);
    } else {
        fq_saas_raw_query(
            "UPDATE `$staff_table`
             SET `firstname`='Właściciel',
                 `lastname`=" . get_instance()->db->escape($brand) . ",
                 `email`=" . get_instance()->db->escape($accounts['owner']['email']) . ",
                 `phonenumber`=" . get_instance()->db->escape($staff_phone) . ",
                 `password`=" . get_instance()->db->escape($owner_hash) . ",
                 `admin`='1',
                 `role`='1',
                 `active`='1',
                 `default_language`='polish',
                 `two_factor_auth_enabled`='0',
                 `hourly_rate`='120.00',
                 `last_password_change`=" . get_instance()->db->escape($now) . "
             WHERE `staffid`=" . (int) $owner->staffid,
            $dsn
        );
    }

    // Pracownik: ograniczony dostęp operacyjny.
    $employee = fq_saas_raw_query_row(
        "SELECT `staffid` FROM `$staff_table`
         WHERE `email` IN (" . get_instance()->db->escape($accounts['employee']['email']) . ", 'pracownik@demo.pl')
         ORDER BY CASE WHEN `email` = " . get_instance()->db->escape($accounts['employee']['email']) . " THEN 0 ELSE 1 END, `staffid` ASC
         LIMIT 1",
        $dsn,
        true
    );
    $employee_hash = password_hash($accounts['employee']['password'], PASSWORD_BCRYPT, ['cost' => 8]);
    if (empty($employee->staffid)) {
        fq_saas_raw_query(
            "INSERT INTO `$staff_table` (`email`, `firstname`, `lastname`, `phonenumber`, `password`, `datecreated`, `admin`, `role`, `active`, `default_language`, `is_not_staff`, `hourly_rate`, `two_factor_auth_enabled`, `last_password_change`)
             VALUES (" . get_instance()->db->escape($accounts['employee']['email']) . ", 'Pracownik', " . get_instance()->db->escape($brand) . ", " . get_instance()->db->escape($staff_phone) . ", " . get_instance()->db->escape($employee_hash) . ", " . get_instance()->db->escape($now) . ", " . get_instance()->db->escape($employee_admin_flag) . ", '2', '1', 'polish', '0', '65.00', '0', " . get_instance()->db->escape($now) . ")",
            $dsn
        );
        $employee = fq_saas_raw_query_row("SELECT `staffid` FROM `$staff_table` WHERE `email` = " . get_instance()->db->escape($accounts['employee']['email']) . " ORDER BY `staffid` ASC LIMIT 1", $dsn, true);
    } else {
        fq_saas_raw_query(
            "UPDATE `$staff_table`
             SET `firstname`='Pracownik',
                 `lastname`=" . get_instance()->db->escape($brand) . ",
                 `email`=" . get_instance()->db->escape($accounts['employee']['email']) . ",
                 `phonenumber`=" . get_instance()->db->escape($staff_phone) . ",
                 `password`=" . get_instance()->db->escape($employee_hash) . ",
                 `admin`=" . get_instance()->db->escape($employee_admin_flag) . ",
                 `role`='2',
                 `active`='1',
                 `default_language`='polish',
                 `two_factor_auth_enabled`='0',
                 `hourly_rate`='65.00',
                 `last_password_change`=" . get_instance()->db->escape($now) . "
             WHERE `staffid`=" . (int) $employee->staffid,
            $dsn
        );
    }

    // Klient: portal klienta, zawsze aktywna i potwierdzona tożsamość.
    $client = fq_saas_raw_query_row(
        "SELECT `id`, `userid` FROM `$contacts_table`
         WHERE `email` IN (" . get_instance()->db->escape($accounts['client']['email']) . ", 'klient@demo.pl')
         ORDER BY CASE WHEN `email` = " . get_instance()->db->escape($accounts['client']['email']) . " THEN 0 ELSE 1 END, `id` ASC
         LIMIT 1",
        $dsn,
        true
    );
    $client_userid = !empty($client->userid) ? (int) $client->userid : 0;
    $client_hash = password_hash($accounts['client']['password'], PASSWORD_BCRYPT, ['cost' => 8]);

    if (empty($client->id)) {
        $existing_client = fq_saas_raw_query_row("SELECT `userid` FROM `$clients_table` ORDER BY `userid` ASC LIMIT 1", $dsn, true);
        if (empty($existing_client->userid)) {
            fq_saas_raw_query(
                "INSERT INTO `$clients_table` (`company`, `phonenumber`, `country`, `city`, `state`, `address`, `website`, `datecreated`, `active`, `default_language`, `default_currency`, `show_primary_contact`, `registration_confirmed`, `addedfrom`)
                 VALUES (" . get_instance()->db->escape($company_name) . ", " . get_instance()->db->escape($client_phone) . ", '177', " . get_instance()->db->escape($brand) . ", " . get_instance()->db->escape($brand) . ", " . get_instance()->db->escape('ul. Demo 1') . ", " . get_instance()->db->escape($slug . '.flowquest.pl') . ", " . get_instance()->db->escape($now) . ", '1', 'polish', '1', '1', '1', '0')",
                $dsn
            );
            $existing_client = fq_saas_raw_query_row("SELECT `userid` FROM `$clients_table` ORDER BY `userid` ASC LIMIT 1", $dsn, true);
        }
        $client_userid = !empty($existing_client->userid) ? (int) $existing_client->userid : 0;
        if (!empty($client_userid)) {
            fq_saas_raw_query(
                "INSERT INTO `$contacts_table` (`userid`, `is_primary`, `firstname`, `lastname`, `email`, `phonenumber`, `title`, `datecreated`, `password`, `active`, `invoice_emails`, `estimate_emails`, `credit_note_emails`, `contract_emails`, `task_emails`, `project_emails`, `ticket_emails`, `last_password_change`, `email_verified_at`)
                 VALUES (" . $client_userid . ", '1', 'Klient', " . get_instance()->db->escape($brand) . ", " . get_instance()->db->escape($accounts['client']['email']) . ", " . get_instance()->db->escape($client_phone) . ", 'Konto pokazowe', " . get_instance()->db->escape($now) . ", " . get_instance()->db->escape($client_hash) . ", '1', '1', '1', '1', '1', '1', '1', '1', " . get_instance()->db->escape($now) . ", " . get_instance()->db->escape($now) . ")",
                $dsn
            );
            $client = fq_saas_raw_query_row("SELECT `id`, `userid` FROM `$contacts_table` WHERE `email` = " . get_instance()->db->escape($accounts['client']['email']) . " ORDER BY `id` ASC LIMIT 1", $dsn, true);
            $client_userid = !empty($client->userid) ? (int) $client->userid : $client_userid;
        }
    } else {
        $client_userid = (int) $client->userid;
        fq_saas_raw_query(
            "UPDATE `$contacts_table`
             SET `firstname`='Klient',
                 `lastname`=" . get_instance()->db->escape($brand) . ",
                 `email`=" . get_instance()->db->escape($accounts['client']['email']) . ",
                 `phonenumber`=" . get_instance()->db->escape($client_phone) . ",
                 `title`='Konto pokazowe',
                 `password`=" . get_instance()->db->escape($client_hash) . ",
                 `active`='1',
                 `is_primary`='1',
                 `invoice_emails`='1',
                 `estimate_emails`='1',
                 `credit_note_emails`='1',
                 `contract_emails`='1',
                 `task_emails`='1',
                 `project_emails`='1',
                 `ticket_emails`='1',
                 `last_password_change`=" . get_instance()->db->escape($now) . ",
                 `email_verified_at`=" . get_instance()->db->escape($now) . "
             WHERE `id`=" . (int) $client->id,
            $dsn
        );
    }

    if (!empty($client_userid)) {
        fq_saas_raw_query("UPDATE `$clients_table` SET `company`=" . get_instance()->db->escape($company_name) . ", `active`='1', `registration_confirmed`='1', `show_primary_contact`='1' WHERE `userid`=" . (int) $client_userid, $dsn);
        fq_saas_raw_query("UPDATE `$contacts_table` SET `is_primary`='0' WHERE `userid`=" . (int) $client_userid . " AND `email` <> " . get_instance()->db->escape($accounts['client']['email']), $dsn);
        fq_saas_raw_query("UPDATE `$contacts_table` SET `is_primary`='1' WHERE `userid`=" . (int) $client_userid . " AND `email` = " . get_instance()->db->escape($accounts['client']['email']), $dsn);
        fq_saas_raw_query("DELETE FROM `$contact_permissions_table` WHERE `userid`=" . (int) $client_userid, $dsn);
        foreach ($client_permission_ids as $permission_id) {
            fq_saas_raw_query(
                "INSERT INTO `$contact_permissions_table` (`permission_id`, `userid`) VALUES (" . (int) $permission_id . ", " . (int) $client_userid . ")",
                $dsn
            );
        }
    }

    // Uprawnienia właściciela na poziomie osobistym.
    if (!empty($owner->staffid)) {
        $owner_id = (int) $owner->staffid;
        fq_saas_raw_query("DELETE FROM `$staff_permissions_table` WHERE `staff_id`=" . $owner_id, $dsn);
        foreach ($owner_permissions as [$feature, $capability]) {
            fq_saas_raw_query(
                "INSERT INTO `$staff_permissions_table` (`staff_id`, `feature`, `capability`) VALUES (" . $owner_id . ", " . get_instance()->db->escape($feature) . ", " . get_instance()->db->escape($capability) . ")",
                $dsn
            );
        }
    }

    // Uprawnienia pracownika na poziomie osobistym.
    if (!empty($employee->staffid)) {
        $employee_id = (int) $employee->staffid;
        fq_saas_raw_query("DELETE FROM `$staff_permissions_table` WHERE `staff_id`=" . $employee_id, $dsn);
        foreach ($employee_permissions as [$feature, $capability]) {
            fq_saas_raw_query(
                "INSERT INTO `$staff_permissions_table` (`staff_id`, `feature`, `capability`) VALUES (" . $employee_id . ", " . get_instance()->db->escape($feature) . ", " . get_instance()->db->escape($capability) . ")",
                $dsn
            );
        }
    }

    if (!empty($tenant->id)) {
        $tenant_row = fq_saas_raw_query_row("SELECT `metadata` FROM `" . fq_saas_table('companies') . "` WHERE `id`=" . (int) $tenant->id . " LIMIT 1", [], true);
        $metadata = [];
        if (!empty($tenant_row->metadata)) {
            $decoded = json_decode((string) $tenant_row->metadata, true);
            if (is_array($decoded)) {
                $metadata = $decoded;
            }
        }

        $metadata['login_panel'] = $metadata['login_panel'] ?? [];
        $metadata['login_panel']['accounts'] = [
            'admin' => array_merge(fq_saas_demo_login_panel_default_roles($slug)['admin'], ['action' => admin_url('authentication')]),
            'owner' => array_merge(fq_saas_demo_login_panel_default_roles($slug)['owner'], ['action' => admin_url('authentication')]),
            'employee' => array_merge(fq_saas_demo_login_panel_default_roles($slug)['employee'], ['action' => admin_url('authentication')]),
            'client' => array_merge(fq_saas_demo_login_panel_default_roles($slug)['client'], ['action' => site_url('authentication/login')]),
        ];

        fq_saas_raw_query(
            "UPDATE `" . fq_saas_table('companies') . "` SET `metadata`=" . get_instance()->db->escape(json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . " WHERE `id`=" . (int) $tenant->id,
            []
        );
    }
}

function fq_saas_demo_autologin_admin(): void
{
    if (!fq_saas_is_tenant() || !fq_saas_demo_autologin_enabled()) {
        return;
    }
    if (function_exists('is_staff_logged_in') && is_staff_logged_in()) {
        return;
    }

    $key = fq_saas_demo_autologin_account_key();
    if ($key === '') {
        return;
    }

    $tenant = fq_saas_tenant();
    $accounts = fq_saas_demo_autologin_accounts($tenant);
    if (!fq_saas_demo_autologin_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$key] ?? null;
    if (!$account || !is_array((array)$account)) {
        return;
    }
    $account = (array)$account;
    if (($account['target'] ?? '') !== 'admin') {
        return;
    }

    $email = trim((string)($account['email'] ?? ''));
    $password = (string)($account['password'] ?? '');
    if ($email === '' || $password === '') {
        return;
    }

    $CI = &get_instance();
    if (!$CI) {
        return;
    }
    $CI->load->model('Authentication_model');

    $data = $CI->Authentication_model->login($email, $password, false, true);
    if ($data === false || (is_array($data) && (isset($data['memberinactive']) || isset($data['two_factor_auth'])))) {
        return;
    }

    hooks()->do_action('after_staff_login');
    redirect(admin_url());
}

function fq_saas_demo_autologin_client($controller): void
{
    if (!fq_saas_is_tenant() || !fq_saas_demo_autologin_enabled()) {
        return;
    }
    if (function_exists('is_client_logged_in') && is_client_logged_in()) {
        return;
    }

    $key = fq_saas_demo_autologin_account_key();
    if ($key === '') {
        return;
    }

    $tenant = fq_saas_tenant();
    $accounts = fq_saas_demo_autologin_accounts($tenant);
    if (!fq_saas_demo_autologin_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$key] ?? null;
    if (!$account || !is_array((array)$account)) {
        return;
    }
    $account = (array)$account;
    if (($account['target'] ?? '') !== 'client') {
        return;
    }

    $email = trim((string)($account['email'] ?? ''));
    $password = (string)($account['password'] ?? '');
    if ($email === '' || $password === '') {
        return;
    }

    if (!$controller || !isset($controller->load)) {
        return;
    }

    $controller->load->model('Authentication_model');
    $success = $controller->Authentication_model->login($email, $password, false, false);
    if ($success === false || (is_array($success) && isset($success['memberinactive']))) {
        return;
    }

    hooks()->do_action('after_contact_login');
    redirect(site_url());
}

/**
 * Cron management
 */
if (fq_saas_is_tenant()) {
    hooks()->add_action('before_cron_run', 'fq_saas_cron', PHP_INT_MIN); // Want to run this first for tenant
} else {
    hooks()->add_action('after_cron_run', 'fq_saas_cron');

    hooks()->add_action('before_cron_run', 'fq_saas_cron_before', PHP_INT_MIN);

    hooks()->add_action('after_cron_run', 'fq_saas_reset_demo_instances');
}

hooks()->add_filter('cron_functions_execute_seconds', function ($seconds) {
    // Disable cron lock for tenant. This is neccessary as there is already parent lock by the top saas cron.
    if (fq_saas_is_tenant() && !defined('APP_DISABLE_CRON_LOCK')) define('APP_DISABLE_CRON_LOCK', true);
    return $seconds;
});
hooks()->add_filter('used_cron_features', function ($f) {
    $f[] = _l('fq_saas_cron_feature_migration');
    return $f;
});

/**
 * Listen to any module activation and run the setup again.
 * This ensure new tables are prepared for saas.
 */
hooks()->add_action('module_activated', 'fq_saas_trigger_module_install');
hooks()->add_action('module_deactivated', 'fq_saas_trigger_module_install');

/**
 * Register activation module hook
 */
register_activation_hook(FQ_SAAS_MODULE_NAME, 'fq_saas_module_activation_hook');

function fq_saas_module_activation_hook()
{
    fq_saas_install();
}

/**
 * Self-heal: re-inject routes/hooks into application/config if a file upgrade wiped the markers.
 *
 * This is the #1 source of post-upgrade 404s — when the user uploads a new ZIP without going through
 * the Perfex Setup → Modules Deactivate/Activate cycle, the `require_once` line that pulls in our
 * module's config/my_routes.php is missing from application/config/my_routes.php. Without that line,
 * admin_url('fq_saas/...') hits the default CI router and returns 404. We detect the missing marker
 * and restore it transparently; nothing is re-run if markers already exist.
 */
hooks()->add_action('admin_init', 'fq_saas_routes_self_heal', 1);
function fq_saas_routes_self_heal()
{
    if (fq_saas_is_tenant()) {
        return;
    }

    $required = [
        APPPATH . 'config/my_routes.php'  => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/my_routes.php'",
        APPPATH . 'config/app-config.php' => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/app-config.php'",
        APPPATH . 'config/my_hooks.php'   => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/my_hooks.php'",
    ];

    $healed = false;
    foreach ($required as $dest => $requirePath) {
        if (!is_file($dest) || !is_writable($dest)) {
            continue;
        }
        $haystack = @file_get_contents($dest);
        if ($haystack === false) {
            continue;
        }
        if (strpos($haystack, "modules/" . FQ_SAAS_MODULE_NAME . "/config/" . basename($dest)) === false) {
            fq_saas_require_in_file($dest, $requirePath, false, false, true);
            $healed = true;
        }
    }

    if ($healed) {
        log_message('info', 'fq_saas: self-healed missing require_once in application/config; reloading request.');
        $url = current_url();
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        header('Location: ' . $url . $sep . 'fq_saas_selfheal=1');
        exit;
    }
}

/**
 * Dactivation module hook
 */
register_deactivation_hook(FQ_SAAS_MODULE_NAME, 'fq_saas_module_deactivation_hook');
function fq_saas_module_deactivation_hook()
{
    fq_saas_uninstall();
}

/**
 * Register admin footer hook - Common to both admin and instance
 * @todo Separate instance js customization from super admin
 */
hooks()->add_action('app_admin_footer', 'fq_saas_admin_footer_hook');
function fq_saas_admin_footer_hook()
{
    //load common admin asset
    $CI = &get_instance();
    $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/scripts');


    //load add user to package modal
    if (!fq_saas_is_tenant() && $CI->router->fetch_class() == 'invoices')
        $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/add_user_to_package_modal');
}


/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
hooks()->add_action('admin_init', 'fq_saas_module_init_menu_items', 50);
function fq_saas_module_init_menu_items()
{
    $CI = &get_instance();

    // Ensure module strings are available before building menu (matches register_staff_capabilities feature ids).
    $locale = $GLOBALS['language'] ?? get_option('active_language') ?: 'english';
    $CI->lang->load(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME, $locale);

    if (
        !fq_saas_is_tenant() && (
            staff_can('view', 'fq_saas_companies') ||
            staff_can('view', 'fq_saas_packages') ||
            staff_can('view', 'fq_saas_settings') ||
            staff_can('view', 'fq_saas_landing') ||
            staff_can('view', 'fq_saas_cms') ||
            staff_can('view', 'fq_saas_coupons') ||
            staff_can('view', 'fq_saas_affiliates') ||
            staff_can('view', 'fq_saas_domains') ||
            staff_can('view', 'fq_saas_api_user') ||
            staff_can('view', 'fq_saas_dashboard')
        )
    ) {

        $badge = [];

        $CI->app_menu->add_sidebar_menu_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
            'name' => _l('fq_saas_menu_title'),
            'icon' => 'fa fa-users tw-font-bold',
            'position' => 2,
            'badge' => $badge
        ]);

        if (staff_can('view', 'fq_saas_api_user')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_api',
                'name' => _l('fq_saas_api'),
                'icon' => 'fa fa-link',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/api'),
                'position' => 1,
            ]);
        }

        if (staff_can('view', 'fq_saas_packages')) {
            $is_single_package_mode = fq_saas_is_single_package_mode();
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_packages',
                'name' => $is_single_package_mode ? _l('fq_saas_pricing') : _l('fq_saas_packages'),
                'icon' => 'fa fa-list',
                'href' => $is_single_package_mode ? admin_url(FQ_SAAS_ROUTE_NAME . '/pricing') : admin_url(FQ_SAAS_ROUTE_NAME . '/packages'),
                'position' => 5,
            ]);
        }

        if (staff_can('view', 'fq_saas_landing')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_landing',
                'name' => _l('fq_saas_landing_builder'),
                'icon' => 'fa fa-paint-brush',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/landing_builder'),
                'position' => 6,
            ]);
        }

        if (staff_can('view', 'fq_saas_cms')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_cms',
                'name' => _l('fq_saas_cms'),
                'icon' => 'fa fa-file-lines',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/cms'),
                'position' => 7,
            ]);
        }

        if (staff_can('view', 'fq_saas_coupons')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_coupons',
                'name' => _l('fq_saas_coupons'),
                'icon' => 'fa fa-ticket',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/coupons'),
                'position' => 8,
            ]);
        }

        if (staff_can('view', 'fq_saas_affiliates')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_affiliates',
                'name' => _l('fq_saas_affiliates'),
                'icon' => 'fa fa-handshake',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates'),
                'position' => 9,
            ]);
        }

        if (staff_can('view', 'fq_saas_domains')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_domains',
                'name' => _l('fq_saas_domains'),
                'icon' => 'fa fa-globe',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/domains'),
                'position' => 10,
            ]);
        }

        if (staff_can('view', 'fq_saas_companies')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_companies',
                'name' => _l('fq_saas_tenants'),
                'icon' => 'fa fa-university',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/companies'),
                'position' => 11,
            ]);
        }

        if (staff_can('view', 'fq_saas_companies') && staff_can('view', 'invoices')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_invoices',
                'name' => _l('fq_saas_invoices'),
                'icon' => 'fa-solid fa-receipt',
                'href' => admin_url('invoices') . '?' . FQ_SAAS_FILTER_TAG,
                'position' => 15,
            ]);
        }

        if (staff_can('view', 'fq_saas_settings')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_settings',
                'name' => _l('fq_saas_settings'),
                'icon' => 'fa fa-cog',
                'href' => admin_url('settings?group=' . FQ_SAAS_MODULE_WHITELABEL_NAME),
                'position' => 20,
            ]);

            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_update_ext',
                'name' => _l('fq_saas_update_ext_menu'),
                'icon' => 'fa fa-plug',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/system'),
                'position' => 30,
                'badge' => $badge
            ]);

            // SaaS tab on settings page
            $settings_tab = [
                'name'     => _l('settings_group_' . FQ_SAAS_MODULE_NAME),
                'view'     => 'fq_saas/settings/index',
                'position' => -5,
                'icon'     => 'fa fa-users',
            ];

            if (method_exists($CI->app, 'add_settings_section_child'))
                $CI->app->add_settings_section_child('general', FQ_SAAS_MODULE_WHITELABEL_NAME, $settings_tab);
            else
                $CI->app_tabs->add_settings_tab(FQ_SAAS_MODULE_WHITELABEL_NAME, $settings_tab);
        }
    }

    if (fq_saas_is_tenant()) {
        // Reserved routes
        $restricted_menus = ['modules'];
        foreach ($restricted_menus as $menu) {
            $CI->app_menu->add_setup_menu_item($menu, ['name' => '', 'href' => '', 'disabled' => true, 'collapse' => true, 'children' => []]);
        }
    }
}

/**
 * Common hook to filter dangerous file extension when updating settings
 */
hooks()->add_filter('before_settings_updated', 'fq_saas_before_settings_updated_common_hook');
function fq_saas_before_settings_updated_common_hook($data)
{
    $filter_permitted_extensions = function ($extensions_string) {
        $_exts = explode(',', $extensions_string);
        if (count($_exts) > 100) throw new \Exception("Ext size too large: Error Processing Request", 1);

        $allowed_files = [];
        foreach ($_exts as $ext) {
            $ext = trim($ext);
            if (str_starts_with($ext, '.') && !in_array($ext, FQ_SAAS_DANGEROUS_EXTENSIONS)) {
                $allowed_files[] = $ext;
            }
        }
        return implode(',', $allowed_files);
    };

    if (isset($data['settings']['allowed_files'])) {
        $data['settings']['allowed_files'] = $filter_permitted_extensions($data['settings']['allowed_files']);
    }

    if (isset($data['settings']['ticket_attachments_file_extensions'])) {
        $data['settings']['ticket_attachments_file_extensions'] = $filter_permitted_extensions($data['settings']['ticket_attachments_file_extensions']);
    }

    return $data;
}



/********SAAS CLIENTS AND SUPER ADMIN HOOKS ******/
$is_tenant = fq_saas_is_tenant();
$is_admin = is_admin();
$is_client = is_client_logged_in();

// Perfex allows admin and client login on the same browser. When a dual-session user lands on
// an admin-area controller, treat them as admin (some controllers extend AdminController via
// custom base classes / traits, so rely on instanceof rather than class-name subclass lookup).
if ($is_admin && $is_client && (
    $CI instanceof AdminController
    || is_subclass_of($CI, 'AdminController')
)) {
    $is_client = false;
}

if (!$is_tenant) {

    // Add contact Permissions os super admin can create contact of a company with some saas feature control
    hooks()->add_filter('get_contact_permissions', function ($permissions) {
        return array_merge($permissions, fq_saas_contact_permissions());
    });

    // We do not want to laod module for the exluded super clients and does without any saas permission
    if ($is_client && !fq_saas_client_can_use_saas())
        return;
}

if (!$is_tenant) {

    // Log a selected plan id whenever we have it. I.e the copied package url
    $plan_identifier = fq_saas_route_id_prefix('plan');
    if (!empty($package_slug = $CI->input->post_get($plan_identifier, true))) {
        $CI->session->set_userdata([$plan_identifier => $package_slug]);
    }

    $slug_identifier = fq_saas_route_id_prefix('slug');
    $custom_domain_identifier = fq_saas_route_id_prefix('custom_domain');
    // Log package, subdomain and custom domain from registration form
    if (!$is_client) {
        $company_slug = $CI->input->post('slug', true);
        if (!empty($company_slug)) {
            $CI->session->set_userdata([$slug_identifier => $company_slug]);
        }

        $custom_domain = $CI->input->post('custom_domain', true);
        if (!empty($custom_domain)) {
            $CI->session->set_userdata([$custom_domain_identifier => $custom_domain]);
        }
    }

    // Add custom domain and subdomain from session if any
    hooks()->add_filter('fq_saas_create_instance_data', function ($data) use ($CI, $custom_domain_identifier, $slug_identifier) {
        $company_slug = $CI->session->{$slug_identifier};
        if (!empty($company_slug) && !isset($data['slug'])) {
            $data['slug'] = $company_slug;
        }

        $custom_domain = $CI->session->{$custom_domain_identifier};
        if (!empty($custom_domain) && !isset($data['custom_domain']) && fq_saas_is_valid_custom_domain($custom_domain)) {
            $data['custom_domain'] = $custom_domain;
        }

        return $data;
    });

    // Clear the session if present after success creating an instance
    hooks()->add_action('fq_saas_after_client_create_instance', function ($id) use ($CI, $plan_identifier, $custom_domain_identifier, $slug_identifier) {
        if ($id) {
            foreach ([$custom_domain_identifier, $slug_identifier, $plan_identifier] as $key) {
                if ($CI->session->has_userdata($key))
                    $CI->session->unset_userdata($key);
            }
        }
    });

    /******* SUPER CLIENT SPECIFIC HOOKS *********/
    if ($is_client) {
        // Auto subscribe to package when logged in as client
        if (fq_saas_contact_can_manage_subscription())
            fq_saas_autosubscribe();
    }

    // Use naked hooks out of $is_client to ensure availability in simulations of the client hooks from within admin panel i.e client menus
    hooks()->add_action('clients_init', 'fq_saas_clients_area_menu_items');
    function fq_saas_clients_area_menu_items()
    {
        if (is_client_logged_in()) {
            if (fq_saas_contact_can_manage_instances())
                add_theme_menu_item('companies', [
                    'name' => _l('fq_saas_client_menu_companies'),
                    'href' => site_url('clients/?companies'),
                    'position' => -2,
                    'href_attributes' => [
                        'class' => 'ps-spa',
                        'data-tab' => "#companies"
                    ]
                ]);

            if (fq_saas_contact_can_manage_subscription()) {
                add_theme_menu_item('subscription', [
                    'name' => _l('fq_saas_client_menu_subscription'),
                    'href' => fq_saas_is_single_package_mode() ? site_url('clients/my_account') : site_url('clients/?subscription'),
                    'position' => -1,
                    'href_attributes' => [
                        'class' => 'ps-spa',
                        'data-tab' => "#subscription"
                    ]
                ]);

                add_theme_menu_item('marketplace', [
                    'name' => _l('fq_saas_marketplace_client_menu'),
                    'href' => site_url('clients/my_account?view-modal=module'),
                    'position' => -1,
                ]);
            }

            // Link to the client profile (Perfex contact profile page). Added in 0.3.7 changelog.
            if (function_exists('add_theme_menu_item')) {
                add_theme_menu_item('saas_profile', [
                    'name' => _l('fq_saas_client_menu_profile'),
                    'href' => site_url('clients/profile'),
                    'position' => 0,
                ]);
            }
        }
    }
    // Add home view for client
    hooks()->add_action('client_area_after_project_overview', 'fq_saas_show_client_home');
    function fq_saas_show_client_home()
    {
        include_once(__DIR__ . '/views/client/home.php');
    }

    // Client panel scripts and widgets
    hooks()->add_action('app_customers_head', function () {
        include_once(__DIR__ . '/views/client/scripts.php');
    });




    /**
     * Register permissions for every admin-context request (priority 5, before menu builder at 50).
     *
     * Must be registered regardless of whether the current staff is a super admin — otherwise
     * non-admin staff visiting the role permission screen will not see fq_saas capabilities,
     * and staff_can() checks return false for never-registered capabilities.
     */
    hooks()->add_action('admin_init', 'fq_saas_permissions', 5);
    if (!function_exists('fq_saas_permissions')) {
        function fq_saas_permissions()
        {
            $view_only = ['capabilities' => ['view' => _l('fq_saas_permission_view')]];
            register_staff_capabilities('fq_saas_dashboard', $view_only, _l('fq_saas') . ' ' . _l('fq_saas_dashboard'));

            $crud = [
                'capabilities' => [
                    'view'   => _l('fq_saas_permission_view'),
                    'create' => _l('fq_saas_permission_create'),
                    'edit'   => _l('fq_saas_permission_edit'),
                    'delete' => _l('fq_saas_permission_delete'),
                ],
            ];
            register_staff_capabilities('fq_saas_companies',  $crud, _l('fq_saas') . ' ' . _l('fq_saas_companies'));
            register_staff_capabilities('fq_saas_packages',   $crud, _l('fq_saas') . ' ' . _l('fq_saas_packages'));
            register_staff_capabilities('fq_saas_api_user',   $crud, _l('fq_saas') . ' ' . _l('fq_saas_api_user'));
            register_staff_capabilities('fq_saas_landing',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_landing_builder'));
            register_staff_capabilities('fq_saas_cms',        $crud, _l('fq_saas') . ' ' . _l('fq_saas_cms'));
            register_staff_capabilities('fq_saas_coupons',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_coupons'));
            register_staff_capabilities('fq_saas_affiliates', $crud, _l('fq_saas') . ' ' . _l('fq_saas_affiliates'));
            register_staff_capabilities('fq_saas_domains',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_domains'));

            $view_edit = [
                'capabilities' => [
                    'view' => _l('fq_saas_permission_view'),
                    'edit' => _l('fq_saas_permission_edit'),
                ],
            ];
            register_staff_capabilities('fq_saas_settings', $view_edit, _l('fq_saas') . ' ' . _l('fq_saas_settings'));
        }
    }

    /******* SUPER ADMIN PANEL SPECIFIC HOOKS *********/
    if ($is_admin || is_staff_member()) {

        //dashboard
        if (staff_can('view', 'fq_saas_dashboard')) {
            hooks()->add_filter('get_dashboard_widgets', function ($widgets) {

                return array_merge([
                    ['path' => FQ_SAAS_MODULE_NAME . '/dashboard/overview_widget', 'container' => 'top-12'],
                    ['path' => FQ_SAAS_MODULE_NAME . '/dashboard/mrr_widget', 'container' => 'top-12'],
                ], $widgets);
            });

            hooks()->add_action('before_start_render_dashboard_content', 'fq_saas_dashboard_hook');
            function fq_saas_dashboard_hook()
            {
                get_instance()->load->view(FQ_SAAS_MODULE_NAME . '/dashboard/index', []);
            }
        }

        /** Invoice view hooks and filters */
        if (staff_can('view', 'fq_saas_packages')) {
            // Add packageid column to the datatable column and hide
            hooks()->add_filter('invoices_table_columns', 'fq_saas_invoices_table_columns');
            function fq_saas_invoices_table_columns($cols)
            {
                $cols[fq_saas_column('packageid')] = ['name' => fq_saas_column('packageid'), 'th_attrs' => ['class' => 'not_visible']];
                return $cols;
            }

            // Add packageid to selected invoice fields
            hooks()->add_filter('invoices_table_sql_columns', 'fq_saas_invoices_table_sql_columns');
            function fq_saas_invoices_table_sql_columns($fields)
            {
                $fields[] = fq_saas_column('packageid');
                return $fields;
            }

            // Add package name to recurring bill on invoices list
            hooks()->add_filter('invoices_table_row_data', 'fq_saas_invoices_table_row_data', 10, 2);
            function fq_saas_invoices_table_row_data($row, $data)
            {
                $label = _l('fq_saas_invoice_recurring_indicator');
                $col = fq_saas_column('packageid');
                if (!empty($data[$col])) {
                    $packageid = $data[$col];
                    $package_name = get_instance()->fq_saas_model->packages($packageid)->name;
                    $row[0] = str_ireplace($label, $label . ' | ' . $package_name, $row[0]);
                }
                $row[] = '';
                return $row;
            }


            // Add package selection to invoice edit/create
            hooks()->add_action('before_render_invoice_template', 'fq_saas_after_render_invoice_template_hook');
            function fq_saas_after_render_invoice_template_hook($invoice)
            {
                $col_name = fq_saas_column('packageid');
                if (empty($invoice->{$col_name})) return;
                $CI = &get_instance();
                $data = [
                    'packages' => $CI->fq_saas_model->packages(),
                    'invoice' => $invoice,
                    'col_name' => $col_name,
                    'invoice_packageid' => $invoice->{$col_name}
                ];

                $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/select_package_invoice_template', $data);
            }
        }

        /************Settings */
        // Ensure perfex saas setting is use as default when no settings group is defined
        hooks()->add_action('before_settings_group_view', 'fq_saas_before_settings_group_view_hook');
        function fq_saas_before_settings_group_view_hook($tab)
        {

            if (empty(get_instance()->input->get('group'))) { //root settings

                redirect(admin_url('settings?group=' . FQ_SAAS_MODULE_WHITELABEL_NAME));
            }
        }

        // Get modules whitelabeling settings
        hooks()->add_filter('before_settings_updated', 'fq_saas_before_settings_updated_hook');
        function fq_saas_before_settings_updated_hook($data)
        {
            $fq_saas_settings_array_fields = [
                'fq_saas_custom_modules_name',
                'fq_saas_tenants_seed_tables',
                'fq_saas_sensitive_options',
                'fq_saas_modules_marketplace',
                'fq_saas_restricted_clients_id',
                'fq_saas_custom_services',
                'fq_saas_require_invoice_payment_status',
                'fq_saas_demo_instance'
            ];
            foreach ($fq_saas_settings_array_fields as $key) {
                if (isset($data['settings'][$key])) {
                    $data['settings'][$key] = json_encode($data['settings'][$key]);
                }
            }

            $encrypted_fields = ['fq_saas_cpanel_password', 'fq_saas_plesk_password', 'fq_saas_mysql_root_password'];
            $CI = &get_instance();
            foreach ($encrypted_fields as $key => $field) {
                if (isset($data['settings'][$field]))
                    $data['settings'][$field] = $CI->encryption->encrypt($data['settings'][$field]);
            }

            return $data;
        }
    }
}


/********OTHER SPECIFIC HOOKS ******/
/**
 * Load every feature hook file exactly once. require_once guards against duplicate registration
 * when middleware_hooks.php already pulled the same file (e.g. tenant mode + admin mode in one
 * request), and the is_file() check keeps us safe if a filter injected a non-existent path.
 */
$folder_path = __DIR__ . '/hooks/';
$feature_hook_files = glob($folder_path . '*.php') ?: [];
$feature_hook_files = hooks()->apply_filters('fq_saas_extra_hook_files', $feature_hook_files);
foreach ($feature_hook_files as $file) {
    if (is_string($file) && is_file($file)) {
        require_once $file;
    }
}


// Manual run test or cron for development purpose only
if (!empty($CI->input->get(FQ_SAAS_MODULE_NAME . '_dev'))) {

    // Only permit this in development mode and user should be logged in as admin.
    $is_developer = ENVIRONMENT === 'development' && !fq_saas_is_tenant() && $is_admin;
    if (!$is_developer) {
        exit("This action can only be run in development mode");
    }

    $action = $CI->input->get('action');

    if ($action === 'test') {
        include_once(__DIR__ . '/test.php');
    }

    if ($action === 'cron') {
        fq_saas_cron();
    }
    exit();
}
