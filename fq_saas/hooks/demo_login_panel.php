<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('fq_saas_demo_login_panel_start_logo_buffer')) {
    /**
     * Ensure demo login page uses tenant-specific logo file.
     * This stays fully module-scoped (no core edit) by rewriting HTML output at render time.
     */
    function fq_saas_demo_login_panel_start_logo_buffer(): void
    {
        // Keep demo branding editable from settings per instance.
        // Do not force global login logo via output buffering.
        return;

        static $started = false;
        if ($started) {
            return;
        }

        $requestUri = strtolower((string)($_SERVER['REQUEST_URI'] ?? ''));
        $is_admin_login = strpos($requestUri, '/admin/authentication') !== false;
        $is_client_login = strpos($requestUri, '/authentication/login') !== false;
        if (!$is_admin_login && !$is_client_login) {
            return;
        }

        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        if (!preg_match('/^([a-z0-9-]+)\.flowquest\.pl$/', $host, $m)) {
            return;
        }

        $slug = $m[1];
        if (!in_array($slug, fq_saas_demo_login_panel_allowed_slugs(), true)) {
            return;
        }

        $logoFile = 'flowquest_logo_global.png';
        if (!file_exists(FCPATH . 'uploads/company/' . $logoFile)) {
            return;
        }

        $logoUrl = base_url('uploads/company/' . $logoFile) . '?v=' . time();
        $started = true;

        ob_start(function ($buffer) use ($logoUrl) {
            if (!is_string($buffer) || $buffer === '') {
                return $buffer;
            }

            // Replace only the login company logo image source.
            $pattern = '~(<div class="company-logo[^>]*>.*?<img[^>]*src=")([^"]+)(")~is';
            $replaced = preg_replace($pattern, '$1' . $logoUrl . '$3', $buffer, 1);
            return $replaced ?? $buffer;
        });
    }
}

fq_saas_demo_login_panel_start_logo_buffer();

function fq_saas_demo_login_panel_allowed_slugs(): array
{
    return [
        'demo',
        'hotel',
        'logistyka',
        'warsztat',
        'nieruchomosc',
        'nieruchomosci',
        'kursy',
        'ecommerce',
        'serwiswww',
        'oze',
        'agencja',
        'rekrutacja',
        'medycyna',
        'eventy',
        'gastronomia',
        'beauty',
    ];
}

function fq_saas_demo_login_panel_slug_to_label(string $slug): string
{
    $labels = [
        'demo' => 'Core',
        'hotel' => 'Hotel',
        'logistyka' => 'Logistyka',
        'warsztat' => 'Warsztat',
        'nieruchomosc' => 'Nieruchomości',
        'nieruchomosci' => 'Nieruchomości',
        'kursy' => 'Kursy',
        'ecommerce' => 'E-commerce',
        'serwiswww' => 'Serwis WWW',
        'oze' => 'OZE',
        'agencja' => 'Agencja',
        'rekrutacja' => 'Rekrutacja',
        'medycyna' => 'Medycyna',
        'eventy' => 'Eventy',
        'gastronomia' => 'Gastronomia',
        'beauty' => 'Beauty',
    ];

    return $labels[$slug] ?? ucfirst($slug);
}

function fq_saas_demo_account_token(string $slug): string
{
    $label = fq_saas_demo_login_panel_slug_to_label($slug);
    $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $label);
    if (is_string($transliterated) && $transliterated !== '') {
        $label = $transliterated;
    }

    $label = preg_replace('/[^a-z0-9]+/i', ' ', (string) $label);
    $parts = preg_split('/\s+/', trim((string) $label)) ?: [];
    $token = '';

    foreach ($parts as $part) {
        $token .= ucfirst(strtolower($part));
    }

    return $token !== '' ? $token : 'Demo';
}

function fq_saas_demo_accounts(string $slug): array
{
    $slug = strtolower(trim($slug));
    $slug_token = preg_replace('/[^a-z0-9]+/', '', $slug);
    $email_domain = $slug === 'demo' ? 'demo.pl' : $slug_token . '.demo.pl';
    $token = fq_saas_demo_account_token($slug);

    return [
        'admin' => [
            'email' => 'administrator@' . $email_domain,
            'password' => 'FQ!' . $token . 'Admin2026',
        ],
        'owner' => [
            'email' => 'wlasciciel@' . $email_domain,
            'password' => 'FQ!' . $token . 'Owner2026',
        ],
        'employee' => [
            'email' => 'pracownik@' . $email_domain,
            'password' => 'FQ!' . $token . 'Staff2026',
        ],
        'client' => [
            'email' => 'klient@' . $email_domain,
            'password' => 'FQ!' . $token . 'Client2026',
        ],
    ];
}

