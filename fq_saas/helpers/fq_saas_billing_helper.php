<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Landlord-only billing hooks (proration, overdue → suspend). Extend in place.
 */
function fq_saas_billing_register_hooks(): void
{
    if (fq_saas_is_tenant()) {
        return;
    }

    hooks()->add_filter('before_invoice_added', 'fq_saas_billing_before_invoice_added', 5);
    hooks()->add_action('invoice_overdue_reminder_sent', 'fq_saas_billing_on_invoice_overdue_reminder');
    hooks()->add_action('after_payment_added', 'fq_saas_billing_after_payment_added', 20, 1);
    hooks()->add_action('invoice_status_changed', 'fq_saas_billing_invoice_status_changed', 20, 1);
}

/**
 * @param array $data Perfex hook payload
 * @return array
 */
function fq_saas_billing_before_invoice_added($data)
{
    Fq_saas_kernel::dispatch('before_invoice_added', ['data' => $data]);
    return $data;
}

/**
 * @param array $data ['invoice_id' => int, ...]
 */
function fq_saas_billing_on_invoice_overdue_reminder($data): void
{
    fq_saas_log('invoice_overdue_reminder', is_array($data) ? $data : ['data' => $data]);
}

/**
 * @param int $payment_id
 */
function fq_saas_billing_after_payment_added($payment_id): void
{
    $CI = &get_instance();
    $CI->load->model('payments_model');
    $payment = $CI->payments_model->get((int) $payment_id);
    if (!$payment) {
        return;
    }

    $payload = [
        'payment_id' => (int) $payment_id,
        'invoice_id' => (int) $payment->invoiceid,
    ];
    fq_saas_log('invoice_payment_recorded', $payload);

    if (function_exists('fq_saas_coupon_increment_uses')) {
        $code = $CI->session->userdata('fq_saas_checkout_coupon');
        if (!empty($code)) {
            fq_saas_coupon_increment_uses((string) $code);
        }
    }
}

/**
 * @param array $data ['invoice_id' => int, 'status' => mixed]
 */
function fq_saas_billing_invoice_status_changed($data): void
{
    if (!is_array($data)) {
        return;
    }
    fq_saas_log('invoice_status_changed', $data);
}
