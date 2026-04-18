# FQ SAAS integrations

Add optional integration classes here (cPanel, Plesk, DNS providers). Register behaviour using Perfex hooks or filters, for example:

- `fq_saas_domain_dns_probe` — return `['ok' => true, 'message' => '']` when DNS is valid for a tenant custom domain.

Keep vendor-specific credentials out of version control; load from Perfex options or environment.