function fq_saas_demo_owner_permissions(): array
{
    return [
        ['bulk_pdf_exporter', 'view'],
        ['contracts', 'view'],
        ['contracts', 'view_own'],
        ['contracts', 'create'],
        ['contracts', 'edit'],
        ['contracts', 'delete'],
        ['contracts', 'view_all_templates'],
        ['custom_fields', 'view'],
        ['custom_fields', 'create'],
        ['custom_fields', 'edit'],
        ['custom_fields', 'delete'],
        ['credit_notes', 'view'],
        ['credit_notes', 'view_own'],
        ['credit_notes', 'create'],
        ['credit_notes', 'edit'],
        ['credit_notes', 'delete'],
        ['customers', 'view'],
        ['customers', 'create'],
        ['customers', 'edit'],
        ['customers', 'delete'],
        ['email_templates', 'view'],
        ['email_templates', 'edit'],
        ['estimate_request', 'view'],
        ['estimate_request', 'view_own'],
        ['estimate_request', 'create'],
        ['estimate_request', 'edit'],
        ['estimate_request', 'delete'],
        ['estimates', 'view'],
        ['estimates', 'view_own'],
        ['estimates', 'create'],
        ['estimates', 'edit'],
        ['estimates', 'delete'],
        ['expenses', 'view'],
        ['expenses', 'view_own'],
        ['expenses', 'create'],
        ['expenses', 'edit'],
        ['expenses', 'delete'],
        ['invoices', 'view'],
        ['invoices', 'view_own'],
        ['invoices', 'create'],
        ['invoices', 'edit'],
        ['invoices', 'delete'],
        ['items', 'view'],
        ['items', 'create'],
        ['items', 'edit'],
        ['items', 'delete'],
        ['knowledge_base', 'view'],
        ['knowledge_base', 'create'],
        ['knowledge_base', 'edit'],
        ['knowledge_base', 'delete'],
        ['leads', 'view'],
        ['leads', 'delete'],
        ['payments', 'view'],
        ['projects', 'view'],
        ['projects', 'create'],
        ['projects', 'edit'],
        ['projects', 'delete'],
        ['projects', 'create_milestones'],
        ['projects', 'edit_milestones'],
        ['projects', 'delete_milestones'],
        ['proposals', 'view'],
        ['proposals', 'view_own'],
        ['proposals', 'create'],
        ['proposals', 'edit'],
        ['proposals', 'delete'],
        ['proposals', 'view_all_templates'],
        ['reports', 'view'],
        ['reports', 'view-timesheets'],
        ['settings', 'view'],
        ['settings', 'edit'],
        ['staff', 'view'],
        ['staff', 'create'],
        ['staff', 'edit'],
        ['subscriptions', 'view'],
        ['subscriptions', 'view_own'],
        ['subscriptions', 'create'],
        ['subscriptions', 'edit'],
        ['subscriptions', 'delete'],
        ['tasks', 'view'],
        ['tasks', 'view_own'],
        ['tasks', 'create'],
        ['tasks', 'edit'],
        ['tasks', 'delete'],
        ['tasks', 'edit_timesheet'],
        ['tasks', 'edit_own_timesheet'],
        ['tasks', 'delete_timesheet'],
        ['tasks', 'delete_own_timesheet'],
        ['tickets', 'view'],
        ['tickets', 'view_own'],
        ['tickets', 'create'],
        ['tickets', 'edit'],
        ['tickets', 'delete'],
        ['checklist_templates', 'create'],
        ['checklist_templates', 'delete'],
    ];
}

function fq_saas_demo_employee_permissions(): array
{
    return [
        ['contracts', 'view'],
        ['customers', 'view'],
        ['estimates', 'view'],
        ['expenses', 'view'],
        ['expenses', 'view_own'],
        ['invoices', 'view'],
        ['knowledge_base', 'view'],
        ['leads', 'view'],
        ['projects', 'view'],
        ['proposals', 'view'],
        ['tasks', 'view'],
        ['tasks', 'view_own'],
        ['tasks', 'create'],
        ['tasks', 'edit'],
        ['tasks', 'edit_own_timesheet'],
    ];
}

