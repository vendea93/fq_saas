<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_020 extends App_module_migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        add_option('fq_saas_latest_version', FQ_SAAS_VERSION_NUMBER);
        add_option('fq_saas_exempted_clients_id', '', 1);

        fq_saas_install();

        $this->migrationv007Dropped();
    }

    private function migrationv007Dropped()
    {
        $CI = &get_instance();

        /** Compile all data centers */
        $databases = [
            fq_saas_master_dsn()
        ];

        $packages = $CI->fq_saas_model->packages();
        foreach ($packages as $invoice) {

            if (empty($invoice->db_pools)) continue;

            if (!is_string($invoice->db_pools)) {
                $invoice->db_pools = fq_saas_dsn_to_string($invoice->db_pools);
            }
            $dsn = fq_saas_parse_dsn($invoice->db_pools);
            $databases[] = $dsn;
        }

        $companies = $CI->fq_saas_model->companies();
        foreach ($companies as $key => $company) {

            try {
                $dsn = fq_saas_get_company_dsn($company);

                //skip companies running on same db as master
                if ($dsn['host'] == APP_DB_HOSTNAME_DEFAULT && $dsn['dbname'] == APP_DB_NAME_DEFAULT) {
                    continue;
                }

                $databases[] = $dsn;
            } catch (\Throwable $th) {
                //throw $th;
            }
        }


        // Validate all data center
        foreach ($databases as $key => $dsn) {
            try {
                if (($valid = fq_saas_is_valid_dsn($dsn)) !== true)
                    unset($databases[$key]);
            } catch (\Throwable $th) {
                throw new \Exception("Invalid DSN center. Can not proceed with migraiton. Ensure all db pools are valid or remove invalid pools manually: " . fq_saas_dsn_to_string($dsn) . ". " . $th->getMessage(), 1);
            }
        }

        $dbprefix = fq_saas_master_db_prefix();

        foreach ($databases as $index => $dsn) {
            $current_index = $index;
            if (isset($_SESSION['_migration_last_index']))
                $current_index = $_SESSION['_migration_last_index'];

            if ($current_index > $index) continue;

            // Get the list of tables starting with "tbl_" in the tenant database
            $sql_tables = "SHOW TABLES LIKE '%" . $dbprefix . "%'";
            $tables = fq_saas_raw_query($sql_tables, $dsn, true);

            if (empty($tables)) {
                echo "Skipping " . $dsn['dbname'] . " : no table founds<br/>";
                continue;
            }

            $col = array_key_first((array)$tables[0]);

            // Drop the old perfex_saas_tenant_id column
            foreach ($tables as $k => $row) {
                $table = $row->{$col};
                try {
                    fq_saas_raw_query("ALTER TABLE $table DROP `perfex_saas_tenant_id`", $dsn, false, false, null, true, false);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }

            $_SESSION['_migration_last_index'] = $index;
        }
    }

    public function down()
    {
    }
}
