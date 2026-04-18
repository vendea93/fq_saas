<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$col = fq_saas_column('packageid');
$CI->db->where($col . ' >', 0);
$CI->db->group_start();
$CI->db->where('recurring >', 0);
$CI->db->or_where('subscription_id >', 0);
$CI->db->group_end();
$active_subs = (int) $CI->db->count_all_results(db_prefix() . 'invoices');
?>
<div class="widget relative tw-mb-8" id="widget-fq_saas_mrr" data-name="<?php echo _l('fq_saas_mrr_active_subscriptions'); ?>">
    <div class="panel_s">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <p class="tw-font-medium tw-mb-0 tw-p-1.5"><?php echo _l('fq_saas_mrr_active_subscriptions'); ?></p>
            <hr class="-tw-mx-3 tw-mt-3 tw-mb-3">
            <div class="tw-text-3xl tw-font-semibold tw-text-neutral-800"><?php echo $active_subs; ?></div>
            <p class="text-muted tw-text-sm tw-mb-0"><?php echo _l('fq_saas_recurring_invoices'); ?> (<?php echo html_escape($col); ?> &gt; 0)</p>
        </div>
    </div>
</div>
