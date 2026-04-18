# FQ SAAS (Perfex module)

**Display name:** FQ SAAS · **Slug:** `fq_saas` · **Marketing brand:** [FlowQuest](https://flowquest.app) (Modular Business OS).

This folder is the unified SaaS engine: tenants, packages, Stripe subscriptions, extension tables (`tblfq_saas_*`), CMS, landing builder, coupons, affiliates, and API hooks.

## Install

Copy the `fq_saas` directory into `modules/fq_saas/`, activate the module in Perfex, then run database upgrades. Migration **035** creates extension tables, copies legacy `perfex_saas_%` options to `fq_saas_%` where missing, and aligns schema.

## Configuration

- **Feature limits:** `config/feature_limits.php` — optional per-plan caps merged with package quotas (see `fq_saas_feature_limits_plan_key` filter).
- **Built-in landing:** set option `fq_saas_landing_builtin_slug` to a **published** `landing_pages.slug` to serve it at `/` when no external `fq_saas_landing_page_url` is set.
- **Checkout coupon:** clients may use `?coupon=CODE` on subscribe; map to Stripe via `stripe_coupon_id` on the coupon row.
- **Referrals:** `?ref=AFFILIATECODE` on subscribe stores referrer; commissions accrue on recorded invoice payments (see `fq_saas_affiliate_helper.php`).

## Integrations

Place cPanel/Plesk or DNS automation under `libraries/integrations/` and implement the `fq_saas_domain_dns_probe` filter (see Domains admin screen).

## API

OpenAPI includes `GET /saas/api/cms_pages` (published pages only). Grant the `cms_pages` endpoint on API users in **FQ SAAS → API users**.

## Changelog

See `CHANGELOG.md`.

## Automation (Stripe → tenant)

Align custom workflows with your FlowQuest ROOT doc (§19–23): use `fq_saas_subscription_activated`, `fq_saas_invoice_payment_recorded`, `fq_saas_tenant_deployed`, and `fq_saas_log()` for a single audit trail on the master instance.
