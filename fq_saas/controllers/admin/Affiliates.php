<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Affiliates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fq_saas/fq_saas_extensions_model');
    }

    public function index()
    {
        if (!staff_can('view', 'fq_saas_affiliates')) {
            return access_denied('fq_saas_affiliates');
        }

        $data['title']       = _l('fq_saas_affiliates');
        $data['affiliates']  = $this->fq_saas_extensions_model->affiliates();
        $this->load->view('affiliates/manage', $data);
    }

    public function edit($id = '')
    {
        if (!staff_can('view', 'fq_saas_affiliates')) {
            return access_denied('fq_saas_affiliates');
        }

        if ($this->input->post()) {
            if (!staff_can('edit', 'fq_saas_affiliates')) {
                return access_denied('fq_saas_affiliates');
            }
            $post = $this->input->post(null, true);
            $save = [
                'id'                  => (int) ($post['id'] ?? 0),
                'clientid'           => (int) ($post['clientid'] ?? 0),
                'code'               => $post['code'] ?? '',
                'commission_percent' => (float) ($post['commission_percent'] ?? 0),
                'payout_status'      => $post['payout_status'] ?? 'none',
                'status'             => $post['status'] ?? 'active',
            ];
            $newId = $this->fq_saas_extensions_model->save_affiliate($save);
            fq_saas_log('affiliate_saved', ['id' => $newId]);
            set_alert('success', _l('updated_successfully', _l('fq_saas_affiliates')));
            redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates/edit/' . $newId));
        }

        $data['title']      = _l('fq_saas_affiliates');
        $data['affiliate']  = null;
        if ($id) {
            $row = $this->db->get_where(fq_saas_extensions_table('affiliates'), ['id' => (int) $id])->row();
            $data['affiliate'] = $row;
        }
        $this->load->view('affiliates/form', $data);
    }
}
