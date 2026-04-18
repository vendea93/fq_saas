<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Coupons extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fq_saas/fq_saas_extensions_model');
    }

    public function index()
    {
        if (!staff_can('view', 'fq_saas_coupons')) {
            return access_denied('fq_saas_coupons');
        }

        $data['title']   = _l('fq_saas_coupons');
        $data['coupons'] = $this->fq_saas_extensions_model->coupons();
        $this->load->view('coupons/manage', $data);
    }

    public function edit($id = '')
    {
        if (!staff_can('view', 'fq_saas_coupons')) {
            return access_denied('fq_saas_coupons');
        }

        if ($this->input->post()) {
            if (!staff_can('edit', 'fq_saas_coupons')) {
                return access_denied('fq_saas_coupons');
            }
            $post = $this->input->post(null, true);
            $save = [
                'id'               => (int) ($post['id'] ?? 0),
                'code'             => $post['code'] ?? '',
                'type'             => $post['type'] ?? 'percent',
                'value'            => (float) ($post['value'] ?? 0),
                'max_uses'         => $post['max_uses'] === '' ? null : (int) $post['max_uses'],
                'expires_at'       => $post['expires_at'] === '' ? null : $post['expires_at'],
                'package_ids'      => $post['package_ids'] ?? '',
                'stripe_coupon_id' => $post['stripe_coupon_id'] ?? null,
                'active'           => !empty($post['active']) ? 1 : 0,
            ];
            $newId = $this->fq_saas_extensions_model->save_coupon($save);
            fq_saas_log('coupon_saved', ['id' => $newId]);
            set_alert('success', _l('updated_successfully', _l('fq_saas_coupons')));
            redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/coupons/edit/' . $newId));
        }

        $data['title']  = _l('fq_saas_coupons');
        $data['coupon'] = $id ? $this->fq_saas_extensions_model->get_coupon((int) $id) : null;
        $this->load->view('coupons/form', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fq_saas_coupons')) {
            return access_denied('fq_saas_coupons');
        }
        $this->fq_saas_extensions_model->delete_coupon((int) $id);
        set_alert('success', _l('deleted', _l('fq_saas_coupons')));
        redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/coupons'));
    }
}
