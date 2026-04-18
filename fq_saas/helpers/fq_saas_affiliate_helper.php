<?php

defined('BASEPATH') or exit('No direct script access allowed');

function fq_saas_affiliate_register_hooks(): void
{
    hooks()->add_action('contact_created', 'fq_saas_affiliate_bind_referrer_to_client', 25, 1);
    hooks()->add_action('fq_saas_invoice_payment_recorded', 'fq_saas_affiliate_on_invoice_payment', 10, 1);
}

/**
 * @param int $contact_id
 */
function fq_saas_affiliate_bind_referrer_to_client($contact_id): void
{
    if (fq_saas_is_tenant()) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('clients_model');
    $contact = $CI->clients_model->get_contact((int) $contact_id);
    if (!$contact || empty($contact->userid)) {
        return;
    }

    $code = $CI->session->userdata('fq_saas_ref_code');
    if (empty($code)) {
        return;
    }

    $CI->load->model('fq_saas/fq_saas_extensions_model');
    $aff = $CI->fq_saas_extensions_model->get_affiliate_by_code((string) $code);
    if (!$aff || (int) $aff->clientid === (int) $contact->userid) {
        return;
    }

    fq_saas_get_or_save_client_metadata((int) $contact->userid, [
        'referred_from_affiliate' => (int) $aff->id,
    ]);
}

/**
 * @param array{invoice_id?: int, payment_id?: int} $payload
 */
function fq_saas_affiliate_on_invoice_payment(array $payload): void
{
    if (fq_saas_is_tenant() || empty($payload['invoice_id'])) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $invoice = $CI->invoices_model->get((int) $payload['invoice_id']);
    if (!$invoice || empty($invoice->clientid)) {
        return;
    }

    $meta = (object) fq_saas_get_or_save_client_metadata((int) $invoice->clientid);
    if (empty($meta->referred_from_affiliate)) {
        return;
    }

    $affId = (int) $meta->referred_from_affiliate;
    $CI->load->model('fq_saas/fq_saas_extensions_model');
    $aff = $CI->db->get_where(fq_saas_extensions_table('affiliates'), ['id' => $affId])->row();
    if (!$aff || ($aff->status ?? '') !== 'active') {
        return;
    }

    $pct = (float) ($aff->commission_percent ?? 0);
    if ($pct <= 0) {
        return;
    }

    $amount = (float) ($invoice->subtotal ?? 0);
    if ($amount <= 0) {
        return;
    }

    $credit = round($amount * ($pct / 100.0), 2);
    if ($credit > 0) {
        $CI->fq_saas_extensions_model->credit_affiliate_balance($affId, $credit);
        fq_saas_log('affiliate_commission_credited', [
            'affiliate_id' => $affId,
            'invoice_id'   => (int) $invoice->id,
            'amount'       => $credit,
        ]);
    }
}
