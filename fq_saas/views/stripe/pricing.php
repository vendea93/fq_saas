<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">

        <?php if (empty($packages) && staff_can('create', 'fq_saas_packages')) { ?>
        <div class="panel_s">
            <div class="panel_body text-center tw-py-4">
                <div class="tw-mb-4"><?= _l('fq_saas_create_your_first_package'); ?></div>
                <a href="<?php echo admin_url(FQ_SAAS_ROUTE_NAME . '/packages/create'); ?>" class="btn btn-primary">
                    <i class="fa-regular fa-plus tw-mr-1"></i>
                    <?php echo _l('fq_saas_new_package'); ?>
                </a>
            </div>
        </div>
        <?php } ?>

        <?php if (!empty($packages) && staff_can('edit', 'fq_saas_packages')) : ?>
        <div class="tw-flex tw-justify-end tw-mb-3">
            <button type="button" id="perfex-saas-mass-sync-stripe" class="btn btn-default">
                <i class="fa-solid fa-sync tw-mr-1"></i>
                <?= _l('fq_saas_stripe_mass_sync'); ?>
            </button>
        </div>
        <?php endif; ?>

        <?php if (!empty($packages)) : ?>
        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
            <div class="scroller arrow-left tw-mt-px"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right tw-mt-px"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <?php
                        // Define tab data as an array for better maintainability
                        $tabs = [];
                        foreach ($packages as $key => $package) {
                            if (fq_saas_stripe_package_recurring_is_over_three_years($package)) continue;

                            $tabs[] = ['id' => $package->slug, 'label' => $package->name, 'package' => $package];
                        }

                        $activeTabId = $this->input->get('tab');
                        if (empty($activeTabId))
                            $activeTabId = $tabs[0]['id'];


                        // Loop through the tabs to generate navigation
                        foreach ($tabs as $tab) {
                            $isActive = ($tab['id'] === $activeTabId) ? 'active' : '';
                        ?>
                    <li role="presentation" class="<?php echo $isActive; ?>">
                        <a href="#<?php echo $tab['id']; ?>" aria-controls="<?php echo $tab['id']; ?>" role="tab"
                            data-toggle="tab" class="stripe-tab-anchor">
                            <?php echo $tab['label']; ?>
                        </a>
                    </li>
                    <?php
                        }
                        ?>
                </ul>
            </div>
        </div>

        <div class="tab-content mtop30">
            <?php
                // Loop through the tab content files
                foreach ($tabs as $tab) {
                    $tabId = $tab['id'];
                    $tabFile = '_form.php';
                    $isActive = ($tabId === $activeTabId) ? 'active' : '';
                ?>
            <div role="tabpanel" class="tab-pane <?php echo $isActive; ?>" id="<?php echo $tabId; ?>">
                <?php $this->load->view($tabFile, ['package' => $tab['package'], 'stripe_plans' => $stripe_plans, 'stripe_tax_rates' => $stripe_tax_rates]); ?>
            </div>
            <?php
                }
                ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php init_tail(); ?>
<script>
$(document).ready(function() {
    $('input.onoffswitch-checkbox.enable').on('change', function() {
        let cards = $(this).closest('.tab-pane').find('.enable-deps');
        if ($(this).prop('checked')) cards.show()
        else cards.hide();
    }).trigger('change');

    $('input.onoffswitch-checkbox.sync').on('change', function() {
        let cards = $(this).closest('.tab-pane').find('.sync-deps');
        if ($(this).prop('checked')) cards.hide()
        else cards.show();
    }).trigger('change');

    $(".stripe-tab-anchor").on('click', function() {
        var queryParams = new URLSearchParams(window.location.search);
        queryParams.set("tab", $(this).attr('aria-controls'));
        history.replaceState(null, null, "?" + queryParams.toString());
    });

    // Mass sync all Stripe-enabled packages (0.3.8)
    $('#perfex-saas-mass-sync-stripe').on('click', function () {
        var $btn = $(this);
        if (!confirm(<?= json_encode(_l('fq_saas_stripe_mass_sync_confirm'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)) return;

        $btn.prop('disabled', true).find('i').addClass('fa-spin');

        $.ajax({
            url: <?= json_encode(admin_url(FQ_SAAS_ROUTE_NAME . '/stripe_pricing/mass_sync'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            method: 'POST',
            dataType: 'json',
            data: { <?= json_encode($this->security->get_csrf_token_name()); ?>: <?= json_encode($this->security->get_csrf_hash()); ?> }
        }).done(function (resp) {
            if (resp && resp.success && resp.report) {
                var r = resp.report;
                var msg = 'Synced: ' + r.synced + ' / Skipped: ' + r.skipped + ' / Failed: ' + r.failed;
                if (r.failed > 0 && r.errors && r.errors.length) {
                    msg += '\n\nErrors:\n' + r.errors.map(function (e) { return '- ' + e.package_name + ': ' + e.error; }).join('\n');
                }
                alert(msg);
            } else {
                alert('Sync finished with an unexpected response.');
            }
        }).fail(function (xhr) {
            var err = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Mass sync failed.';
            alert(err);
        }).always(function () {
            $btn.prop('disabled', false).find('i').removeClass('fa-spin');
        });
    });

})
</script>
</body>

</html>