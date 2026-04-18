<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h4 class="tw-mb-3"><?php echo _l('fq_saas_affiliates'); ?></h4>
                <?php echo form_open(admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates/edit/' . ($affiliate->id ?? ''))); ?>
                <input type="hidden" name="id" value="<?php echo (int) ($affiliate->id ?? 0); ?>" />
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo render_input('clientid', 'Client ID', $affiliate->clientid ?? '', 'number', ['required' => true]); ?>
                        <?php echo render_input('code', 'Referral code', $affiliate->code ?? '', 'text', ['required' => true]); ?>
                        <?php echo render_input('commission_percent', 'Commission %', $affiliate->commission_percent ?? '0', 'number', ['step' => '0.01']); ?>
                        <?php echo render_select('payout_status', [['id' => 'none', 'name' => 'none'], ['id' => 'pending', 'name' => 'pending'], ['id' => 'paid', 'name' => 'paid']], ['id', 'name'], 'Payout', $affiliate->payout_status ?? 'none'); ?>
                        <?php echo render_select('status', [['id' => 'active', 'name' => 'active'], ['id' => 'disabled', 'name' => 'disabled']], ['id', 'name'], _l('status'), $affiliate->status ?? 'active'); ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
