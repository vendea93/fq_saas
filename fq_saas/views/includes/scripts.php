<?php defined('BASEPATH') or exit('No direct script access allowed');

$saas_enforced_fields = FQ_SAAS_ENFORCED_SHARED_FIELDS;
$is_demo_instance = false;
if (fq_saas_is_tenant()) {
    $tenant = fq_saas_tenant();
    $tenant_slug = strtolower((string) ($tenant->slug ?? ''));
    $is_demo_instance = function_exists('fq_saas_tenant_is_demo_instance') && fq_saas_tenant_is_demo_instance();
    if (!$is_demo_instance) {
        $demo_like_slugs = [
            'demo',
            'beauty',
            'hotel',
            'warsztat',
            'nieruchomosc',
            'nieruchomosci',
            'logistyka',
            'ecommerce',
            'kursy',
            'serwiswww',
            'oze',
            'agencja',
            'rekrutacja',
            'medycyna',
            'eventy',
            'gastronomia',
        ];
        $is_demo_instance = ((int) ($tenant->clientid ?? 0) === 3) || in_array($tenant_slug, $demo_like_slugs, true);
    }
}
if ($is_demo_instance) {
    $saas_enforced_fields = array_values(array_filter($saas_enforced_fields, function ($field) {
        return !in_array($field, ['company_logo', 'company_logo_dark', 'favicon'], true);
    }));
}
?>

<script>
    "use strict";

    const SAAS_MODULE_NAME = '<?= FQ_SAAS_MODULE_WHITELABEL_NAME ?>';
    const SAAS_FILTER_TAG = '<?= FQ_SAAS_FILTER_TAG; ?>';
    const SAAS_IS_TENANT = <?= fq_saas_is_tenant() ? 'true' : 'false'; ?>;
    const SAAS_ENFORCED_SHARED_FIELDS = <?= json_encode($saas_enforced_fields); ?>;
    const SAAS_IFRAME_MODE = window.self !== window.top;
</script>

<!-- Module custom admin script -->
<script src="<?= fq_saas_asset_url('js/admin.js') ?>">
</script>
