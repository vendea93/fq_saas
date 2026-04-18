# FQ SAAS — changelog i komentarz zmian

Ten dokument zbiera wpisy z changelogu modułu oraz **krótkie wyjaśnienia po polsku**, po co dana zmiana i co sprawdzić po aktualizacji.

Źródło techniczne (lista commitów / wersji w repozytorium): `fq_saas/CHANGELOG.md`.

---

## Wersja 0.4.2

### Co się zmieniło

- Trasy panelu (`/{ADMIN_URI}/fq_saas/affiliates` itd.) były wpisane na sztywno jako `admin/fq_saas/...`. Perfex buduje linki przez `admin_url()`, które używają stałej **`ADMIN_URI`** (albo `CUSTOM_ADMIN_URL`). Gdy segment panelu nie nazywa się `admin`, **żaden route nie pasował → 404** (np. Afiliacja, Kupony).
- Trasy SaaS w panelu były rejestrowane tylko w bloku `if (!fq_saas_is_tenant())`. Przy jakiejkolwiek sytuacji, w której request jest traktowany jak tenant, **te trasy w ogóle nie powstawały** → znowu 404 mimo poprawnego modułu.

### Po aktualizacji

Wgraj nowy zip modułu, wyczyść cache jeśli używasz (np. Opcje → niektóre hostingi), sprawdź `…/{twój_admin_uri}/fq_saas/affiliates`.

---

## Wersja 0.4.1

### Changelog (skrót techniczny)

- **Menu administracyjne:** sprawdzenia `staff_can('view', …)` używają teraz prawidłowych identyfikatorów uprawnień zarejestrowanych w Perfex: `fq_saas_companies` oraz `fq_saas_packages` (wcześniej błędnie: `fq_saas_company` / `fq_saas_package`). Dzięki temu przy włączonych uprawnieniach w roli pojawiają się m.in. Pakiety, Najemcy, Kupony, Afiliacja — a nie tylko Landing.
- **Etykiety:** przed zbudowaniem menu wymuszane jest `lang->load` dla pliku językowego modułu; na hooku `admin_init` rejestracja uprawnień ma priorytet **5**, a budowa menu **50**, żeby kolejność bootstrapu była przewidywalna. Domyślne teksty EN: tytuł paska **FQ SaaS**, pozycja **Discount coupons**.

### Komentarz zmian

W Perfex CRM uprawnienia staffu są powiązane z **dokładnym stringiem** „feature” (np. `fq_saas_companies`). Moduł rejestrował te nazwy w `register_staff_capabilities`, ale część warunków w `fq_saas.php` odwoływała się do **innych** stringów (`fq_saas_company` — liczba pojedyncza). Dla systemu to były **dwa różne feature’y**: jeden zawsze „nieistniejący” w sensie uprawnień, więc `staff_can` zwracał `false` i pozycje menu się nie pokazywały, mimo że w **Ustawienia → Staff → Role** zaznaczono np. firmy/pakiety.

Skutek uboczny, który widzieliście w praktyce: **działał głównie landing**, bo tylko tam zbieżność nazw uprawnień z kodem była przypadkowo zgodna z tym, co macie w roli.

Po 0.4.1: po wdrożeniu **odśwież uprawnienia roli** (ew. zapisz rolę ponownie) i sprawdź sidebar **FQ SaaS** — powinny wrócić wszystkie podpozycje zgodne z zaznaczonymi checkboxami.

---

## Wersja 0.4.0

### Changelog (skrót techniczny)

- Rebrand modułu na **FQ SAAS** (`fq_saas`, stałe `FQ_SAAS_*`, klasy `Fq_saas_*`), przy zachowaniu kompatybilności prefiksów tabel rdzenia `perfex_saas_*` w bazie.
- **Fq_saas_kernel** + log `fq_saas_log` / tabela `tblfq_saas_activity_log`.
- Migracja **035:** tabele rozszerzeń (m.in. landing, kupony, afiliacja, CMS), migracja opcji `perfex_saas_%` → `fq_saas_%`.
- Hooki billingowe pod kernel; admin: landing builder, CMS, kupony, afiliacja, domeny, limity pakietów, widgety dashboardu; API `cms_pages`; afiliacja (`?ref=`, merge fields).

### Komentarz zmian

0.4.0 to **pakiet funkcji SaaS** (multi-tenant, billing Stripe, landing, CMS, kupony, afiliacja) spięty jednym modułem. Baza może nadal używać starych nazw tabel `tblperfex_saas_*` dla rdzenia — to celowe, żeby migracje z wcześniejszych instalacji były możliwe.

---

## Inne poprawki z ostatniej fazy (warto wiedzieć przy testach)

Poniżej nie są osobnymi numerami wersji w `CHANGELOG.md`, ale dotyczą tego samego katalogu `fq_saas/`:

- **`config/middleware_hooks.php`:** inicjalizacja stałych bazy dla tenanta nie odwołuje się do `APP_DB_*_DEFAULT`, dopóki te stałe nie istnieją (uniknięcie błędu przy wczesnym ładowaniu `my_routes.php`). Dodatkowo `fq_saas_apply_tenant_db_constants()` może być dołączona na `app_init`, a w middleware pominięto fałszywy błąd „brak `APP_DB_PREFIX`”, gdy domyślne stałe jeszcze nie są załadowane.
- **`config/app-config.php` (moduł):** jeśli brakuje pliku core helpera (np. częściowe usunięcie modułu), bootstrap SaaS się wycofa zamiast zablokować cały CRM.
- **`helpers/fq_saas_setup_helper.php`:** wstrzyknięcia `require` w `app-config.php` / `my_routes.php` / `my_hooks.php` mogą być owinięte w `file_exists`, żeby usunięcie folderu modułu nie „zbrickowało” Perfexa przed czystym odinstalowaniem (nowe instalacje z instalatora modułu).

---

## Pakiet instalacyjny

Gotowe archiwum modułu (do wgrania do `modules/fq_saas/`):

- `fq_saas-install.zip` w katalogu nadrzędnym względem folderu `fq_saas/` (w tym workspace: obok katalogu `fq_saas`).

Po każdej istotnej zmianie w kodzie **przebuduj zip** poleceniem:

```bash
cd /ścieżka/do/SAAS && zip -rq fq_saas-install.zip fq_saas -x "*.git*" -x "*__MACOSX*" -x "*.DS_Store"
```
