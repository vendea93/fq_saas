<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-mb-3">
                    <h4 class="tw-m-0"><?php echo _l('fq_saas_coupons'); ?></h4>
                    <?php if (staff_can('create', 'fq_saas_coupons')) { ?>
                    <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/coupons/edit'); ?>" class="btn btn-primary"><?php echo _l('new'); ?></a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Stripe</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($coupons as $c) { ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/coupons/edit/' . $c->id); ?>"><?php echo html_escape($c->code); ?></a></td>
                                        <td><?php echo html_escape($c->type); ?></td>
                                        <td><?php echo html_escape($c->value); ?></td>
                                        <td><?php echo html_escape($c->stripe_coupon_id ?? ''); ?></td>
                                        <td>
                                            <?php if (staff_can('delete', 'fq_saas_coupons')) { ?>
                                            <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/coupons/delete/' . $c->id); ?>" class="text-danger _delete"><?php echo _l('delete'); ?></a>
                                            <?php } ?>
                                        </td>
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
