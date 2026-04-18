<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Landing_builder extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fq_saas/fq_saas_extensions_model');
    }

    public function index()
    {
        if (!staff_can('view', 'fq_saas_landing')) {
            return access_denied('fq_saas_landing');
        }

        $data['title']   = _l('fq_saas_landing_builder');
        $data['landing'] = $this->fq_saas_extensions_model->landing_pages();
        $this->load->view('landing_builder/manage', $data);
    }

    public function edit($id = '')
    {
        if (!staff_can('view', 'fq_saas_landing')) {
            return access_denied('fq_saas_landing');
        }

        if ($this->input->post()) {
            if (!staff_can('edit', 'fq_saas_landing')) {
                return access_denied('fq_saas_landing');
            }
            $post = $this->input->post(null, true);
            $save = [
                'id'         => (int) ($post['id'] ?? 0),
                'slug'       => $post['slug'] ?? '',
                'title'      => $post['title'] ?? '',
                'status'     => $post['status'] ?? 'draft',
                'body_html'  => $post['body_html'] ?? '',
                'body_json'  => $post['body_json'] ?? '',
                'revisions'  => $post['revisions'] ?? '',
            ];
            $newId = $this->fq_saas_extensions_model->save_landing_page($save);
            fq_saas_log('landing_page_saved', ['id' => $newId, 'slug' => $save['slug']]);
            set_alert('success', _l('updated_successfully', _l('fq_saas_landing_builder')));
            redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/landing_builder/edit/' . $newId));
        }

        $data['title'] = _l('fq_saas_landing_builder');
        $data['page']  = $id ? $this->fq_saas_extensions_model->get_landing_page((int) $id) : null;
        $this->load->view('landing_builder/form', $data);
    }
}
