<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mb-3"><?php echo _l('fq_saas_coupons'); ?></h4>
                <?php echo form_open(admin_url(FQ_SAAS_ROUTE_NAME . '/coupons/edit/' . ($coupon->id ?? ''))); ?>
                <input type="hidden" name="id" value="<?php echo (int) ($coupon->id ?? 0); ?>" />
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo render_input('code', 'Code', $coupon->code ?? '', 'text', ['required' => true]); ?>
                        <?php echo render_select('type', [['id' => 'percent', 'name' => 'percent'], ['id' => 'fixed', 'name' => 'fixed']], ['id', 'name'], 'Type', $coupon->type ?? 'percent'); ?>
                        <?php echo render_input('value', 'Value', $coupon->value ?? '0', 'number', ['step' => '0.01']); ?>
                        <?php echo render_input('max_uses', 'Max uses', $coupon->max_uses ?? '', 'number'); ?>
                        <?php echo render_input('expires_at', _l('expiry_date'), $coupon->expires_at ?? '', 'text', ['placeholder' => 'YYYY-MM-DD']); ?>
                        <?php echo render_input('package_ids', 'Package IDs (comma)', $coupon->package_ids ?? '', 'text'); ?>
                        <?php echo render_input('stripe_coupon_id', _l('fq_saas_stripe_coupon_id'), $coupon->stripe_coupon_id ?? '', 'text'); ?>
                        <p class="text-muted"><?php echo _l('fq_saas_stripe_coupon_id_hint'); ?></p>
                        <div class="checkbox">
                            <input type="checkbox" name="active" value="1" id="active" <?php echo !empty($coupon->active) ? 'checked' : ''; ?> />
                            <label for="active"><?php echo _l('active'); ?></label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
