<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Central orchestration for FQ SAAS (billing, CMS, domains, cron).
 * Subsystems call Fq_saas_kernel::dispatch() instead of duplicating cross-cutting hooks.
 *
 * Common events: tenant_deployed, tenant_removed, tenant_created_or_updated, domain_verified,
 * invoice_payment_recorded, invoice_status_changed, invoice_overdue_reminder, tenant_grace_reminder,
 * tenant_auto_purged, subscription_activated, cms_page_saved.
 */
class Fq_saas_kernel
{
    /**
     * Emit an internal event (hooks: fq_saas_{$event}).
     *
     * @param string $event Snake_case event name without fq_saas_ prefix.
     * @param array  $payload
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        hooks()->do_action('fq_saas_' . $event, $payload);
    }

    /**
     * One-time wiring after module PHP is parsed.
     */
    public static function boot(): void
    {
        hooks()->do_action('fq_saas_kernel_booted');
    }
}
