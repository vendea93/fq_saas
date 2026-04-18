<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Demo instance reset notification banner (introduced in 0.3.8).
 *
 * Expected data passed in:
 *   - $info : array from fq_saas_tenant_demo_reset_info()
 *
 * This view is intentionally minimal and self-contained so it can be rendered
 * either on the tenant admin dashboard (via before_start_render_dashboard_content)
 * or embedded elsewhere by integrators via the `fq_saas_demo_reset_notice`
 * filter hook.
 */
if (empty($info) || empty($info['is_demo'])) return;

$seconds_until = (int) ($info['seconds_until_reset'] ?? 0);
$next_reset    = (int) ($info['next_reset_time'] ?? 0);

if ($seconds_until <= 0) {
    $human_when = _l('fq_saas_demo_reset_imminent');
} else {
    $hours = floor($seconds_until / 3600);
    $minutes = floor(($seconds_until % 3600) / 60);

    if ($hours > 0) {
        $human_when = sprintf(_l('fq_saas_demo_reset_in_hm'), $hours, $minutes);
    } else {
        $human_when = sprintf(_l('fq_saas_demo_reset_in_minutes'), max(1, $minutes));
    }
}

$iso_next_reset = $next_reset ? date('c', $next_reset) : '';
?>
<div class="alert alert-warning tw-mb-4" data-perfex-saas-demo-reset-notice="1" role="alert">
    <strong><?= _l('fq_saas_demo_reset_title'); ?>:</strong>
    <span><?= _l('fq_saas_demo_reset_body'); ?></span>
    <span class="tw-font-semibold" <?= $iso_next_reset ? 'title="' . htmlspecialchars($iso_next_reset, ENT_QUOTES) . '"' : ''; ?>>
        <?= htmlspecialchars($human_when, ENT_QUOTES); ?>
    </span>
</div>
