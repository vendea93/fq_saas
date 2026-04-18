<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h4><?php echo _l('fq_saas_feature_limits_compare'); ?></h4>
                <p class="text-muted"><?php echo html_escape($path); ?></p>
                <p>Edit <code>config/feature_limits.php</code> to cap quotas per plan slug (filter <code>fq_saas_feature_limits_plan_key</code>).</p>
                <pre class="tw-bg-neutral-50 tw-p-3 tw-rounded"><?php echo html_escape(json_encode($limits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/packages'); ?>" class="btn btn-default"><?php echo _l('fq_saas_packages'); ?></a>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