function fq_saas_demo_client_permission_ids(): array
{
    return [1, 2, 3, 4, 5];
}

function fq_saas_demo_login_panel_host_slug(): string
{
    $slug_from_host = static function (string $host): string {
        $host = strtolower(trim($host));
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        if (!preg_match('/^([a-z0-9-]+)\.flowquest\.pl$/', $host, $m)) {
            return '';
        }

        $slug = $m[1];
        if (in_array($slug, ['crm', 'go', 'www'], true)) {
            return '';
        }

        return in_array($slug, fq_saas_demo_login_panel_allowed_slugs(), true) ? $slug : '';
    };

    if (function_exists('site_url')) {
        $site_host = (string) parse_url(site_url('/'), PHP_URL_HOST);
        $site_slug = $slug_from_host($site_host);
        if ($site_slug !== '') {
            return $site_slug;
        }
    }

    if (empty($_SERVER['HTTP_HOST'])) {
        return '';
    }

    $host = strtolower(trim((string) $_SERVER['HTTP_HOST']));
    $host_slug = $slug_from_host($host);
    if ($host_slug !== '') {
        return $host_slug;
    }

    $default_host = function_exists('fq_saas_get_saas_default_host')
        ? strtolower(trim((string) fq_saas_get_saas_default_host()))
        : '';
    if ($default_host && $host === $default_host) {
        return '';
    }

    $slug = '';
    if ($default_host && str_ends_with($host, '.' . $default_host)) {
        $slug = substr($host, 0, -1 * (strlen($default_host) + 1));
    }

    if ($slug === '' || in_array($slug, ['crm', 'go', 'www'], true)) {
        return '';
    }

    if (!in_array($slug, fq_saas_demo_login_panel_allowed_slugs(), true)) {
        return '';
    }

    return $slug;
}

function fq_saas_demo_login_panel_tenant()
{
    if (function_exists('fq_saas_tenant')) {
        $candidate = fq_saas_tenant();
        $candidate_slug = strtolower(trim((string) ($candidate->slug ?? '')));
        if ($candidate && $candidate_slug !== '') {
            if (!in_array($candidate_slug, fq_saas_demo_login_panel_allowed_slugs(), true)) {
                return null;
            }
            if ((int) ($candidate->clientid ?? 0) !== 3) {
                return null;
            }
            if (!isset($candidate->metadata) || !is_object($candidate->metadata)) {
                $candidate->metadata = !empty($candidate->metadata) ? json_decode((string) $candidate->metadata) : (object) [];
            }
            if (!is_object($candidate->metadata)) {
                $candidate->metadata = (object) [];
            }

            return $candidate;
        }
    }

    $slug = fq_saas_demo_login_panel_host_slug();
    if ($slug === '') {
        return null;
    }

    $tenant = null;
    if (function_exists('fq_saas_tenant')) {
        $candidate = fq_saas_tenant();
        if ($candidate && (string) ($candidate->slug ?? '') === $slug) {
            $tenant = $candidate;
        }
    }

    if (!$tenant) {
        $tenant = (object) [
            'slug' => $slug,
            'clientid' => 3,
            'metadata' => (object) [],
        ];
    } elseif ((int) ($tenant->clientid ?? 0) !== 3) {
        return null;
    }

    if (!isset($tenant->metadata) || !is_object($tenant->metadata)) {
        $tenant->metadata = !empty($tenant->metadata) ? json_decode((string) $tenant->metadata) : (object) [];
    }

    if (!is_object($tenant->metadata)) {
        $tenant->metadata = (object) [];
    }

    return $tenant;
}

