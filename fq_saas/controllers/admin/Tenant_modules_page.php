<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tenant_modules_page extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        /**
         * Modules are only accessible by administrators
         */
        if (!fq_saas_tenant_admin_modules_page_enabled()) {
            redirect(admin_url());
        }
        if (!is_admin()) {
            redirect(admin_url());
        }
    }

    public function index()
    {
        $tenant = fq_saas_tenant();
        $all_modules = $this->fq_saas_model->modules();
        $tenant_modules = fq_saas_tenant_modules($tenant, false, false, false, false, true);
        $demo_marketplace = fq_saas_demo_tenant_admin_marketplace_enabled($tenant);

        if ($demo_marketplace) {
            foreach ($this->fq_saas_model->default_modules() as $module) {
                $system_name = $module['system_name'];
                $description = $this->default_module_description($system_name);
                $custom_name = $this->default_module_name($system_name);
                if (!isset($all_modules[$system_name])) {
                    $all_modules[$system_name] = [
                        'system_name' => $system_name,
                        'custom_name' => $custom_name,
                        'description' => $description,
                        'headers' => [
                            'description' => $description,
                            'version' => 'Core',
                        ],
                    ];
                } else {
                    $all_modules[$system_name]['custom_name'] = $custom_name;
                    if (empty($all_modules[$system_name]['description'])) {
                        $all_modules[$system_name]['description'] = $description;
                        $all_modules[$system_name]['headers']['description'] = $description;
                    }
                }
                $tenant_modules[] = $system_name;
            }

            $tenant_modules = array_values(array_unique($tenant_modules));
        }

        // Ensure the marketplace always exposes at least one clearly priced item.
        if (isset($all_modules['projects'])) {
            if (empty($all_modules['projects']['description'])) {
                $all_modules['projects']['description'] = _l('fq_saas_marketplace_projects_desc');
            }
            if ((string)($all_modules['projects']['price'] ?? '') === '') {
                $all_modules['projects']['price'] = '99.00';
            }
            if ((string)($all_modules['projects']['billing_mode'] ?? '') === '') {
                $all_modules['projects']['billing_mode'] = 'monthly';
            }
        }

        $disabled_modules = $this->disabled_modules();
        $disabled_modules = array_merge((array)(fq_saas_tenant()->metadata->disabled_modules ?? []), $disabled_modules);

        foreach ($all_modules as $key => $module) {
            $in_package = in_array($module['system_name'], $tenant_modules, true);
            $all_modules[$key]['in_package'] = $in_package;

            $disabled = in_array($module['system_name'], $disabled_modules);

            if (!isset($module['activated'])) {
                $module['activated'] = (!$in_package || $disabled) ? 0 : 1;
                $all_modules[$key]['activated'] = $module['activated'];
            }

            if ($module['activated'] === 1 && $disabled) {
                $all_modules[$key]['activated'] = 0;
            }
            if (!$in_package) {
                $all_modules[$key]['activated'] = 0;
            }
        }

        $data['modules'] = $all_modules;
        $data['title']   = _l('fq_saas_plugin_marketplace');
        $data['is_demo_marketplace'] = $demo_marketplace;
        $this->load->view('tenant_admin/modules/list', $data);
    }

    public function update($name, $action)
    {
        $tenant = fq_saas_tenant();
        $tenant_modules = fq_saas_tenant_modules($tenant, false, false, false, false, true);

        if (fq_saas_demo_tenant_admin_marketplace_enabled($tenant)) {
            $tenant_modules = array_merge(
                $tenant_modules,
                array_column($this->fq_saas_model->default_modules(), 'system_name')
            );
        }

        $known_modules = array_column($this->fq_saas_model->modules(), 'system_name');
        $is_demo_marketplace = fq_saas_demo_tenant_admin_marketplace_enabled($tenant);
        $can_manage = in_array($name, $tenant_modules, true) || ($is_demo_marketplace && in_array($name, $known_modules, true));
        if (!$can_manage) {
            return $this->to_modules();
        }


        $disabled_modules = $this->disabled_modules();
        $dirty = false;

        if ($action === 'enable' && in_array($name, $disabled_modules)) {
            $disabled_modules = array_diff($disabled_modules, [$name]);
            $dirty = true;
        }

        if ($action === 'disable' && !in_array($name, $disabled_modules)) {
            $disabled_modules[] = $name;
            $dirty = true;
        }

        if ($dirty)
            update_option('tenant_local_disabled_modules', json_encode($disabled_modules));

        $this->to_modules();
    }

    private function disabled_modules()
    {
        $disabled_modules = get_option('tenant_local_disabled_modules');
        $disabled_modules = (array)json_decode($disabled_modules ?? '', true);
        return $disabled_modules;
    }

    private function default_module_description(string $system_name): string
    {
        $descriptions = [
            'contracts' => 'Zarządzanie umowami z klientami: rejestr warunków, dat obowiązywania, załączników, odnowień i powiązanych dokumentów handlowych. Pomaga kontrolować terminy oraz utrzymać pełną historię współpracy.',
            'credit_notes' => 'Obsługa korekt i not kredytowych powiązanych ze sprzedażą. Moduł porządkuje zwroty, rozliczenia i dokumenty korygujące, zachowując spójny obieg finansowy.',
            'custom_fields' => 'Elastyczne pola niestandardowe dla kluczowych obiektów CRM. Pozwalają dopasować system do branży, procesów i danych, które firma chce zbierać bez zmian programistycznych.',
            'estimate_request' => 'Formularze zapytań ofertowych, które zbierają potrzeby klientów i porządkują je przed przygotowaniem wyceny. Ułatwia szybkie przejście od zapytania do konkretnej oferty.',
            'estimates' => 'Profesjonalne kosztorysy i wyceny z możliwością wysyłki do klienta, śledzenia akceptacji oraz konwersji do dokumentów sprzedażowych. Skraca drogę od rozmowy handlowej do decyzji.',
            'expenses' => 'Rejestr kosztów firmowych i projektowych z kategoriami, załącznikami oraz powiązaniami z klientami lub projektami. Pomaga kontrolować rentowność i porządek w rozliczeniach.',
            'invoices' => 'Kompletny obieg faktur: wystawianie, wysyłka, statusy płatności, przypomnienia i historia rozliczeń. Moduł wspiera codzienna sprzedaż oraz kontrolowanie należności.',
            'items' => 'Baza produktów i usług wykorzystywanych w ofertach, fakturach i zamówieniach. Ujednolica nazwy, ceny, podatki i pozycje sprzedażowe w dokumentach.',
            'knowledge_base' => 'Baza wiedzy dla zespołu i klientów: artykuły, instrukcje oraz odpowiedzi na najczęstsze pytania. Zmniejsza liczbę powtarzalnych zapytań i przyspiesza onboarding.',
            'leads' => 'Zarządzanie potencjalnymi klientami od pierwszego kontaktu do konwersji. Moduł pozwala kwalifikować leady, przypisywać je do opiekunów i prowadzić uporządkowany proces sprzedaży.',
            'payments' => 'Rejestrowanie i śledzenie płatności powiązanych z fakturami. Zapewnia przejrzysty obraz wpływów, zaległości i historii rozliczeń klienta.',
            'projects' => 'Zarządzanie projektami, etapami, zadaniami, terminami i współpracą z klientem. Moduł daje zespołowi jedno miejsce do planowania pracy, monitorowania postępu i rozliczania rezultatów.',
            'proposals' => 'Tworzenie atrakcyjnych propozycji handlowych z opisem zakresu, cenami i warunkami współpracy. Umożliwia wysyłkę do klienta oraz sprawne przejście do umowy lub faktury.',
            'reports' => 'Raporty operacyjne i finansowe pokazujące kondycję sprzedaży, projektów, faktur, kosztów i aktywności zespołu. Pomaga podejmować decyzje na podstawie danych.',
            'subscriptions' => 'Obsługa cyklicznych usług i płatności abonamentowych. Moduł automatyzuje powtarzalne rozliczenia i ułatwia kontrolowanie aktywnych subskrypcji klientów.',
            'tasks' => 'Zadania, priorytety, terminy, komentarze i odpowiedzialności w jednym miejscu. Moduł porządkuje codzienną pracę zespołu i pozwala monitorować wykonanie obowiązków.',
            'tickets' => 'System obsługi zgłoszeń klientów z priorytetami, statusami i historią komunikacji. Ułatwia szybsze reagowanie, kontrolowanie SLA i utrzymanie wysokiej jakości wsparcia.',
        ];

        return $descriptions[$system_name] ?? _l('fq_saas_demo_marketplace_default_module_desc');
    }

    private function default_module_name(string $system_name): string
    {
        $names = [
            'contracts' => 'Umowy',
            'credit_notes' => 'Korekty',
            'custom_fields' => 'Pola',
            'estimate_request' => 'Zapytania',
            'estimates' => 'Wyceny',
            'expenses' => 'Koszty',
            'invoices' => 'Faktury',
            'items' => 'Produkty',
            'knowledge_base' => 'Baza wiedzy',
            'leads' => 'Leady',
            'payments' => 'Płatności',
            'projects' => 'Projekty',
            'proposals' => 'Oferty',
            'reports' => 'Raporty',
            'subscriptions' => 'Abonamenty',
            'tasks' => 'Zadania',
            'tickets' => 'Zgłoszenia',
        ];

        return $names[$system_name] ?? ucfirst(str_replace('_', ' ', $system_name));
    }

    private function to_modules()
    {
        redirect(admin_url('apps/modules'));
    }
}
