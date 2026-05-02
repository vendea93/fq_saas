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
$reset_interval = (int) (($info['reset_hour_interval'] ?? 0) * 3600);

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
<div class="alert alert-warning tw-mb-4" data-perfex-saas-demo-reset-notice="1" data-demo-next-reset="<?= (int) $next_reset; ?>" data-demo-reset-interval="<?= (int) $reset_interval; ?>" role="alert">
    <strong><?= _l('fq_saas_demo_reset_title'); ?>:</strong>
    <span><?= _l('fq_saas_demo_reset_body'); ?></span>
    <span class="tw-font-semibold" data-demo-countdown <?= $iso_next_reset ? 'title="' . htmlspecialchars($iso_next_reset, ENT_QUOTES) . '"' : ''; ?>>
        <?= htmlspecialchars($human_when, ENT_QUOTES); ?>
    </span>
</div>
<script>
(function () {
    const notice = document.querySelector('[data-perfex-saas-demo-reset-notice="1"]');
    const countdown = notice ? notice.querySelector('[data-demo-countdown]') : null;
    if (!notice || !countdown) {
        return;
    }

    const updateCountdown = () => {
        const nextReset = parseInt(notice.getAttribute('data-demo-next-reset') || '0', 10);
        const resetInterval = parseInt(notice.getAttribute('data-demo-reset-interval') || '0', 10);
        if (!nextReset || !resetInterval) {
            return;
        }

        let remaining = Math.max(0, nextReset - Math.floor(Date.now() / 1000));
        if (remaining <= 0) {
            countdown.textContent = <?= json_encode(_l('fq_saas_demo_reset_imminent')); ?>;
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);

        if (hours > 0) {
            countdown.textContent = <?= json_encode(_l('fq_saas_demo_reset_in_hm')); ?>.replace('%d', hours).replace('%d', minutes);
        } else {
            countdown.textContent = <?= json_encode(_l('fq_saas_demo_reset_in_minutes')); ?>.replace('%d', Math.max(1, minutes));
        }
    };

    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
</script>