function fq_saas_demo_login_panel_default_roles(string $slug = 'demo'): array
{
    $accounts = fq_saas_demo_accounts($slug);

    return [
        'admin' => [
            'target' => 'admin',
            'label' => 'Administrator',
            'email' => $accounts['admin']['email'],
            'password' => $accounts['admin']['password'],
            'submit' => 'Zaloguj jako administrator',
            'hint' => 'Pełny dostęp techniczny',
            'copy' => 'Administrator widzi pełną konfigurację systemu i wszystkie obszary instancji demo.',
            'panel' => 'Panel administracyjny',
        ],
        'owner' => [
            'target' => 'admin',
            'label' => 'Właściciel',
            'email' => $accounts['owner']['email'],
            'password' => $accounts['owner']['password'],
            'submit' => 'Zaloguj jako właściciel',
            'hint' => 'Szeroki dostęp biznesowy',
            'copy' => 'Właściciel widzi klientów, leady, finanse, projekty i ustawienia operacyjne firmy.',
            'panel' => 'Panel administracyjny',
        ],
        'employee' => [
            'target' => 'admin',
            'label' => 'Pracownik',
            'email' => $accounts['employee']['email'],
            'password' => $accounts['employee']['password'],
            'submit' => 'Zaloguj jako pracownik',
            'hint' => 'Dzienna obsługa zespołu',
            'copy' => 'Pracownik pracuje na zadaniach, kalendarzu, klientach i komunikacji operacyjnej.',
            'panel' => 'Panel administracyjny',
        ],
        'client' => [
            'target' => 'client',
            'label' => 'Klient',
            'email' => $accounts['client']['email'],
            'password' => $accounts['client']['password'],
            'submit' => 'Zaloguj jako klient',
            'hint' => 'Portal klienta',
            'copy' => 'Klient widzi swoje dokumenty, terminy, statusy i wiadomości z firmy.',
            'panel' => 'Portal klienta',
        ],
    ];
}

function fq_saas_demo_login_panel_build_config($tenant): array
{
    $slug = (string) ($tenant->slug ?? 'demo');
    $hostSlug = fq_saas_demo_login_panel_host_slug();
    if ($hostSlug !== '') {
        $slug = $hostSlug;
    }

    $loginPanel = $tenant->metadata->login_panel ?? null;
    $panelTitle = $loginPanel->title ?? ('Zobacz, jak pracuje ' . fq_saas_demo_login_panel_slug_to_label($slug));
    $panelCopy = $loginPanel->copy ?? 'Wybierz zakładkę, a formularz poniżej uzupełni się bez przeładowania strony.';
    $panelKicker = $loginPanel->kicker ?? 'FlowQuest Demo';
    $panelAccounts = is_object($loginPanel) && !empty($loginPanel->accounts) ? (array) $loginPanel->accounts : [];

    $adminAction = admin_url('authentication');
    $clientAction = site_url('authentication/login');
    $roles = [];

    foreach (fq_saas_demo_login_panel_default_roles($slug) as $key => $defaults) {
        $account = (array) ($panelAccounts[$key] ?? []);
        $merged = array_merge($defaults, array_intersect_key($account, array_flip(['hint', 'copy'])));
        $merged['email'] = trim((string) $defaults['email']);
        $merged['password'] = (string) $defaults['password'];
        $merged['submit'] = (string) $defaults['submit'];
        $merged['panel'] = (string) $defaults['panel'];
        $merged['hint'] = (string) ($merged['hint'] ?? $defaults['hint']);
        $merged['copy'] = (string) ($merged['copy'] ?? $defaults['copy']);
        $merged['action'] = (($merged['target'] ?? 'admin') === 'client') ? $clientAction : $adminAction;
        $roles[$key] = $merged;
    }

    return [
        'title' => $panelTitle,
        'copy' => $panelCopy,
        'kicker' => $panelKicker,
        'roles' => $roles,
        'adminAction' => $adminAction,
        'clientAction' => $clientAction,
    ];
}

function fq_saas_demo_login_panel_default_role(): string
{
    $requestUri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    if (strpos($requestUri, '/admin/') !== false) {
        return 'owner';
    }

    return 'client';
}

function fq_saas_demo_login_panel_css(): string
{
    return <<<'CSS'
.fq-demo-login {
    position: relative;
    margin: 0 0 18px;
}

.fq-demo-login__shell {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.10);
    border-radius: 24px;
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.13), transparent 34%),
        radial-gradient(circle at left bottom, rgba(16, 185, 129, 0.12), transparent 30%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
    box-shadow: 0 22px 48px rgba(15, 23, 42, 0.10);
    padding: 22px;
}

.fq-demo-login__shell::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.06), transparent 40%, rgba(16, 185, 129, 0.05));
    pointer-events: none;
}

.fq-demo-login__kicker-row {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-bottom: 12px;
}

