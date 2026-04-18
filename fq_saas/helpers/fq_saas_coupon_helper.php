<?php

defined('BASEPATH') or exit('No direct script access allowed');

function fq_saas_coupon_register_filters(): void
{
    hooks()->add_filter('fq_saas_stripe_subscription_params', 'fq_saas_coupon_apply_to_stripe_params', 10, 2);
}

/**
 * @param array $params Stripe subscription params
 * @param array $context clientid, packageid, subscription (object)
 * @return array
 */
function fq_saas_coupon_apply_to_stripe_params(array $params, array $context): array
{
    $CI = &get_instance();
    $code = $CI->session->userdata('fq_saas_checkout_coupon');
    if (empty($code) || fq_saas_is_tenant()) {
        return $params;
    }

    $row = fq_saas_coupon_lookup(strtoupper(trim((string) $code)), (int) ($context['packageid'] ?? 0));
    if (!$row || empty($row['stripe_coupon_id'])) {
        return $params;
    }

    $params['discounts'] = [['coupon' => $row['stripe_coupon_id']]];

    return $params;
}

/**
 * @return array<string, mixed>|null
 */
function fq_saas_coupon_lookup(string $code, int $package_id = 0): ?array
{
    if ($code === '' || fq_saas_is_tenant()) {
        return null;
    }

    $CI    = &get_instance();
    $table = fq_saas_extensions_table('coupons');
    if (!$CI->db->table_exists($table)) {
        return null;
    }

    $row = $CI->db->get_where($table, ['code' => $code, 'active' => 1])->row_array();
    if (!$row) {
        return null;
    }

    if (!empty($row['expires_at']) && $row['expires_at'] < date('Y-m-d')) {
        return null;
    }

    if (!empty($row['max_uses']) && (int) $row['uses'] >= (int) $row['max_uses']) {
        return null;
    }

    if ($package_id > 0 && !empty($row['package_ids'])) {
        $allowed = array_filter(array_map('intval', explode(',', $row['package_ids'])));
        if (count($allowed) && !in_array($package_id, $allowed, true)) {
            return null;
        }
    }

    return $row;
}

function fq_saas_coupon_increment_uses(string $code): void
{
    if ($code === '' || fq_saas_is_tenant()) {
        return;
    }
    $CI    = &get_instance();
    $table = fq_saas_extensions_table('coupons');
    if (!$CI->db->table_exists($table)) {
        return;
    }
    $CI->db->where('code', strtoupper(trim($code)));
    $CI->db->set('uses', 'uses+1', false);
    $CI->db->update($table);
}
