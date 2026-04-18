<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_029 extends App_module_migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $CI = &get_instance();

        fq_saas_install();

        $companies = $CI->fq_saas_model->companies();
        foreach ($companies as $company) {
            $dsn_array = false;
            try {
                $dsn_array = fq_saas_get_company_dsn($company);
            } catch (\Throwable $th) {
                //throw $th;
            }
            if ($dsn_array !== false) {
                // Complete any hanging installation
                if ($company->status !== 'deploying' && !fq_saas_is_tenant_installation_secured($company->slug, $dsn_array, true)) {

                    // Check the tenant has staff already or not

                    $result = fq_saas_get_super_admin($company->slug, $dsn_array);
                    if (isset($result->email)) return true; //already has staff - most likely fully setup or imported.

                    fq_saas_deploy_company($company, true);
                }
            }
        }
    }

    public function down()
    {
    }
}