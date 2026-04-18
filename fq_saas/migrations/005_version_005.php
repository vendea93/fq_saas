<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_005 extends App_module_migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        fq_saas_uninstall();
        fq_saas_install();
        update_option('fq_saas_force_redirect_to_dashboard', '1');
    }

    public function down()
    {
    }
}
