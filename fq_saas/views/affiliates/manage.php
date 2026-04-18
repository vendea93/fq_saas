<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-mb-3">
                    <h4 class="tw-m-0"><?php echo _l('fq_saas_affiliates'); ?></h4>
                    <?php if (staff_can('create', 'fq_saas_affiliates')) { ?>
                    <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates/edit'); ?>" class="btn btn-primary"><?php echo _l('new'); ?></a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client ID</th>
                                        <th>Code</th>
                                        <th>%</th>
                                        <th>Balance</th>
                                        <th>Payout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($affiliates as $a) { ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates/edit/' . $a->id); ?>"><?php echo (int) $a->clientid; ?></a></td>
                                        <td><?php echo html_escape($a->code); ?></td>
                                        <td><?php echo html_escape($a->commission_percent); ?></td>
                                        <td><?php echo html_escape($a->balance); ?></td>
                                        <td><?php echo html_escape($a->payout_status ?? 'none'); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
