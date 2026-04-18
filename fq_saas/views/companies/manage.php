<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create', 'fq_saas_companies')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/companies/create'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('fq_saas_new_company'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s tenants-table">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('fq_saas_name'),
                            _l('fq_saas_clients_list_company'),
                            _l('fq_saas_company_status'),
                            _l('fq_saas_subscription'),
                            _l('fq_saas_data_location'),
                            _l('fq_saas_modules'),
                            _l('fq_saas_date_created'),
                            _l('fq_saas_options'),
                        ], 'companies'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";
    $(function() {
        initDataTable('.table-companies', window.location.href, undefined, [5], undefined, [6, "desc"]);
    });
</script>
</body>

</html>