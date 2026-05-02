<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (isset($multiple)) echo '<input name="' . $name . '" value="" type="hidden" />';
if (!is_array($value)) $value = [$value];
$tenants = fq_saas_filter_visible_instances(get_instance()->fq_saas_model->companies('', true));
?>
<select name="<?= $name; ?>" class="form-control selectpicker <?= $class ?? ''; ?>" <?= isset($id) ? "id='$id'" : ""; ?>
    <?= isset($multiple) ? "multiple='$multiple'" : ""; ?>>
    <option value=""></option>
    <?php
    foreach ($tenants as $tenant) {
        $selected = in_array($tenant->slug, $value) ? 'selected' : '';
        echo '<option value="' . $tenant->slug . '" ' . $selected . '>' . $tenant->name . ' (' . $tenant->slug . ')</option>';
    } ?>
</select>
