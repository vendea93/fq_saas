<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$csrf_name = $CI->security->get_csrf_token_name();
$csrf_hash = $CI->security->get_csrf_hash();
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h4><?php echo _l('fq_saas_domain_workflow'); ?></h4>
                <p class="text-muted"><?php echo _l('fq_saas_custom_domain_hint'); ?></p>
                <div class="panel_s">
                    <div class="panel-body">
                        <p>DNS verification uses the filter <code>fq_saas_domain_dns_probe</code>. Wire cPanel/Plesk APIs under <code>modules/fq_saas/libraries/integrations/</code> and return <code>['ok' => true, 'message' => '']</code> when records match.</p>
                        <div class="form-group">
                            <label>Test domain</label>
                            <input type="text" class="form-control" id="fq_saas_dns_domain" placeholder="crm.example.com" />
                        </div>
                        <button type="button" class="btn btn-default" id="fq_saas_dns_btn"><?php echo _l('fq_saas_domain_dns_check'); ?></button>
                        <pre class="tw-mt-3" id="fq_saas_dns_out"></pre>
                    </div>
                </div>
                <p><a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/companies'); ?>"><?php echo _l('fq_saas_companies'); ?></a></p>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('fq_saas_dns_btn').addEventListener('click', function() {
    var d = document.getElementById('fq_saas_dns_domain').value;
    fetch('<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/domains/dns_probe'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'domain=' + encodeURIComponent(d) + '&<?php echo $csrf_name; ?>=' + encodeURIComponent('<?php echo $csrf_hash; ?>')
    }).then(function(r){ return r.json(); }).then(function(j){
        document.getElementById('fq_saas_dns_out').textContent = JSON.stringify(j, null, 2);
    });
});
</script>
<?php init_tail(); ?>
