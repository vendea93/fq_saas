<?php

defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="tw-flex tw-flex-col">

    <!-- Plesk settings-->
    <h3><?= _l('fq_saas_plesk_settings'); ?></h3>
    <p class="tw-mb-4"><?= _l('fq_saas_plesk_settings_hint'); ?></p>
    <div class="row mtop25 tw-mb-4">
        <div class="col-md-5 border-right">
            <span><?php echo _l('fq_saas_enabled?'); ?></span>
        </div>
        <div class="col-md-2">
            <div class="onoffswitch">
                <input type="checkbox" id="fq_saas_plesk_enabled" data-perm-id="3" class="onoffswitch-checkbox"
                    <?php if (get_option('fq_saas_plesk_enabled') == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?> value="1" name="settings[fq_saas_plesk_enabled]">
                <label class="onoffswitch-label" for="fq_saas_plesk_enabled"></label>
            </div>
        </div>
    </div>
    <?php
    $CI = &get_instance();
    $fields = ['fq_saas_plesk_host', 'fq_saas_plesk_primary_domain', 'fq_saas_plesk_username', 'fq_saas_plesk_password'];
    $encrypted_fields = ['fq_saas_plesk_password'];
    $attrs = [
        'fq_saas_plesk_password' => ['type' => 'password'],
        'fq_saas_plesk_primary_domain' => ['data-default-value' => fq_saas_get_saas_default_host()]
    ];

    foreach ($fields as $key => $field) {
        $value = get_option($field);
        if (empty($value)) {
            $value = $attrs[$field]['data-default-value'] ?? '';
        }

        if (!empty($value) && in_array($field, $encrypted_fields))
            $value = $CI->encryption->decrypt($value);

        $label = _l($field) . fq_saas_form_label_hint($field . '_hint');
        echo render_input('settings[' . $field . ']', $label, $value, $attrs[$field]['type'] ?? 'text', $attrs[$field] ?? []);
    } ?>

    <?php $alias_domain_enabled = get_option('fq_saas_plesk_enable_aliasdomain') == '1'; ?>
    <div class="row mtop25 tw-mb-4">
        <div class="col-md-5 border-right">
            <span><?php echo _l('fq_saas_plesk_enable_aliasdomain'); ?></span>
        </div>
        <div class="col-md-2">
            <input type="hidden" value="<?= $alias_domain_enabled ? '1' : '0'; ?>"
                id="fq_saas_plesk_enable_aliasdomain" name="settings[fq_saas_plesk_enable_aliasdomain]" />
            <div class="onoffswitch">
                <input type="checkbox" id="fq_saas_plesk_enable_aliasdomain_switch" class="onoffswitch-checkbox"
                    <?= $alias_domain_enabled ? 'checked' : ''; ?> value="1">
                <label class="onoffswitch-label" for="fq_saas_plesk_enable_aliasdomain_switch"></label>
            </div>
        </div>
        <div class="col-md-12 text-warning"><?= _l('fq_saas_plesk_enable_aliasdomain_hint'); ?></div>
    </div>

    <?php $addondoamin_enabled = get_option('fq_saas_plesk_enable_aliasdomain') == '1'; ?>
    <div id="addondomain_deps" class="<?= $addondoamin_enabled ? '' : 'hidden'; ?>">

        <?php
        $key = 'fq_saas_plesk_addondomain_mode';
        $value = get_option($key);
        $value = empty($value) ? 'all' : $value;
        echo render_select(
            'settings[' . $key . ']',
            [
                ['key' => 'all', 'label' => _l('fq_saas_integration_addondomain_mode_all')],
                ['key' => 'subdomain', 'label' => _l('fq_saas_integration_addondomain_mode_subdomain')],
                ['key' => 'customdomain', 'label' => _l('fq_saas_integration_addondomain_mode_customdomain')],
            ],
            ['key', ['label']],
            fq_saas_input_label_with_hint('fq_saas_integration_addondomain_mode'),
            $value,
            [],
            [],
            '',
            '',
            false
        );
        ?>
    </div>

    <div class="tw-flex tw-justify-end">
        <button onclick="testPleskIntegration()" class="btn btn-danger btn-sm"
            type="button"><?= _l('fq_saas_test'); ?></button>
    </div>

    <div class="tw-mt-4 tw-mb-4">
        <hr />
    </div>

</div>

<script>
"use strict";

function testPleskIntegration() {
    const data = {};
    const button = $("#plesk button");

    $("#plesk input").each(function() {
        data[this.name] = this.value;
    });

    button.attr('disabled', true);
    $.post(admin_url + '<?= FQ_SAAS_ROUTE_NAME; ?>/integrations/test_plesk', data)
        .done(function(response) {
            button.removeAttr('disabled');
            response = JSON.parse(response);
            alert_float(response.status, response.message, 10000);
        }).fail(function(error) {
            button.removeAttr('disabled');
        });
}


// Ensure host and primary domain dont have scheme
document.getElementById('settings[fq_saas_plesk_host]').addEventListener('change', function() {
    let value = this.value;
    if (value.indexOf('://') !== -1) {
        this.value = value.split('://')[1];
    }
});
document.getElementById('settings[fq_saas_plesk_primary_domain]').addEventListener('change', function() {
    let value = this.value;
    if (value.indexOf('://') !== -1) {
        this.value = value.split('://')[1];
    }
});
document.getElementById('fq_saas_plesk_enable_aliasdomain_switch').addEventListener('change', function() {
    $('#addondomain_deps').toggleClass('hidden');

    let switchValue = this.checked ? 1 : 0;
    document.getElementById('fq_saas_plesk_enable_aliasdomain').value = switchValue;
});
</script>