<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Domains extends AdminController
{
    public function index()
    {
        if (!staff_can('view', 'fq_saas_domains')) {
            return access_denied('fq_saas_domains');
        }

        $data['title'] = _l('fq_saas_domain_workflow');
        $this->load->view('domain/workflow', $data);
    }

    /**
     * AJAX: POST domain string, returns fq_saas_domain_dns_probe result JSON.
     */
    public function dns_probe()
    {
        if (!staff_can('view', 'fq_saas_domains')) {
            show_404();
        }
        $domain = $this->input->post('domain', true) ?: $this->input->get('domain', true);
        header('Content-Type: application/json');
        echo json_encode(fq_saas_domain_dns_probe((string) $domain));
    }
}