.fq-demo-login__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.10);
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
}

.fq-demo-login__slug {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.05);
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

.fq-demo-login__title {
    position: relative;
    margin: 0 0 10px;
    color: #0f172a;
    font-size: 28px;
    line-height: 1.08;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.fq-demo-login__copy {
    position: relative;
    margin: 0 0 18px;
    color: #475569;
    font-size: 14px;
    line-height: 1.7;
    max-width: 44rem;
}

.fq-demo-login__tabs {
    position: relative;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.fq-demo-login__tab {
    appearance: none;
    border: 1px solid rgba(15, 23, 42, 0.12);
    border-radius: 18px;
    padding: 14px 14px 13px;
    background: rgba(255, 255, 255, 0.84);
    color: #334155;
    text-align: left;
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
}

.fq-demo-login__tab:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    border-color: rgba(37, 99, 235, 0.28);
}

.fq-demo-login__tab.is-active {
    background: linear-gradient(135deg, #1d4ed8, #2563eb 46%, #0f172a 130%);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.28);
}

.fq-demo-login__tab-label {
    display: block;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 4px;
}

.fq-demo-login__tab-hint {
    display: block;
    font-size: 12px;
    line-height: 1.35;
    opacity: .82;
}

.fq-demo-login__details {
    position: relative;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.fq-demo-login__detail {
    padding: 14px 15px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.76);
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.fq-demo-login__detail span {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.fq-demo-login__detail strong {
    display: block;
    color: #0f172a;
    font-size: 14px;
    line-height: 1.35;
    word-break: break-word;
}

.fq-demo-login__note {
    position: relative;
    margin: 14px 0 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.65;
}

@media (max-width: 767px) {
    .fq-demo-login__shell {
        padding: 18px;
        border-radius: 20px;
    }

    .fq-demo-login__title {
        font-size: 24px;
    }

    .fq-demo-login__tabs,
    .fq-demo-login__details {
        grid-template-columns: 1fr;
    }
}
CSS;
}

function fq_saas_demo_login_panel_render_config_script(array $config): void
{
    $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo '<script type="application/json" data-fq-demo-login-config>' . $json . '</script>';
}

function fq_saas_render_demo_login_panel(): void
{
    if (!empty($GLOBALS['fq_demo_login_panel_rendered'])) {
        return;
    }

    $tenant = fq_saas_demo_login_panel_tenant();
    if (!$tenant || !isset($tenant->metadata)) {
        return;
    }

    $config = fq_saas_demo_login_panel_build_config($tenant);
    if (empty($config['roles'])) {
        return;
    }

    static $assets_printed = false;
    if (!$assets_printed) {
        $assets_printed = true;
        echo '<style>' . fq_saas_demo_login_panel_css() . '</style>';
    }

    $slugLabel = fq_saas_demo_login_panel_slug_to_label((string) ($tenant->slug ?? 'demo'));
    $slugKey = strtolower((string) ($tenant->slug ?? 'demo'));
    $defaultRole = fq_saas_demo_login_panel_default_role();

    echo '<div class="fq-demo-login" data-fq-demo-login-panel data-demo-default-role="' . e($defaultRole) . '">';
    fq_saas_demo_login_panel_render_config_script($config);
    echo '<div class="fq-demo-login__shell">';
    echo '<div class="fq-demo-login__kicker-row">';
    echo '<span class="fq-demo-login__badge">' . e($config['kicker']) . '</span>';
    echo '<span class="fq-demo-login__slug">' . e($slugLabel) . ' demo</span>';
    echo '</div>';
    echo '<h2 class="fq-demo-login__title">' . e($config['title']) . '</h2>';
    echo '<p class="fq-demo-login__copy">' . e($config['copy']) . '</p>';
    echo '<div class="fq-demo-login__tabs" role="tablist" aria-label="Wybór poziomu logowania">';

    foreach ($config['roles'] as $roleKey => $role) {
        echo '<button type="button" class="fq-demo-login__tab" data-demo-role="' . e($roleKey) . '" aria-pressed="false">';
        echo '<span class="fq-demo-login__tab-label">' . e($role['label']) . '</span>';
        echo '<span class="fq-demo-login__tab-hint">' . e($role['hint']) . '</span>';
        echo '</button>';
    }

    echo '</div>';
    echo '<div class="fq-demo-login__details">';
    $firstRole = reset($config['roles']);
    $firstPanel = is_array($firstRole) ? (string) ($firstRole['panel'] ?? 'Panel') : 'Panel';
    $firstEmail = is_array($firstRole) ? (string) ($firstRole['email'] ?? 'admin@demo.pl') : 'admin@demo.pl';
    $firstPassword = is_array($firstRole) ? (string) ($firstRole['password'] ?? 'FlowQuest123!') : 'FlowQuest123!';
    echo '<div class="fq-demo-login__detail"><span>Panel</span><strong data-demo-panel>' . e($firstPanel) . '</strong></div>';
    echo '<div class="fq-demo-login__detail"><span>Email</span><strong data-demo-email>' . e($firstEmail) . '</strong></div>';
    echo '<div class="fq-demo-login__detail"><span>Hasło</span><strong data-demo-password>' . e($firstPassword) . '</strong></div>';
    echo '</div>';
    echo '<p class="fq-demo-login__note" data-demo-note>Wybór zakładki uzupełnia formularz poniżej i przełącza go na właściwy panel bez przeładowania strony.</p>';
    echo '</div>';
    echo '</div>';

    echo '<script>(function(){';
    echo 'const root=document.querySelector("[data-fq-demo-login-panel]");';
    echo 'if(!root){return;}';
    echo 'const tenantSlug=' . json_encode($slugKey, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';';
    echo 'const config=JSON.parse(root.querySelector("[data-fq-demo-login-config]").textContent);';
    echo 'const form=root.closest("form")||document.querySelector("body.login_admin form")||document.querySelector("form.login-form")||root.parentElement?.querySelector("form")||document.querySelector("form");';
    echo 'if(!form){return;}';
    echo 'const emailInput=form.querySelector("#email");';
    echo 'const passwordInput=form.querySelector("#password");';
    echo 'const realEmailInput=form.querySelector("[data-fq-demo-login-real-email]")||form.querySelector(\'input[name="email"]:not(#email)\');';
    echo 'const realPasswordInput=form.querySelector("[data-fq-demo-login-real-password]")||form.querySelector(\'input[name="password"]:not(#password)\');';
    echo 'const submitButton=form.querySelector(\'button[type="submit"]\');';
    echo 'const panelEl=root.querySelector("[data-demo-panel]");';
    echo 'const emailEl=root.querySelector("[data-demo-email]");';
    echo 'const passwordEl=root.querySelector("[data-demo-password]");';
    echo 'const noteEl=root.querySelector("[data-demo-note]");';
    echo 'const tabs=Array.from(root.querySelectorAll("[data-demo-role]"));';
    echo 'const roleMap=config.roles||{};';
    echo 'const routeLabels={admin:"panel administracyjny",client:"portal klienta"};';
    echo 'const pageDefault=root.getAttribute("data-demo-default-role")||"client";';
    echo 'const queryRole=new URLSearchParams(window.location.search).get("demo_account");';
    echo 'const initialRole=roleMap[queryRole]?queryRole:(roleMap[pageDefault]?pageDefault:"client");';
    echo 'const syncPostedFields=()=>{';
    echo '  if(realEmailInput&&emailInput){realEmailInput.value=emailInput.value||"";}';
    echo '  if(realPasswordInput&&passwordInput){realPasswordInput.value=passwordInput.value||"";}';
    echo '};';
    echo 'const setButtonState=(role)=>{';
    echo '  const label=role.submit||"Zaloguj";';
    echo '  if(submitButton){submitButton.textContent=label;}';
    echo '  const action=role.action||config.clientAction||form.action;';
    echo '  form.action=action;';
    echo '  if(emailInput){emailInput.value=role.email||""; emailInput.dispatchEvent(new Event("input",{bubbles:true})); emailInput.dispatchEvent(new Event("change",{bubbles:true}));}';
    echo '  if(passwordInput){passwordInput.value=role.password||""; passwordInput.dispatchEvent(new Event("input",{bubbles:true})); passwordInput.dispatchEvent(new Event("change",{bubbles:true}));}';
    echo '  syncPostedFields();';
    echo '  if(panelEl){panelEl.textContent=role.panel||"";}';
    echo '  if(emailEl){emailEl.textContent=role.email||"";}';
    echo '  if(passwordEl){passwordEl.textContent=role.password||"";}';
    echo '  if(noteEl){noteEl.textContent=(role.copy||"")+" Formularz poniżej został przełączony na "+(routeLabels[role.target]||"właściwy panel")+"."; }';
    echo '  tabs.forEach((tab)=>{';
    echo '    const isActive=tab.getAttribute("data-demo-role")===(role.key||"");';
    echo '    tab.classList.toggle("is-active", isActive);';
    echo '    tab.setAttribute("aria-pressed", isActive ? "true" : "false");';
    echo '  });';
    echo '};';
    echo 'const selectRole=(key)=>{';
    echo '  const role=roleMap[key]||roleMap[initialRole]||Object.values(roleMap)[0];';
    echo '  if(!role){return;}';
    echo '  role.key=key;';
    echo '  setButtonState(role);';
    echo '};';
    echo 'tabs.forEach((tab)=>{';
    echo '  tab.addEventListener("click",()=>selectRole(tab.getAttribute("data-demo-role")));';
    echo '});';
    echo 'if(emailInput){emailInput.addEventListener("input",syncPostedFields); emailInput.addEventListener("change",syncPostedFields);}';
    echo 'if(passwordInput){passwordInput.addEventListener("input",syncPostedFields); passwordInput.addEventListener("change",syncPostedFields);}';
    echo 'selectRole(initialRole);';
    echo '})();</script>';

    $GLOBALS['fq_demo_login_panel_rendered'] = true;
}

/**
 * Hook dodający dane demo do instancji przy pierwszym logowaniu
 * Działa tylko dla instancji demo (clientid = 3)
 */
function fq_saas_demo_add_data_on_login() {
    // Sprawdź czy jesteśmy w instancji demo
    if (!function_exists('fq_saas_tenant')) {
        return;
    }
    
    $tenant = fq_saas_tenant();
    if (!$tenant || (int)($tenant->clientid ?? 0) !== 3) {
        return;
    }
    
    $slug = strtolower(trim($tenant->slug ?? ''));
    if (!$slug) {
        return;
    }
    
    // Sprawdź czy dane demo już zostały dodane (sprawdzamy w sesji)
    $CI = &get_instance();
    $demo_data_key = 'fq_demo_data_added_' . $slug;
    
    if ($CI->session->userdata($demo_data_key)) {
        return; // Dane już dodane w tej sesji
    }
    
    // Mapowanie slugów do plików SQL
    $demo_files = [
        'beauty' => 'beauty_demo.sql',
        'logistyka' => 'logistyka_demo.sql', 
        'oze' => 'oze_demo.sql',
    ];
    
    if (!isset($demo_files[$slug])) {
        return; // Brak danych demo dla tej branży
    }
    
    $demo_file = module_dir_path(FQ_SAAS_MODULE_NAME, 'migrations/default_seeds/' . $demo_files[$slug]);
    
    if (!file_exists($demo_file)) {
        log_message('error', "Plik demo nie istnieje: $demo_file");
        return;
    }
    
    // Załaduj parser SQL
    $CI->load->library(FQ_SAAS_MODULE_NAME . '/SqlScriptParser');
    
    try {
        $sqlStatements = $CI->sqlscriptparser->parse($demo_file);
        $success_count = 0;
        
        foreach ($sqlStatements as $statement) {
            $distilled = $CI->sqlscriptparser->removeComments($statement);
            
            if (!empty($distilled) && str_starts_with($distilled, 'INSERT INTO')) {
                try {
                    $CI->db->query($distilled);
                    $success_count++;
                } catch (Exception $e) {
                    // Ignoruj błędy duplikatów (dane już istnieją)
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        log_message('error', "Błąd SQL dla $slug: " . $e->getMessage());
                    }
                }
            }
        }
        
        if ($success_count > 0) {
            log_message('info', "Dodano $success_count rekordów demo dla $slug");
            $CI->session->set_userdata($demo_data_key, true);
        }
        
    } catch (Exception $e) {
        log_message('error', "Błąd parsowania SQL dla $slug: " . $e->getMessage());
    }
}

// Zarejestruj hook - działa po zalogowaniu
hooks()->add_action('admin_auth_init', 'fq_saas_demo_add_data_on_login', 90);
hooks()->add_action('clients_authentication_constructor', 'fq_saas_demo_add_data_on_login', 90);
