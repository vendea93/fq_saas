<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cms extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fq_saas/fq_saas_extensions_model');
    }

    public function index($type = '')
    {
        if (!staff_can('view', 'fq_saas_cms')) {
            return access_denied('fq_saas_cms');
        }

        $data['title'] = _l('fq_saas_cms');
        $data['type']   = $type;
        $data['pages']  = $this->fq_saas_extensions_model->cms_pages($type);
        $this->load->view('cms/manage', $data);
    }

    public function edit($id = '')
    {
        if (!staff_can('view', 'fq_saas_cms')) {
            return access_denied('fq_saas_cms');
        }

        if ($this->input->post()) {
            if (!staff_can('edit', 'fq_saas_cms')) {
                return access_denied('fq_saas_cms');
            }
            $post = $this->input->post(null, true);
            $save = [
                'id'         => (int) ($post['id'] ?? 0),
                'slug'       => $post['slug'] ?? '',
                'type'       => $post['type'] ?? 'page',
                'title'      => $post['title'] ?? '',
                'status'     => $post['status'] ?? 'draft',
                'body_html'  => $post['body_html'] ?? '',
            ];
            $newId = $this->fq_saas_extensions_model->save_cms_page($save);
            fq_saas_log('cms_page_saved', ['id' => $newId, 'slug' => $save['slug'], 'type' => $save['type']]);
            set_alert('success', _l('updated_successfully', _l('fq_saas_cms')));
            redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/cms/edit/' . $newId));
        }

        $data['title'] = _l('fq_saas_cms');
        $data['page']  = $id ? $this->fq_saas_extensions_model->get_cms_page((int) $id) : null;
        $this->load->view('cms/form', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fq_saas_cms')) {
            return access_denied('fq_saas_cms');
        }
        $id = (int) $id;
        $ok = $this->fq_saas_extensions_model->delete_cms_page($id);
        if ($ok) {
            fq_saas_log('cms_page_deleted', ['id' => $id]);
            set_alert('success', _l('deleted', _l('fq_saas_cms')));
        } else {
            set_alert('danger', _l('fq_saas_error_completing_action'));
        }
        return redirect(admin_url(FQ_SAAS_ROUTE_NAME . '/cms'));
    }
}
