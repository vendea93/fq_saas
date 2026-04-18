<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-mb-3">
                    <h4 class="tw-m-0"><?php echo _l('fq_saas_landing_builder'); ?></h4>
                    <?php if (staff_can('create', 'fq_saas_landing')) { ?>
                    <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/landing_builder/edit'); ?>" class="btn btn-primary"><?php echo _l('new'); ?></a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('slug'); ?></th>
                                        <th><?php echo _l('name'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($landing as $p) { ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/landing_builder/edit/' . $p->id); ?>"><?php echo html_escape($p->slug); ?></a></td>
                                        <td><?php echo html_escape($p->title); ?></td>
                                        <td><?php echo html_escape($p->status); ?></td>
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
