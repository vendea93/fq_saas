<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$mysqli = new mysqli('localhost', 'root', '');
if ($mysqli->connect_error) {
    fwrite(STDERR, $mysqli->connect_error . PHP_EOL);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$tenants = [
    'agencja' => ['logo' => 'agencja.png', 'module' => 'zillapage'],
    'beauty' => ['logo' => 'beauty.png', 'module' => 'appointly'],
    'ecommerce' => ['logo' => 'ecommerce.png', 'module' => 'products'],
    'eventy' => ['logo' => 'wydarzenia.png', 'module' => 'flexstage'],
    'gastronomia' => ['logo' => 'gastronomia.png', 'module' => 'catering_management_module'],
    'hotel' => ['logo' => 'hotel.png', 'module' => 'hotel_management_system'],
    'kursy' => ['logo' => 'kursy.png', 'module' => 'flexacademy'],
    'logistyka' => ['logo' => 'logistyka.png', 'module' => 'logistic'],
    'medycyna' => ['logo' => 'medycyna.png', 'module' => 'appointly'],
    'nieruchomosci' => ['logo' => 'agencja.png', 'module' => 'realestate'],
    'oze' => ['logo' => 'oze.png', 'module' => 'projects'],
    'rekrutacja' => ['logo' => 'rekrutacja.png', 'module' => 'recruitment'],
    'serwiswww' => ['logo' => 'ecommerce.png', 'module' => 'website_maintenance_management'],
    'warsztat' => ['logo' => 'warsztat.png', 'module' => 'workshop'],
];

$moduleNames = [
    'account_planning' => 'Konta', 'affiliate_management' => 'Afiliacja', 'aiagentchat' => 'Czat AI',
    'appointly' => 'Spotkania', 'backup' => 'Kopie', 'call_logs' => 'Połączenia',
    'catering_management_module' => 'Catering', 'coinbase' => 'Krypto', 'custom_email_and_sms_notifications' => 'Powiadomienia',
    'customtables' => 'Tabele', 'delivery_notes' => 'Wydania', 'einvoice' => 'E-faktury',
    'exports' => 'Eksporty', 'feedback' => 'Opinie', 'flexacademy' => 'Kursy',
    'flexform' => 'Formularze', 'flexibackup' => 'Backup', 'flexibleleadfinder' => 'Wyszukiwarka',
    'flexibleleadscore' => 'Scoring', 'flexstage' => 'Wydarzenia', 'flowquest_menu' => 'Menu',
    'form_sync' => 'Formularze', 'google_analytic' => 'Analityka', 'hosting_manager' => 'Hosting',
    'hotel_management_system' => 'Hotel', 'hrm' => 'Kadry', 'idea_hub' => 'Pomysły',
    'ideal' => 'Płatności', 'invoices_builder' => 'Faktury PDF', 'logistic' => 'Logistyka',
    'mailflow' => 'Mailing', 'manufacturing' => 'Produkcja', 'menu_setup' => 'Menu',
    'mercadopago_gateway' => 'Mercado Pago', 'openai' => 'AI', 'otpless' => 'Logowanie',
    'perfex_dashboard' => 'Pulpit', 'perfex_email_builder' => 'E-maile', 'perfex_office_theme' => 'Motyw',
    'perfex_popup' => 'Pop-upy', 'perfshield' => 'Ochrona', 'prchat' => 'Czat',
    'project_roadmap' => 'Roadmapa', 'purchase_orders' => 'Zakupy', 'realestate' => 'Nieruchomości',
    'reputation' => 'Reputacja', 'service_management' => 'Serwis', 'surveys' => 'Ankiety',
    'task_signing' => 'Podpisy', 'telegram_chat' => 'Telegram', 'theme_style' => 'Wygląd',
    'translations' => 'Tłumaczenia', 'website_maintenance_management' => 'Strony WWW',
    'wiki' => 'Wiki', 'workshop' => 'Warsztat', 'xml_exports' => 'XML', 'zillapage' => 'Landing Page',
    'contracts' => 'Umowy', 'credit_notes' => 'Korekty', 'custom_fields' => 'Pola',
    'estimate_request' => 'Zapytania', 'estimates' => 'Wyceny', 'expenses' => 'Koszty',
    'invoices' => 'Faktury', 'items' => 'Produkty', 'knowledge_base' => 'Baza wiedzy',
    'leads' => 'Leady', 'payments' => 'Płatności', 'projects' => 'Projekty',
    'proposals' => 'Oferty', 'reports' => 'Raporty', 'subscriptions' => 'Abonamenty',
    'tasks' => 'Zadania', 'tickets' => 'Zgłoszenia',
];

$marketplace = [
    'account_planning' => 'Planowanie pracy z kluczowymi klientami: cele, kontakty decyzyjne, ryzyka, szanse sprzedażowe i zadania opiekuna w jednym widoku. Pomaga prowadzić strategiczne konta bez rozproszenia notatek.',
    'affiliate_management' => 'Program partnerski z rejestracją partnerów, śledzeniem poleceń, prowizjami i rozliczeniami. Daje kontrolę nad kanałem afiliacyjnym od linku polecającego po wypłatę.',
    'aiagentchat' => 'Czat AI wspierający obsługę klienta i pracę zespołu. Pozwala automatyzować odpowiedzi, porządkować kontekst rozmów i szybciej reagować na typowe pytania.',
    'appointly' => 'Rezerwacje spotkań z typami wizyt, terminami, danymi uczestników, przypomnieniami i statusem akceptacji. Idealne dla usług, konsultacji, gabinetów i salonów.',
    'backup' => 'Kopie bezpieczeństwa plików i danych systemu. Moduł ułatwia cykliczne zabezpieczanie środowiska oraz szybkie odtworzenie pracy po awarii.',
    'call_logs' => 'Rejestr połączeń z klientami, tematami rozmów, opiekunami i dalszymi krokami. Ułatwia utrzymanie historii kontaktu i kontrolę follow-upów.',
    'catering_management_module' => 'Obsługa cateringu od zapytania po realizację: wydarzenia, menu, pakiety, alergeny, diety, składniki, personel, notatki i kalkulacja kosztów na gościa.',
    'coinbase' => 'Bramka płatności kryptowalutowych przez Coinbase Commerce. Pozwala przyjmować płatności cyfrowe i łączyć je z dokumentami sprzedażowymi.',
    'custom_email_and_sms_notifications' => 'Konfigurowalne powiadomienia e-mail i SMS dla ważnych zdarzeń w systemie. Pomaga automatycznie informować klientów i zespół o statusach, terminach oraz zmianach.',
    'customtables' => 'Własne tabele danych bez programowania. Moduł pozwala budować dedykowane rejestry, pola i listy pod procesy, których nie obejmuje standardowy CRM.',
    'delivery_notes' => 'Dokumenty wydań i dostaw powiązane ze sprzedażą. Porządkuje przekazanie towaru, potwierdzenia odbioru i historię realizacji zamówień.',
    'einvoice' => 'Obsługa elektronicznych faktur i eksportów zgodnych z wymaganiami księgowymi. Przyspiesza przekazywanie dokumentów oraz ogranicza ręczne przepisywanie danych.',
    'exports' => 'Eksport danych z systemu do plików i integracji. Przydatne do raportowania, księgowości, archiwizacji oraz pracy z zewnętrznymi narzędziami.',
    'feedback' => 'Zbieranie opinii klientów po usługach, projektach lub zgłoszeniach. Ułatwia mierzenie satysfakcji i wyłapywanie obszarów wymagających poprawy.',
    'flexacademy' => 'Platforma kursów z kategoriami, lekcjami, sekcjami, zapisami uczestników, quizami i certyfikatami. Pozwala sprzedawać lub udostępniać wiedzę klientom i zespołowi.',
    'flexform' => 'Zaawansowane formularze z blokami, logiką pól i zapisem odpowiedzi. Sprawdza się przy ankietach, kwalifikacji leadów, zgłoszeniach i briefach.',
    'flexibackup' => 'Elastyczne kopie zapasowe z obsługą harmonogramów i archiwów. Zapewnia dodatkową warstwę bezpieczeństwa dla danych operacyjnych.',
    'flexibleleadfinder' => 'Narzędzie do wyszukiwania i porządkowania potencjalnych klientów. Pomaga budować bazę leadów, uzupełniać kontakty i rozpoczynać działania sprzedażowe.',
    'flexibleleadscore' => 'Scoring leadów według kryteriów jakości i gotowości zakupowej. Pozwala zespołowi sprzedaży skupić się na kontaktach o najwyższym potencjale.',
    'flexstage' => 'Organizacja wydarzeń z prelegentami, biletami, zamówieniami, kategoriami i stronami wydarzeń. Moduł wspiera konferencje, szkolenia, webinary i eventy płatne.',
    'flowquest_menu' => 'Zarządzanie układem menu FlowQuest. Pozwala uporządkować nawigację pod wybrany proces, rolę użytkownika lub branżowe demo.',
    'form_sync' => 'Synchronizacja formularzy i danych zgłoszeniowych między stroną, CRM i procesami sprzedaży. Ogranicza ręczne przenoszenie leadów.',
    'google_analytic' => 'Integracja z Google Analytics dla śledzenia ruchu i konwersji. Pomaga mierzyć skuteczność stron, kampanii i formularzy.',
    'hosting_manager' => 'Zarządzanie usługami hostingowymi, pakietami, domenami i cyklami obsługi klienta. Dobre dla firm utrzymujących strony lub aplikacje.',
    'hotel_management_system' => 'System hotelowy z obiektami, pokojami, rezerwacjami, usługami dodatkowymi, cenami, statusem płatności i obsługą gości.',
    'hrm' => 'Kadry i zasoby ludzkie: pracownicy, struktura, obecności, wnioski i procesy HR. Ułatwia administrację personelem.',
    'idea_hub' => 'Zbieranie i ocenianie pomysłów od zespołu lub klientów. Pomaga porządkować inicjatywy, priorytety i decyzje produktowe.',
    'ideal' => 'Bramka płatności iDEAL dla klientów korzystających z bankowości wspierającej ten standard. Ułatwia szybkie płatności online.',
    'invoices_builder' => 'Projektowanie szablonów faktur i dokumentów PDF. Pozwala dopasować wygląd dokumentów do marki oraz wymagań sprzedażowych.',
    'logistic' => 'Obsługa logistyki: przesyłki, paczki, odbiorcy, adresy, statusy dostawy, śledzenie, koszty, kurierzy i dokumenty przewozowe.',
    'mailflow' => 'Kampanie i automatyzacje e-mailowe dla klientów oraz leadów. Pomaga planować komunikację, segmentować odbiorców i mierzyć reakcje.',
    'manufacturing' => 'Procesy produkcyjne: zlecenia, materiały, etapy, koszty i realizacja. Wspiera firmy, które muszą łączyć sprzedaż z wytwarzaniem.',
    'menu_setup' => 'Konfiguracja menu i widoczności elementów interfejsu. Pozwala uprościć panel użytkownika pod konkretne demo lub rolę.',
    'mercadopago_gateway' => 'Integracja płatności Mercado Pago. Umożliwia przyjmowanie płatności online i wiązanie ich z fakturami.',
    'openai' => 'Funkcje AI w CRM: generowanie treści, wsparcie komunikacji i automatyzacja pracy z tekstem. Przyspiesza codzienne działania zespołu.',
    'otpless' => 'Logowanie bez hasła przez jednorazowe potwierdzenia. Zmniejsza tarcie przy dostępie i poprawia wygodę użytkowników.',
    'perfex_dashboard' => 'Rozszerzony pulpit z widżetami, skrótami i wskaźnikami. Pomaga szybciej oceniać stan sprzedaży, projektów i obsługi.',
    'perfex_email_builder' => 'Kreator wiadomości e-mail z szablonami i układem wizualnym. Ułatwia przygotowanie spójnej komunikacji firmowej.',
    'perfex_office_theme' => 'Motyw panelu administracyjnego z dopasowaniem wyglądu i ergonomii pracy. Nadaje systemowi bardziej spójny charakter wizualny.',
    'perfex_popup' => 'Pop-upy i komunikaty na stronach oraz w panelu. Przydatne do promocji, ogłoszeń, formularzy i krótkich wezwań do działania.',
    'perfshield' => 'Dodatkowe zabezpieczenia systemu, kontrola dostępu i ochrona wybranych obszarów CRM. Wzmacnia bezpieczeństwo instalacji.',
    'prchat' => 'Czat wewnętrzny i komunikacja zespołowa w CRM. Ułatwia szybkie ustalenia przy klientach, projektach i zgłoszeniach.',
    'project_roadmap' => 'Roadmapa projektów i inicjatyw z etapami, planami i priorytetami. Pomaga pokazać kierunek prac oraz postęp realizacji.',
    'purchase_orders' => 'Zamówienia zakupu, dostawcy, pozycje kosztowe i status realizacji. Uporządkowuje proces zakupowy od potrzeby do dokumentu.',
    'realestate' => 'Obsługa nieruchomości jako ofert z parametrami lokali, cenami, lokalizacją, właścicielem, statusem i szczegółami technicznymi.',
    'reputation' => 'Monitorowanie reputacji i opinii. Pomaga zbierać recenzje, reagować na oceny oraz budować wiarygodność firmy.',
    'service_management' => 'Zarządzanie usługami, zgłoszeniami serwisowymi, harmonogramem i realizacją. Dobre dla firm utrzymujących sprzęt lub instalacje.',
    'surveys' => 'Ankiety dla klientów i zespołu z odpowiedziami oraz analizą wyników. Wspiera badanie satysfakcji, potrzeb i jakości usług.',
    'task_signing' => 'Podpisywanie i akceptowanie zadań lub protokołów. Ułatwia formalne potwierdzanie wykonania pracy.',
    'telegram_chat' => 'Integracja komunikacji z Telegramem. Pozwala przenosić powiadomienia i rozmowy bliżej kanałów używanych przez klientów.',
    'theme_style' => 'Personalizacja wyglądu panelu: kolory, logo i elementy identyfikacji. Pomaga dopasować system do marki.',
    'translations' => 'Zarządzanie tłumaczeniami interfejsu i tekstów systemowych. Przydatne przy lokalizacji CRM dla różnych języków.',
    'website_maintenance_management' => 'Utrzymanie stron WWW: witryny klientów, pakiety godzin, zadania serwisowe, logi prac, rozliczenia i historia opieki technicznej.',
    'wiki' => 'Wewnętrzna baza wiedzy dla procedur, instrukcji i ustaleń. Pozwala zespołowi szybciej znaleźć aktualne informacje.',
    'workshop' => 'Obsługa warsztatu: urządzenia lub pojazdy, zlecenia napraw, usługi robocizny, inspekcje, terminy, koszty i status realizacji.',
    'xml_exports' => 'Eksport danych do formatów XML dla integracji, księgowości lub wymiany z zewnętrznymi systemami.',
    'zillapage' => 'Kreator landing page z edycją stron, formularzami, leadami, SEO, publikacją i stroną podziękowania po wysłaniu formularza.',
];

function q(mysqli $db, string $sql): void
{
    if (!$db->query($sql)) {
        throw new RuntimeException($db->error . ' SQL: ' . $sql);
    }
}

function esc(mysqli $db, ?string $value): string
{
    return $value === null ? 'NULL' : "'" . $db->real_escape_string($value) . "'";
}

function upsertOption(mysqli $db, string $database, string $prefix, string $name, string $value): void
{
    $table = "`$database`.`{$prefix}_tbloptions`";
    upsertOptionTable($db, $table, $name, $value);
}

function upsertOptionTable(mysqli $db, string $table, string $name, string $value): void
{
    $ids = [];
    $result = $db->query("SELECT `id` FROM $table WHERE `name`=" . esc($db, $name) . ' ORDER BY `id` ASC');
    while ($result && $row = $result->fetch_assoc()) {
        $ids[] = (int)$row['id'];
    }

    if (!$ids) {
        q($db, "INSERT INTO $table (`name`,`value`,`autoload`) VALUES (" . esc($db, $name) . ',' . esc($db, $value) . ',1)');
        return;
    }

    $keep = $ids[0];
    q($db, "UPDATE $table SET `value`=" . esc($db, $value) . ", `autoload`=1 WHERE `id`=$keep");
    if (count($ids) > 1) {
        q($db, "DELETE FROM $table WHERE `id` IN (" . implode(',', array_slice($ids, 1)) . ')');
    }
}

function ensureModule(mysqli $db, string $database, string $prefix, string $module): void
{
    if ($module === 'products') {
        $module = 'items';
    }
    $table = "`$database`.`{$prefix}_tblmodules`";
    $result = $db->query("SELECT `id` FROM $table WHERE `module_name`=" . esc($db, $module) . ' LIMIT 1');
    if ($result && $row = $result->fetch_assoc()) {
        q($db, "UPDATE $table SET `active`=1 WHERE `id`=" . (int)$row['id']);
        return;
    }
    q($db, "INSERT INTO $table (`module_name`,`installed_version`,`active`) VALUES (" . esc($db, $module) . ",'1.0.0',1)");
}

function ensureTable(mysqli $db, string $database, string $prefix, string $suffix, ?string $sourceDb = 'perfex_db', ?string $sourcePrefix = ''): void
{
    $target = "{$prefix}_tbl{$suffix}";
    $source = "{$sourcePrefix}tbl{$suffix}";
    $exists = $db->query("SHOW TABLES FROM `$database` LIKE " . esc($db, $target))->num_rows > 0;
    if (!$exists) {
        q($db, "CREATE TABLE `$database`.`$target` LIKE `$sourceDb`.`$source`");
    }
}

function truncateTables(mysqli $db, string $database, string $prefix, array $suffixes): void
{
    q($db, 'SET FOREIGN_KEY_CHECKS=0');
    foreach ($suffixes as $suffix) {
        $table = "`$database`.`{$prefix}_tbl{$suffix}`";
        $exists = $db->query("SHOW TABLES FROM `$database` LIKE " . esc($db, "{$prefix}_tbl{$suffix}"))->num_rows > 0;
        if ($exists) {
            q($db, "TRUNCATE TABLE $table");
        }
    }
    q($db, 'SET FOREIGN_KEY_CHECKS=1');
}

function insertRows(mysqli $db, string $database, string $prefix, string $suffix, array $rows): void
{
    if (!$rows) {
        return;
    }
    $columns = array_keys($rows[0]);
    $values = [];
    foreach ($rows as $row) {
        $values[] = '(' . implode(',', array_map(fn($column) => esc($db, isset($row[$column]) ? (string)$row[$column] : null), $columns)) . ')';
    }
    q($db, "INSERT INTO `$database`.`{$prefix}_tbl{$suffix}` (`" . implode('`,`', $columns) . '`) VALUES ' . implode(',', $values));
}

function seedBasics(mysqli $db, string $database, string $prefix, string $company): void
{
    q($db, "UPDATE `$database`.`{$prefix}_tblclients` SET `company`=" . esc($db, $company) . " WHERE `userid`=1");
}

function seedHotel(mysqli $db, string $database, string $prefix): void
{
    foreach (['hms_landlords','hms_properties','hms_rooms','hms_services','hms_bookings','hms_service_assignments'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['hms_service_assignments','hms_bookings','hms_services','hms_rooms','hms_properties','hms_landlords']);
    insertRows($db, $database, $prefix, 'hms_landlords', [['id'=>1,'name'=>'FlowQuest Hospitality','email'=>'hotel@flowquest.pl','phone'=>'+48 22 100 20 30','datecreated'=>'2026-04-29 10:00:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'hms_properties', [['id'=>1,'landlord_id'=>1,'name'=>'FlowQuest Boutique Hotel','address'=>'ul. Nadbrzeżna 12','city'=>'Gdańsk','country'=>'Polska','property_type'=>'hotel','description'=>'Kameralny hotel biznesowo-wypoczynkowy z pokojami premium i obsługą konferencji.','amenities'=>'parking, spa, restauracja, sale konferencyjne','rules'=>'Doba hotelowa 15:00-11:00','check_in_time'=>'15:00:00','check_out_time'=>'11:00:00','status'=>'active','featured'=>1,'datecreated'=>'2026-04-29 10:00:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'hms_rooms', [
        ['id'=>1,'property_id'=>1,'name'=>'Pokój Business 101','room_type'=>'business','description'=>'Pokój dla osób podróżujących służbowo z biurkiem i szybkim Wi-Fi.','capacity'=>2,'bed_type'=>'queen','num_beds'=>1,'room_size'=>'24.00','amenities'=>'Wi-Fi, biurko, ekspres, sejf','price_per_night'=>'420.00','cleaning_fee'=>'35.00','tax_rate'=>'8.00','status'=>'available','datecreated'=>'2026-04-29 10:05:00','created_by'=>1],
        ['id'=>2,'property_id'=>1,'name'=>'Apartament Marina','room_type'=>'apartment','description'=>'Apartament rodzinny z widokiem na marinę i strefą dzienną.','capacity'=>4,'bed_type'=>'king + sofa','num_beds'=>2,'room_size'=>'48.00','amenities'=>'balkon, aneks, Wi-Fi, śniadanie','price_per_night'=>'780.00','cleaning_fee'=>'60.00','tax_rate'=>'8.00','status'=>'reserved','datecreated'=>'2026-04-29 10:06:00','created_by'=>1],
        ['id'=>3,'property_id'=>1,'name'=>'Pokój Standard 204','room_type'=>'standard','description'=>'Wygodny pokój dla krótkich pobytów miejskich.','capacity'=>2,'bed_type'=>'twin','num_beds'=>2,'room_size'=>'21.00','amenities'=>'Wi-Fi, TV, klimatyzacja','price_per_night'=>'310.00','cleaning_fee'=>'30.00','tax_rate'=>'8.00','status'=>'available','datecreated'=>'2026-04-29 10:07:00','created_by'=>1],
    ]);
    insertRows($db, $database, $prefix, 'hms_services', [
        ['id'=>1,'name'=>'Śniadanie premium','description'=>'Bufet śniadaniowy z lokalnymi produktami.','service_type'=>'food','price'=>'69.00','duration_minutes'=>60,'status'=>'active','datecreated'=>'2026-04-29 10:10:00','created_by'=>1],
        ['id'=>2,'name'=>'Transfer lotniskowy','description'=>'Odbiór lub odwóz gościa na lotnisko.','service_type'=>'transport','price'=>'140.00','duration_minutes'=>45,'status'=>'active','datecreated'=>'2026-04-29 10:11:00','created_by'=>1],
    ]);
    insertRows($db, $database, $prefix, 'hms_bookings', [
        ['id'=>1,'room_id'=>2,'client_id'=>1,'booking_reference'=>'FQ-HOT-2026-001','guest_name'=>'Anna Kowalska','guest_email'=>'anna.kowalska@example.com','guest_phone'=>'+48 501 200 300','check_in_date'=>'2026-05-08','check_out_date'=>'2026-05-11','adults'=>2,'children'=>1,'special_requests'=>'Łóżeczko dziecięce i późny check-in.','total_nights'=>3,'room_price'=>'780.00','cleaning_fee'=>'60.00','additional_services'=>'278.00','taxes'=>'197.84','total_amount'=>'2875.84','payment_status'=>'paid','booking_status'=>'confirmed','datecreated'=>'2026-04-29 10:15:00','created_by'=>1],
        ['id'=>2,'room_id'=>1,'client_id'=>1,'booking_reference'=>'FQ-HOT-2026-002','guest_name'=>'Marek Nowak','guest_email'=>'marek.nowak@example.com','guest_phone'=>'+48 602 400 500','check_in_date'=>'2026-05-14','check_out_date'=>'2026-05-16','adults'=>1,'children'=>0,'special_requests'=>'Faktura na firmę.','total_nights'=>2,'room_price'=>'420.00','cleaning_fee'=>'35.00','additional_services'=>'140.00','taxes'=>'81.20','total_amount'=>'1096.20','payment_status'=>'pending','booking_status'=>'confirmed','datecreated'=>'2026-04-29 10:16:00','created_by'=>1],
    ]);
}

function seedProducts(mysqli $db, string $database, string $prefix): void
{
    truncateTables($db, $database, $prefix, ['itemable','items','items_groups']);
    insertRows($db, $database, $prefix, 'items_groups', [['id'=>1,'name'=>'Sklep demo','commodity_group_code'=>'ECOM','order'=>1,'display'=>1,'note'=>'Produkty pokazowe dla e-commerce.']]);
    insertRows($db, $database, $prefix, 'items', [
        ['id'=>1,'description'=>'Zestaw OZE Smart Home','long_description'=>'Pakiet czujników i sterowania energią dla domu jednorodzinnego.','rate'=>'1299.00','unit'=>'szt.','group_id'=>1,'commodity_code'=>'FQ-ECO-001','commodity_name'=>'Zestaw OZE Smart Home','sku_code'=>'FQ-ECO-001','sku_name'=>'Smart Home Energy','purchase_price'=>'820.00','can_be_sold'=>'can_be_sold','can_be_purchased'=>'can_be_purchased','description_sale'=>'Gotowy zestaw do monitoringu zużycia energii.','active'=>1],
        ['id'=>2,'description'=>'Abonament serwisowy Premium','long_description'=>'Miesięczna opieka, monitoring i szybkie wsparcie techniczne.','rate'=>'249.00','unit'=>'mies.','group_id'=>1,'commodity_code'=>'FQ-SVC-002','commodity_name'=>'Abonament serwisowy Premium','sku_code'=>'FQ-SVC-002','sku_name'=>'Premium Care','purchase_price'=>'0.00','can_be_sold'=>'can_be_sold','can_be_purchased'=>'','service_type'=>'normal','subscription_price'=>'249.00','subscription_period'=>'month','subscription_count'=>1,'active'=>1],
        ['id'=>3,'description'=>'Panel dotykowy FlowQuest','long_description'=>'Panel do prezentacji danych sklepu, energii lub rezerwacji.','rate'=>'1890.00','unit'=>'szt.','group_id'=>1,'commodity_code'=>'FQ-PNL-003','commodity_name'=>'Panel dotykowy FlowQuest','sku_code'=>'FQ-PNL-003','sku_name'=>'Touch Panel','purchase_price'=>'1190.00','can_be_sold'=>'can_be_sold','can_be_purchased'=>'can_be_purchased','active'=>1],
    ]);
}

function seedFlexstage(mysqli $db, string $database, string $prefix): void
{
    foreach (['flexevents','flexspeakers','flextickets','flexticketorders'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['flexticketorders','flextickets','flexspeakers','flexevents']);
    insertRows($db, $database, $prefix, 'flexevents', [['id'=>1,'name'=>'FlowQuest Summit 2026','slug'=>'flowquest-summit-2026','description'=>'Konferencja dla firm wdrażających CRM, automatyzację i sprzedaż cyfrową.','status'=>1,'start_date'=>'2026-06-18 09:00:00','end_date'=>'2026-06-18 17:30:00','create_date'=>'2026-04-29 11:00:00','category_id'=>1,'summary'=>'Jeden dzień praktycznych wystąpień, warsztatów i networkingu.','type'=>'offline','location'=>'Warszawa, Centrum Konferencyjne','privacy'=>'public','created_by'=>1,'tags'=>'crm,automatyzacja,sprzedaż']]);
    insertRows($db, $database, $prefix, 'flexspeakers', [
        ['id'=>1,'event_id'=>1,'name'=>'Katarzyna Zielińska','email'=>'katarzyna@example.com','image'=>'','show'=>1,'bio'=>'Ekspertka procesów sprzedażowych i automatyzacji CRM.'],
        ['id'=>2,'event_id'=>1,'name'=>'Piotr Wiśniewski','email'=>'piotr@example.com','image'=>'','show'=>1,'bio'=>'Konsultant wdrożeń SaaS i integracji danych.'],
    ]);
    insertRows($db, $database, $prefix, 'flextickets', [
        ['id'=>1,'event_id'=>1,'name'=>'Early Bird','status'=>'active','quantity'=>80,'paid'=>1,'currency'=>'PLN','price'=>'349.000','min_buying_limit'=>1,'max_buying_limit'=>4,'sales_start_date'=>'2026-04-29 00:00:00','sales_end_date'=>'2026-05-31 23:59:00','description'=>'Bilet w przedsprzedaży z dostępem do sesji i lunchu.'],
        ['id'=>2,'event_id'=>1,'name'=>'VIP Workshop','status'=>'active','quantity'=>20,'paid'=>1,'currency'=>'PLN','price'=>'899.000','min_buying_limit'=>1,'max_buying_limit'=>2,'sales_start_date'=>'2026-04-29 00:00:00','sales_end_date'=>'2026-06-10 23:59:00','description'=>'Bilet z warsztatem zamkniętym i konsultacją po wydarzeniu.'],
    ]);
    insertRows($db, $database, $prefix, 'flexticketorders', [['id'=>1,'eventid'=>1,'attendee_name'=>'Joanna Eventowa','attendee_email'=>'joanna@example.com','attendee_mobile'=>'+48 500 111 222','attendee_company'=>'Demo Events','total_amount'=>'349.000','tickets_sent'=>1,'in_leads'=>1]]);
}

function seedCatering(mysqli $db, string $database, string $prefix): void
{
    foreach (['catering_event_types','catering_menu_categories','catering_menus','catering_menu_items','catering_packages','catering_events'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['catering_events','catering_packages','catering_menu_items','catering_menus','catering_menu_categories','catering_event_types']);
    insertRows($db, $database, $prefix, 'catering_event_types', [['etid'=>1,'name'=>'Bankiet firmowy','background_color'=>'#2f80ed','text_color'=>'#ffffff','sort_order'=>1,'editable'=>1,'created_by'=>1]]);
    insertRows($db, $database, $prefix, 'catering_menu_categories', [['id'=>1,'name'=>'Dania główne','icon'=>'cutlery','color'=>'#27ae60','display_order'=>1,'active'=>1,'created_by'=>1]]);
    insertRows($db, $database, $prefix, 'catering_menus', [['id'=>1,'menu_name'=>'Menu Biznes Premium','description'=>'Menu bankietowe dla spotkań zarządu i konferencji.','base_price_per_person'=>'145.00','active'=>1,'created_by'=>1]]);
    insertRows($db, $database, $prefix, 'catering_menu_items', [
        ['id'=>1,'item_name'=>'Polędwiczka w sosie borowikowym','category_id'=>1,'description'=>'Danie główne z puree selerowym i warzywami sezonowymi.','unit_cost'=>'42.00','unit_price'=>'79.00','default_portion_size'=>'1 porcja','prep_time_minutes'=>35,'version'=>1,'active'=>1,'created_by'=>1],
        ['id'=>2,'item_name'=>'Tarta warzywna premium','category_id'=>1,'description'=>'Opcja wegetariańska z serem kozim i ziołami.','unit_cost'=>'28.00','unit_price'=>'55.00','default_portion_size'=>'1 porcja','prep_time_minutes'=>25,'version'=>1,'active'=>1,'created_by'=>1],
    ]);
    insertRows($db, $database, $prefix, 'catering_packages', [['id'=>1,'package_name'=>'Pakiet Konferencja 80','description'=>'Przerwa kawowa, lunch, napoje i obsługa kelnerska dla wydarzenia biznesowego.','price_per_person'=>'189.00','min_guests'=>40,'max_guests'=>120,'active'=>1,'created_by'=>1]]);
    insertRows($db, $database, $prefix, 'catering_events', [['eventid'=>1,'hash'=>'fqcat202604290001','client_id'=>1,'event_name'=>'Premiera produktu FlowQuest','event_type_id'=>1,'status'=>'confirmed','venue_name'=>'Centrum Biznesowe Praga','venue_address'=>'Warszawa, ul. Konferencyjna 8','event_start'=>'2026-05-22 12:00:00','event_end'=>'2026-05-22 18:00:00','guest_count_expected'=>80,'guest_count_final'=>76,'dietary_notes'=>'12 posiłków wegetariańskich, 4 bezglutenowe.','allergen_summary'=>'Orzechy i gluten oznaczone przy bufecie.','internal_notes'=>'Dostawa sprzętu do godziny 9:30.','kanban_order'=>1,'created_by'=>1]]);
}

function seedFlexacademy(mysqli $db, string $database, string $prefix): void
{
    foreach (['flexacademy_categories','flexacademy_courses','flexacademy_sections','flexacademy_lessons','flexacademy_enrollments'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['flexacademy_enrollments','flexacademy_lessons','flexacademy_sections','flexacademy_courses','flexacademy_categories']);
    insertRows($db, $database, $prefix, 'flexacademy_categories', [['id'=>1,'title'=>'Sprzedaż i CRM','description'=>'Kursy wdrożeniowe dla zespołów handlowych.','parent_id'=>0,'sort_order'=>1,'status'=>'active','created_at'=>'2026-04-29 12:00:00']]);
    insertRows($db, $database, $prefix, 'flexacademy_courses', [['id'=>1,'title'=>'Akademia CRM dla zespołu sprzedaży','slug'=>'akademia-crm-sprzedaz','description'=>'Kompletny kurs pracy z leadami, szansami sprzedaży, zadaniami i raportami.','requirements'=>'Podstawowa znajomość procesu sprzedaży.','outcomes'=>'Uczestnik potrafi prowadzić klienta od leada do faktury.','short_description'=>'Praktyczny kurs CRM dla handlowców.','category_id'=>1,'creator_id'=>1,'price'=>'499.00','discount_price'=>'399.00','pricing_type'=>'paid','difficulty_level'=>'beginner','status'=>'published','language'=>'pl','max_students'=>50,'privacy'=>'public','access'=>'everyone','created_at'=>'2026-04-29 12:05:00']]);
    insertRows($db, $database, $prefix, 'flexacademy_sections', [['id'=>1,'title'=>'Podstawy procesu','course_id'=>1,'sort_order'=>1,'created_at'=>'2026-04-29 12:10:00'],['id'=>2,'title'=>'Automatyzacja pracy','course_id'=>1,'sort_order'=>2,'created_at'=>'2026-04-29 12:11:00']]);
    insertRows($db, $database, $prefix, 'flexacademy_lessons', [
        ['id'=>1,'course_id'=>1,'section_id'=>1,'title'=>'Mapa procesu sprzedaży','lesson_type'=>'text','text_lesson'=>'Jak opisać etapy sprzedaży i dopasować je do CRM.','summary'=>'Leady, statusy, zadania i odpowiedzialności.','duration'=>18,'sort_order'=>1,'is_free'=>1,'status'=>'published','created_at'=>'2026-04-29 12:15:00'],
        ['id'=>2,'course_id'=>1,'section_id'=>2,'title'=>'Automatyczne przypomnienia','lesson_type'=>'text','text_lesson'=>'Ustawianie przypomnień, follow-upów i pracy z aktywnościami.','summary'=>'Codzienna praca bez gubienia terminów.','duration'=>22,'sort_order'=>1,'is_free'=>0,'status'=>'published','created_at'=>'2026-04-29 12:16:00'],
    ]);
    insertRows($db, $database, $prefix, 'flexacademy_enrollments', [['id'=>1,'course_id'=>1,'student_id'=>1,'student_type'=>'client','status'=>'active','progress'=>'35.00','enrolled_at'=>'2026-04-29 12:20:00','enrollment_date'=>'2026-04-29 12:20:00','payment_status'=>'paid','amount_paid'=>'399.00','payment_date'=>'2026-04-29 12:22:00','created_at'=>'2026-04-29 12:20:00']]);
}

function seedLogistic(mysqli $db, string $database, string $prefix): void
{
    foreach (['lg_recipients','lg_packages','lg_package_detail','lg_shippings','lg_shipping_detail','lg_tracking_history'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['lg_tracking_history','lg_shipping_detail','lg_shippings','lg_package_detail','lg_packages','lg_recipients']);
    insertRows($db, $database, $prefix, 'lg_recipients', [['id'=>1,'client_id'=>1,'first_name'=>'Michał','last_name'=>'Dostawski','phone'=>'+48 600 700 800','email'=>'odbiorca@example.com','created_by_type'=>'staff','created_at'=>'2026-04-29 13:00:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'lg_packages', [['id'=>1,'shipping_prefix'=>'FQL','number_code'=>'FQL-0001','number'=>1,'number_type'=>'package','customer_id'=>1,'tracking_purchase'=>'TRK-FQL-0001','store_supplier'=>'Magazyn centralny','purchase_price'=>'1200.00','delivery_status'=>2,'subtotal'=>'230.00','total'=>'282.90','currency'=>1,'currency_rate'=>'1.000000','from_currency'=>'PLN','to_currency'=>'PLN','created_at'=>'2026-04-29 13:10:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'lg_package_detail', [['id'=>1,'package_id'=>1,'amount'=>'1.00','weight'=>'18.50','length'=>'80.00','width'=>'40.00','height'=>'35.00','weight_vol'=>'22.40','fixed_charge'=>'30.00','dec_value'=>'1200.00','package_description'=>'Falownik hybrydowy do instalacji demo.','created_at'=>'2026-04-29 13:11:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'lg_shippings', [['id'=>1,'shipping_prefix'=>'FQS','number_code'=>'FQS-0001','number'=>1,'number_type'=>'shipping','customer_id'=>1,'recipient_id'=>1,'tracking_purchase'=>'SHIP-FQS-0001','store_supplier'=>'Magazyn centralny','purchase_price'=>'1200.00','delivery_status'=>3,'subtotal'=>'260.00','total'=>'319.80','currency'=>1,'currency_rate'=>'1.000000','from_currency'=>'PLN','to_currency'=>'PLN','shipping_type'=>'standard','approve_status'=>'approved','created_from'=>'admin','created_at'=>'2026-04-29 13:12:00','created_by'=>1]]);
    insertRows($db, $database, $prefix, 'lg_shipping_detail', [['id'=>1,'shipping_id'=>1,'amount'=>'1.00','weight'=>'18.50','length'=>'80.00','width'=>'40.00','height'=>'35.00','weight_vol'=>'22.40','fixed_charge'=>'30.00','dec_value'=>'1200.00','package_description'=>'Falownik hybrydowy z ubezpieczeniem transportu.','created_at'=>'2026-04-29 13:13:00','created_by'=>1]]);
}

function seedRecruitment(mysqli $db, string $database, string $prefix): void
{
    foreach (['rec_job_position','rec_campaign','rec_candidate','rec_interview'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['rec_interview','rec_candidate','rec_campaign','rec_job_position']);
    insertRows($db, $database, $prefix, 'rec_job_position', [['position_id'=>1,'position_name'=>'Specjalista CRM','position_description'=>'Osoba odpowiedzialna za obsługę klientów, konfigurację procesów i wsparcie sprzedaży.']]);
    insertRows($db, $database, $prefix, 'rec_campaign', [['cp_id'=>1,'campaign_code'=>'REC-CRM-2026','campaign_name'=>'Rekrutacja Specjalista CRM','cp_position'=>1,'cp_amount_recruiment'=>2,'cp_form_work'=>'hybrydowa','cp_workplace'=>'Warszawa','cp_salary_from'=>'9000','cp_salary_to'=>'13000','cp_from_date'=>'2026-04-29','cp_to_date'=>'2026-05-31','cp_reason_recruitment'=>'Rozbudowa zespołu wdrożeń.','cp_job_description'=>'Prowadzenie wdrożeń CRM, szkolenia klientów i konfiguracja automatyzacji.','cp_manager'=>'1','cp_follower'=>'1','cp_experience'=>'2 lata w CRM lub SaaS','cp_add_from'=>1,'cp_date_add'=>'2026-04-29','cp_status'=>1]]);
    insertRows($db, $database, $prefix, 'rec_candidate', [['id'=>1,'rec_campaign'=>1,'candidate_code'=>'CAN-001','candidate_name'=>'Aleksandra Wójcik','birthday'=>'1992-08-14','gender'=>'female','nation'=>'Polska','introduce_yourself'=>'Konsultantka CRM z doświadczeniem w szkoleniach sprzedaży.','phonenumber'=>'+48 510 222 333','email'=>'aleksandra@example.com','status'=>1,'rate'=>5,'desired_salary'=>'12000','date_add'=>'2026-04-29','recruitment_channel'=>1],['id'=>2,'rec_campaign'=>1,'candidate_code'=>'CAN-002','candidate_name'=>'Tomasz Krawczyk','birthday'=>'1989-02-03','gender'=>'male','nation'=>'Polska','introduce_yourself'=>'Specjalista wsparcia technicznego i automatyzacji procesów.','phonenumber'=>'+48 530 444 555','email'=>'tomasz@example.com','status'=>2,'rate'=>4,'desired_salary'=>'11000','date_add'=>'2026-04-29','recruitment_channel'=>1]]);
    insertRows($db, $database, $prefix, 'rec_interview', [['id'=>1,'campaign'=>1,'is_name'=>'Rozmowa techniczna - Aleksandra Wójcik','interview_day'=>'2026-05-06','from_time'=>'10:00','to_time'=>'11:00','from_hours'=>'2026-05-06 10:00:00','to_hours'=>'2026-05-06 11:00:00','interviewer'=>'1','added_from'=>1,'added_date'=>'2026-04-29']]);
}

function seedWebsiteMaintenance(mysqli $db, string $database, string $prefix): void
{
    foreach (['projects','wmm_websites','wmm_support_packages','wmm_maintenance_tasks','wmm_maintenance_logs'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['wmm_maintenance_logs','wmm_maintenance_tasks','wmm_support_packages','wmm_websites','projects']);
    insertRows($db, $database, $prefix, 'projects', [['id'=>1,'name'=>'Opieka serwisowa strony klienta','description'=>'Stałe utrzymanie WordPress, aktualizacje i drobny rozwój.','status'=>2,'clientid'=>1,'billing_type'=>2,'start_date'=>'2026-04-29','deadline'=>'2026-12-31','project_created'=>'2026-04-29','progress'=>40,'progress_from_tasks'=>1,'project_rate_per_hour'=>'180.00','estimated_hours'=>'40.00','addedfrom'=>1]]);
    insertRows($db, $database, $prefix, 'wmm_websites', [['id'=>1,'project_id'=>1,'client_id'=>1,'website_url'=>'https://demo-serwis.flowquest.pl','is_active'=>1,'added_by'=>1,'date_added'=>'2026-04-29 14:00:00']]);
    insertRows($db, $database, $prefix, 'wmm_support_packages', [['id'=>1,'client_id'=>1,'website_id'=>1,'package_name'=>'Pakiet Opieka 10h','total_hours'=>'10.00','hours_used'=>'3.50','hours_remaining'=>'6.50','hourly_rate'=>'180.00','package_price'=>'1800.00','low_balance_threshold'=>'2.00','low_balance_notify'=>1,'status'=>'active','start_date'=>'2026-04-29','expiry_date'=>'2026-05-29','notes'=>'Aktualizacje, monitoring i drobne zmiany treści.','created_by'=>1,'created_at'=>'2026-04-29 14:05:00']]);
    insertRows($db, $database, $prefix, 'wmm_maintenance_tasks', [['id'=>1,'name'=>'Aktualizacja WordPress i wtyczek','description'=>'Sprawdzenie kopii, aktualizacja rdzenia i wtyczek, test formularza kontaktowego.','category'=>1,'is_active'=>1,'priority'=>'high','created_by'=>1,'created_at'=>'2026-04-29 14:10:00'],['id'=>2,'name'=>'Optymalizacja szybkości strony','description'=>'Analiza obrazów, cache i podstawowych wskaźników wydajności.','category'=>1,'is_active'=>1,'priority'=>'medium','created_by'=>1,'created_at'=>'2026-04-29 14:11:00']]);
    insertRows($db, $database, $prefix, 'wmm_maintenance_logs', [['id'=>1,'website_id'=>1,'performed_by'=>1,'performed_at'=>'2026-04-29 14:30:00','start_time'=>'2026-04-29 13:00:00','end_time'=>'2026-04-29 14:30:00','time_spent'=>90,'is_completed'=>1,'notes'=>'Zaktualizowano rdzeń, 6 wtyczek i wykonano test formularzy.','hourly_rate'=>'180.00','is_billable'=>1,'package_id'=>1,'deduct_from_package'=>1,'deducted_from_package'=>1]]);
}

function seedWorkshop(mysqli $db, string $database, string $prefix): void
{
    foreach (['wshop_devices','wshop_repair_jobs','wshop_labour_products','wshop_inspections'] as $s) ensureTable($db, $database, $prefix, $s, 'ps_warsztattemplate', 'warsztattemplate_');
    truncateTables($db, $database, $prefix, ['wshop_inspections','wshop_repair_jobs','wshop_labour_products','wshop_devices']);
    insertRows($db, $database, $prefix, 'wshop_devices', [['id'=>1,'name'=>'Toyota Corolla 1.8 Hybrid','code'=>'CAR-001','serial_no'=>'JTDBR32E000000001','client_id'=>1,'purchase_date'=>'2022-06-15','warranty_start_date'=>'2022-06-15','warranty_period_months'=>60,'warranty_expiry_date'=>'2027-06-14','description'=>'Samochód klienta flotowego, regularny serwis co 15 tys. km.','status'=>1,'last_maintenance'=>'2026-01-18','next_maintenance'=>'2026-05-20','datecreated'=>'2026-04-29 15:00:00','staffid'=>1]]);
    insertRows($db, $database, $prefix, 'wshop_labour_products', [['id'=>1,'name'=>'Przegląd okresowy hybrydy','code'=>'LAB-HYB-001','category_id'=>1,'standard_time'=>'2.50','labour_type'=>'fixed','labour_cost'=>'420.00','assign_staff'=>1,'description'=>'Kontrola układu hybrydowego, hamulców, płynów i diagnostyka komputerowa.','status'=>1,'datecreated'=>'2026-04-29 15:05:00','staffid'=>1]]);
    insertRows($db, $database, $prefix, 'wshop_repair_jobs', [['id'=>1,'sent'=>1,'datesend'=>'2026-04-29 15:15:00','job_tracking_number'=>'WRK-2026-0001','name'=>'Przegląd Toyota Corolla Hybrid','number'=>1,'prefix'=>'WRK-','number_format'=>1,'client_id'=>1,'phonenumber'=>'+48 500 900 100','contact_name'=>'Jan Serwisowy','contact_email'=>'jan@example.com','appointment_date'=>'2026-05-07 09:00:00','estimated_completion_date'=>'2026-05-07 14:00:00','device_id'=>1,'sale_agent'=>1,'status'=>'in_progress','reference_no'=>'FLOTA-15K','issue_description'=>'Klient zgłasza komunikat serwisowy po 15 tys. km.','job_description'=>'Przegląd okresowy, diagnostyka i wymiana filtrów.','estimated_hours'=>'2.50','estimated_labour_subtotal'=>'420.00','estimated_labour_total'=>'420.00','estimated_material_subtotal'=>'260.00','estimated_material_total'=>'260.00','currency'=>1,'subtotal'=>'680.00','total_tax'=>'156.40','total'=>'836.40','hash'=>'wrk202604290001','datecreated'=>'2026-04-29 15:15:00','staffid'=>1]]);
    insertRows($db, $database, $prefix, 'wshop_inspections', [['id'=>1,'repair_job_id'=>1,'sent'=>1,'datesend'=>'2026-04-29 15:30:00','number'=>1,'prefix'=>'INSP-','number_format'=>1,'device_id'=>1,'client_id'=>1,'contact_name'=>'Jan Serwisowy','contact_email'=>'jan@example.com','person_in_charge'=>1,'start_date'=>'2026-05-07','end_date'=>'2026-05-07','description'=>'Lista kontrolna przeglądu hybrydy przed wydaniem pojazdu.','status'=>'open','visible_to_customer'=>1,'currency'=>1,'hash'=>'insp202604290001','estimated_labour_subtotal'=>'180.00','estimated_labour_total'=>'180.00','estimated_material_subtotal'=>'0.00','estimated_material_total'=>'0.00','subtotal'=>'180.00','total_tax'=>'41.40','total'=>'221.40','datecreated'=>'2026-04-29 15:30:00','staffid'=>1]]);
}

function seedZillapage(mysqli $db, string $database, string $prefix): void
{
    foreach (['landing_pages','landing_page_form_data','landing_page_settings'] as $s) ensureTable($db, $database, $prefix, $s, 'ps_agencjatemplate', 'agencjatemplate_');
    truncateTables($db, $database, $prefix, ['landing_page_form_data','landing_pages']);
    $html = '<section><h1>Strategia i kampanie B2B</h1><p>Landing page demo agencji z formularzem briefu, ofertą konsultacji i sekcją dowodów społecznych.</p></section>';
    insertRows($db, $database, $prefix, 'landing_pages', [['id'=>1,'code'=>'agency-demo','name'=>'Kampania B2B Demo','html'=>$html,'css'=>'body{font-family:Inter,sans-serif}.hero{padding:64px}','components'=>'[]','styles'=>'[]','thank_you_page_html'=>'<h1>Dziękujemy za brief</h1><p>Oddzwonimy w ciągu jednego dnia roboczego.</p>','thank_you_page_css'=>'','thank_you_page_components'=>'[]','thank_you_page_styles'=>'[]','seo_title'=>'Agencja B2B FlowQuest','seo_description'=>'Landing page demo dla kampanii B2B i pozyskiwania leadów.','seo_keywords'=>'agencja,lead,b2b','social_title'=>'Agencja B2B','social_description'=>'Zobacz demo landing page dla agencji.','type_form_submit'=>'thank_you_page','type_payment_submit'=>'thank_you_page','is_publish'=>1,'is_trash'=>0,'notify_lead_imported'=>1,'notify_type'=>'assigned','responsible'=>1,'created_at'=>'2026-04-29 16:00:00','updated_at'=>'2026-04-29 16:00:00']]);
    insertRows($db, $database, $prefix, 'landing_page_form_data', [['id'=>1,'landing_page_id'=>1,'field_values'=>'{\"name\":\"Paweł Marketing\",\"email\":\"pawel@example.com\",\"budget\":\"25000 PLN\",\"message\":\"Szukamy kampanii lead generation dla SaaS.\"}','browser'=>'Chrome','os'=>'Windows','device'=>'desktop','created_at'=>'2026-04-29 16:10:00','updated_at'=>'2026-04-29 16:10:00']]);
}

function seedAppointly(mysqli $db, string $database, string $prefix, string $industry): void
{
    foreach (['appointly_appointment_types','appointly_appointments','appointly_attendees'] as $s) ensureTable($db, $database, $prefix, $s);
    truncateTables($db, $database, $prefix, ['appointly_attendees','appointly_appointments','appointly_appointment_types']);
    $type = $industry === 'beauty' ? 'Konsultacja kosmetologiczna' : 'Konsultacja lekarska';
    $subject = $industry === 'beauty' ? 'Zabieg pielęgnacyjny - plan skóry' : 'Pierwsza wizyta diagnostyczna';
    insertRows($db, $database, $prefix, 'appointly_appointment_types', [['id'=>1,'type'=>$type,'color'=>'#2f80ed'],['id'=>2,'type'=>'Kontrola po wizycie','color'=>'#27ae60']]);
    insertRows($db, $database, $prefix, 'appointly_appointments', [
        ['id'=>1,'subject'=>$subject,'description'=>'Wizyta pokazowa uzupełniona dla branżowego demo.','email'=>'klient@example.com','name'=>'Monika Klient','phone'=>'+48 511 222 333','address'=>'Warszawa','notes'=>'Klient prosi o przypomnienie dzień wcześniej.','contact_id'=>1,'by_sms'=>1,'by_email'=>1,'hash'=>'appt202604290001','notification_date'=>'2026-05-06 09:00:00','date'=>'2026-05-07','start_hour'=>'10:00','approved'=>1,'created_by'=>1,'reminder_before'=>1,'reminder_before_type'=>'days','finished'=>0,'cancelled'=>0,'source'=>'admin','type_id'=>1,'custom_recurring'=>0],
        ['id'=>2,'subject'=>'Wizyta kontrolna','description'=>'Krótka kontrola efektów i dalszych zaleceń.','email'=>'klient2@example.com','name'=>'Adam Kontrola','phone'=>'+48 522 333 444','address'=>'Kraków','notes'=>'Preferowany kontakt e-mail.','contact_id'=>1,'by_sms'=>0,'by_email'=>1,'hash'=>'appt202604290002','notification_date'=>'2026-05-13 12:00:00','date'=>'2026-05-14','start_hour'=>'13:00','approved'=>1,'created_by'=>1,'reminder_before'=>2,'reminder_before_type'=>'hours','finished'=>0,'cancelled'=>0,'source'=>'admin','type_id'=>2,'custom_recurring'=>0],
    ]);
    insertRows($db, $database, $prefix, 'appointly_attendees', [
        ['staff_id'=>1,'appointment_id'=>1],
        ['staff_id'=>1,'appointment_id'=>2],
    ]);
}

function seedRealestate(mysqli $db, string $database, string $prefix): void
{
    truncateTables($db, $database, $prefix, ['items','items_groups']);
    insertRows($db, $database, $prefix, 'items_groups', [['id'=>1,'name'=>'Oferty nieruchomości','commodity_group_code'=>'RE','order'=>1,'display'=>1,'note'=>'Lokale demo dla modułu nieruchomości.']]);
    insertRows($db, $database, $prefix, 'items', [
        ['id'=>1,'description'=>'Apartament 72 m2 - Mokotów','long_description'=>'Trzypokojowy apartament z balkonem, miejscem garażowym i gotowością do sprzedaży.','rate'=>'1180000.00','unit'=>'lokal','group_id'=>1,'listing_type'=>'sale','transaction_type'=>'Sprzedaż','property_style'=>'apartament','street_name'=>'Cybernetyki','city'=>'Warszawa','country'=>'Polska','beds'=>3,'full_baths'=>1,'sqFt_total'=>'72.00','furnished'=>'częściowo','status'=>'available','owner_name'=>'Anna Właściciel','owner_phone'=>'+48 500 111 000','owner_email'=>'anna.owner@example.com','commodity_code'=>'RE-001','commodity_name'=>'Apartament Mokotów','sku_code'=>'RE-001','sku_name'=>'Apartament 72m2','active'=>1],
        ['id'=>2,'description'=>'Lokal usługowy 140 m2 - Gdańsk','long_description'=>'Lokal na parterze przy ruchliwej ulicy, idealny pod usługi lub showroom.','rate'=>'1650000.00','unit'=>'lokal','group_id'=>1,'listing_type'=>'commercial','transaction_type'=>'Sprzedaż','property_style'=>'lokal usługowy','street_name'=>'Grunwaldzka','city'=>'Gdańsk','country'=>'Polska','sqFt_total'=>'140.00','status'=>'reserved','owner_name'=>'Marek Właściciel','owner_phone'=>'+48 501 222 000','owner_email'=>'marek.owner@example.com','commodity_code'=>'RE-002','commodity_name'=>'Lokal Gdańsk','sku_code'=>'RE-002','sku_name'=>'Lokal 140m2','active'=>1],
    ]);
}

function seedOzeProjects(mysqli $db, string $database, string $prefix): void
{
    truncateTables($db, $database, $prefix, ['tasks','milestones','projects']);
    insertRows($db, $database, $prefix, 'projects', [['id'=>1,'name'=>'Instalacja fotowoltaiczna 9,8 kWp','description'=>'Demo projektu OZE: audyt, oferta, montaż, uruchomienie i odbiór instalacji.','status'=>2,'clientid'=>1,'billing_type'=>3,'start_date'=>'2026-04-29','deadline'=>'2026-06-15','project_created'=>'2026-04-29','progress'=>45,'progress_from_tasks'=>1,'project_cost'=>'38500.00','estimated_hours'=>'64.00','addedfrom'=>1]]);
    insertRows($db, $database, $prefix, 'milestones', [['id'=>1,'name'=>'Audyt i projekt','description'=>'Analiza zużycia energii, dobór komponentów i dokumentacja.','start_date'=>'2026-04-29','due_date'=>'2026-05-10','project_id'=>1,'color'=>'#2f80ed','milestone_order'=>1,'datecreated'=>'2026-04-29'],['id'=>2,'name'=>'Montaż i odbiór','description'=>'Montaż konstrukcji, uruchomienie falownika i protokół odbioru.','start_date'=>'2026-05-20','due_date'=>'2026-06-15','project_id'=>1,'color'=>'#27ae60','milestone_order'=>2,'datecreated'=>'2026-04-29']]);
    insertRows($db, $database, $prefix, 'tasks', [
        ['id'=>1,'name'=>'Audyt energetyczny budynku','description'=>'Zebrać rachunki, profil zużycia i warunki techniczne przyłącza.','priority'=>2,'dateadded'=>'2026-04-29 17:00:00','startdate'=>'2026-04-29','duedate'=>'2026-05-03','addedfrom'=>1,'status'=>5,'rel_id'=>1,'rel_type'=>'project','is_public'=>1,'billable'=>1,'hourly_rate'=>'180.00','milestone'=>1,'kanban_order'=>1,'visible_to_client'=>1],
        ['id'=>2,'name'=>'Dobór komponentów i oferta','description'=>'Przygotować wariant paneli, falownika, magazynu energii i kalkulację ROI.','priority'=>3,'dateadded'=>'2026-04-29 17:05:00','startdate'=>'2026-05-04','duedate'=>'2026-05-10','addedfrom'=>1,'status'=>4,'rel_id'=>1,'rel_type'=>'project','is_public'=>1,'billable'=>1,'hourly_rate'=>'180.00','milestone'=>1,'kanban_order'=>2,'visible_to_client'=>1],
        ['id'=>3,'name'=>'Montaż konstrukcji i paneli','description'=>'Zaplanować ekipę, dostawę komponentów i odbiór prac na dachu.','priority'=>3,'dateadded'=>'2026-04-29 17:10:00','startdate'=>'2026-05-20','duedate'=>'2026-05-28','addedfrom'=>1,'status'=>1,'rel_id'=>1,'rel_type'=>'project','is_public'=>1,'billable'=>1,'hourly_rate'=>'180.00','milestone'=>2,'kanban_order'=>3,'visible_to_client'=>1],
    ]);
}

function updateMarketplace(mysqli $db, array $names, array $descriptions): void
{
    foreach (['`perfex_db`.`tbloptions`', '`ps_oze`.`oze_tbloptions`'] as $table) {
        $row = $db->query("SELECT `value` FROM $table WHERE `name`='fq_saas_custom_modules_name' LIMIT 1")->fetch_assoc();
        $existing = $row ? json_decode((string)$row['value'], true) : [];
        $existing = is_array($existing) ? $existing : [];
        upsertOptionTable($db, $table, 'fq_saas_custom_modules_name', json_encode(array_replace($existing, $names), JSON_UNESCAPED_UNICODE));

        $row = $db->query("SELECT `value` FROM $table WHERE `name`='fq_saas_modules_marketplace' LIMIT 1")->fetch_assoc();
        $existing = $row ? json_decode((string)$row['value'], true) : [];
        $existing = is_array($existing) ? $existing : [];
        foreach ($descriptions as $module => $description) {
            $existing[$module] = array_replace($existing[$module] ?? [], ['description' => $description]);
        }
        upsertOptionTable($db, $table, 'fq_saas_modules_marketplace', json_encode($existing, JSON_UNESCAPED_UNICODE));
    }
}

$report = [];
updateMarketplace($mysqli, $moduleNames, $marketplace);
upsertOptionTable($mysqli, '`perfex_db`.`tbloptions`', 'company_logo_dark', 'flowquest_logo_global.png');
$report[] = 'Zaktualizowano nazwy i opisy marketplace.';

foreach ($tenants as $slug => $cfg) {
    $database = "ps_$slug";
    $prefix = $slug;
    $logoName = "fq_{$slug}_logo.png";
    $sourceLogo = $root . '/demo-logos/' . $cfg['logo'];
    $companyLogo = $root . '/uploads/company/' . $logoName;
    $tenantLogoDir = $root . "/uploads/tenants/$slug/company";
    if (!is_dir($tenantLogoDir)) {
        mkdir($tenantLogoDir, 0775, true);
    }
    if (is_file($sourceLogo)) {
        copy($sourceLogo, $companyLogo);
        copy($sourceLogo, "$tenantLogoDir/$logoName");
    }

    upsertOption($mysqli, $database, $prefix, 'company_logo', $logoName);
    upsertOption($mysqli, $database, $prefix, 'company_logo_dark', $logoName);
    ensureModule($mysqli, $database, $prefix, $cfg['module']);
    seedBasics($mysqli, $database, $prefix, 'FlowQuest Demo ' . ucfirst($slug));

    match ($slug) {
        'agencja' => seedZillapage($mysqli, $database, $prefix),
        'beauty' => seedAppointly($mysqli, $database, $prefix, 'beauty'),
        'ecommerce' => seedProducts($mysqli, $database, $prefix),
        'eventy' => seedFlexstage($mysqli, $database, $prefix),
        'gastronomia' => seedCatering($mysqli, $database, $prefix),
        'hotel' => seedHotel($mysqli, $database, $prefix),
        'kursy' => seedFlexacademy($mysqli, $database, $prefix),
        'logistyka' => seedLogistic($mysqli, $database, $prefix),
        'medycyna' => seedAppointly($mysqli, $database, $prefix, 'medycyna'),
        'nieruchomosci' => seedRealestate($mysqli, $database, $prefix),
        'oze' => seedOzeProjects($mysqli, $database, $prefix),
        'rekrutacja' => seedRecruitment($mysqli, $database, $prefix),
        'serwiswww' => seedWebsiteMaintenance($mysqli, $database, $prefix),
        'warsztat' => seedWorkshop($mysqli, $database, $prefix),
    };

    $report[] = "$slug: logo=$logoName, moduł={$cfg['module']}";
}

$reportPath = $root . '/modules/reports/demo_marketplace_finish_' . date('Ymd_His') . '.md';
file_put_contents($reportPath, "# Demo marketplace finish\n\n- " . implode("\n- ", $report) . "\n");
copy($reportPath, $root . '/modules/FQ_DEMO_MARKETPLACE_FINISH_LAST.md');
echo $reportPath . PHP_EOL;
