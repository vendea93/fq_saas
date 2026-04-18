<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
    "use strict";

    const SAAS_MODULE_NAME = '<?= FQ_SAAS_MODULE_WHITELABEL_NAME ?>';
    const SAAS_FILTER_TAG = '<?= FQ_SAAS_FILTER_TAG; ?>';
    const SAAS_IS_TENANT = <?= fq_saas_is_tenant() ? 'true' : 'false'; ?>;
    const SAAS_ENFORCED_SHARED_FIELDS = <?= json_encode(FQ_SAAS_ENFORCED_SHARED_FIELDS); ?>;
    const SAAS_IFRAME_MODE = window.self !== window.top;
</script>

<!-- Module custom admin script -->
<script src="<?= fq_saas_asset_url('js/admin.js') ?>">
</script>