# Changelog — FQ SAAS

## 0.4.2

- **Routing:** Admin SaaS routes (`admin/{fq_saas}/…`, legacy `perfex_saas`, API) use **`ADMIN_URI`** (same as `admin_url()` / `CUSTOM_ADMIN_URL`) instead of a hardcoded `admin/` segment — fixes 404 on renamed admin paths.
- **Routing:** Those admin routes are registered **for master and tenant** (moved out of `if (!fq_saas_is_tenant())`), so `…/affiliates`, `…/coupons`, etc. resolve whenever the module is active. Tenant-only landing + client routes stay master-only.
- **Tenant:** `unset` of default `modules` menu routes tries both `admin/` and `ADMIN_URI` so overrides work when the admin slug is customised.

## 0.4.1

- **Admin menu:** `staff_can` checks now use registered feature ids `fq_saas_companies` / `fq_saas_packages` (was wrong `fq_saas_company` / `fq_saas_package`), so Packages, Tenants, Coupons, Affiliates, etc. show when the role grants them — not only Landing.
- **Labels:** Explicit `lang->load` before building the sidebar; `admin_init` priorities register staff capabilities before menu (5 vs 50). Clearer default strings: sidebar **FQ SaaS**, English **Discount coupons**.

## 0.4.0

- Rebranded module identity to **FQ SAAS** (`fq_saas`, `FQ_SAAS_*`, `Fq_saas_*`) with legacy DB prefix compatibility for core `tblperfex_saas_*` tables.
- Added **Fq_saas_kernel** orchestration and **fq_saas_log** / `tblfq_saas_activity_log` audit trail.
- Migration **035**: extension tables (`activity_log`, `landing_pages`, `coupons`, `affiliates`, `cms_pages`), option migration `perfex_saas_%` → `fq_saas_%`, coupon Stripe ID + affiliate payout status columns.
- **Billing hooks:** `before_invoice_added`, `after_payment_added`, `invoice_status_changed`, `invoice_overdue_reminder_sent` → kernel + activity log; Stripe subscription params filter for coupons.
- **Admin:** Landing builder, CMS, Coupons, Affiliates, Custom domain workflow, packages **Feature limits** view, dashboard MRR/subscription widget.
- **API:** `cms_pages` read-only endpoint for published CMS content.
- **Affiliate:** `?ref=` capture, client metadata binding, commission on SaaS invoice payments; merge fields `{affiliate_code}`, `{affiliate_balance}`.
