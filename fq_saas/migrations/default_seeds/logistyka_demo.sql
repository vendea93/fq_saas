-- FlowQuest Demo Data for Logistics Industry
-- Created: 2026-04-27
-- Safe IDs start from 2000 to avoid conflicts

-- ============================================
-- 1. DEMO CLIENTS (Klienci firmy transportowej)
-- ============================================

INSERT INTO `tblclients` (`userid`, `company`, `phonenumber`, `country`, `city`, `address`, `datecreated`, `active`, `addedfrom`) VALUES
(2000, 'EuroBuild Sp. z o.o.', '+48 601 234 567', 176, 'Warszawa', 'ul. Budowlana 15', '2026-03-15 09:00:00', 1, 1),
(2001, 'TechPol Corporation', '+48 602 345 678', 176, 'Kraków', 'ul. Techniczna 22', '2026-03-20 10:30:00', 1, 1),
(2002, 'AgroFarm S.A.', '+48 603 456 789', 176, 'Poznań', 'ul. Rolna 45', '2026-03-25 11:45:00', 1, 1),
(2003, 'MediPharm Sp. z o.o.', '+48 604 567 890', 176, 'Wrocław', 'ul. Farmaceutyczna 8', '2026-04-01 14:20:00', 1, 1),
(2004, 'AutoParts Distribution', '+48 605 678 901', 176, 'Łódź', 'ul. Motoryzacyjna 33', '2026-04-05 16:10:00', 1, 1);

-- Kontakty dla klientów
INSERT INTO `tblcontacts` (`id`, `userid`, `firstname`, `lastname`, `email`, `phonenumber`, `datecreated`, `password`) VALUES
(2000, 2000, 'Tomasz', 'Nowak', 'tomasz.nowak@eurobuild.pl', '+48 601 234 567', '2026-03-15 09:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2001, 2001, 'Anna', 'Kowalska', 'anna.kowalska@techpol.pl', '+48 602 345 678', '2026-03-20 10:30:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2002, 2002, 'Jan', 'Wiśniewski', 'jan.wisniewski@agrofarm.pl', '+48 603 456 789', '2026-03-25 11:45:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2003, 2003, 'Magdalena', 'Dąbrowska', 'magdalena.dabrowska@medipharm.pl', '+48 604 567 890', '2026-04-01 14:20:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2004, 2004, 'Piotr', 'Lewandowski', 'piotr.lewandowski@autoparts.pl', '+48 605 678 901', '2026-04-05 16:10:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- 2. INVOICES (Faktury za transport)
-- ============================================

INSERT INTO `tblinvoices` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `duedate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(2000, 1, '2026-04-05 12:30:00', 2000, 'FV/TRANS/2026/001', '2026-04-05', '2026-05-05', 2, 8500.00, 10455.00, 1),
(2001, 1, '2026-04-10 09:15:00', 2001, 'FV/TRANS/2026/002', '2026-04-10', '2026-05-10', 2, 4200.00, 5166.00, 1),
(2002, 1, '2026-04-15 14:45:00', 2002, 'FV/TRANS/2026/003', '2026-04-15', '2026-05-15', 2, 12500.00, 15375.00, 1),
(2003, 0, NULL, 2003, 'FV/TRANS/2026/004', '2026-04-20', '2026-05-20', 1, 6800.00, 8364.00, 1);

-- Pozycje na fakturach
INSERT INTO `tblitems_in` (`id`, `rel_id`, `rel_type`, `description`, `long_description`, `qty`, `rate`, `unit`) VALUES
(2000, 2000, 'invoice', 'Transport maszyn budowlanych', 'Transport 3 maszyn budowlanych z Warszawy do Krakowa', 1, 6500.00, 'transport'),
(2001, 2000, 'invoice', 'Ubezpieczenie ładunku', 'Ubezpieczenie cargo do 500 000 PLN', 1, 2000.00, 'ubezpieczenie'),
(2002, 2001, 'invoice', 'Transport elektroniki', 'Transport sprzętu IT z Krakowa do Gdańska', 1, 3200.00, 'transport'),
(2003, 2002, 'invoice', 'Transport zbóż', 'Transport 20 ton pszenicy z Poznania do Szczecina', 1, 8500.00, 'transport'),
(2004, 2002, 'invoice', 'Przeładunek portowy', 'Usługi przeładunkowe w porcie', 1, 4000.00, 'przeładunek');

-- ============================================
-- 3. CONTRACTS (Umowy logistyczne)
-- ============================================

INSERT INTO `tblcontracts` (`id`, `content`, `description`, `subject`, `client`, `datestart`, `dateend`, `contract_value`, `trash`, `addedfrom`) VALUES
(2000, '<h2>Umowa na stałą obsługę logistyczną</h2><p>TransLog Sp. z o.o. świadczy usługi transportowe dla EuroBuild Sp. z o.o. na następujących warunkach:</p><ul><li>Stała stawka za transport ciężarowy: 8 PLN/km</li><li>Gwarantowany termin dostawy w ciągu 24h</li><li>Miesięczne rozliczenia</li><li>SLA: 99% terminowości</li></ul>', 'Umowa na stałą obsługę transportową dla EuroBuild', 'Umowa logistyczna EuroBuild', 2000, '2026-01-01', '2026-12-31', 150000.00, 0, 1);

-- ============================================
-- 4. ESTIMATES (Wyceny transportowe)
-- ============================================

INSERT INTO `tblestimates` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `expirydate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(2000, 1, '2026-04-03 11:20:00', 2004, 'WYC/TRANS/2026/001', '2026-04-03', '2026-05-03', 1, 9500.00, 11685.00, 1),
(2001, 1, '2026-04-08 15:30:00', 2003, 'WYC/TRANS/2026/002', '2026-04-08', '2026-05-08', 1, 7200.00, 8856.00, 1),
(2002, 0, NULL, 2001, 'WYC/TRANS/2026/003', '2026-04-25', '2026-05-25', 1, 15500.00, 19065.00, 1);

-- ============================================
-- 5. PROJECTS (Projekty logistyczne)
-- ============================================

INSERT INTO `tblprojects` (`id`, `name`, `description`, `status`, `start_date`, `deadline`, `addedfrom`) VALUES
(2000, 'Optymalizacja tras floty', 'Analiza i optymalizacja tras przewozowych floty 15 pojazdów. Celem jest redukcja kosztów paliwa o 15%.', 2, '2026-04-01', '2026-05-30', 1),
(2001, 'Wdrożenie systemu śledzenia GPS', 'Implementacja systemu śledzenia pojazdów w czasie rzeczywistym z raportami dla klientów.', 3, '2026-04-10', '2026-06-15', 1);

-- Dyskusje w projekcie
INSERT INTO `tblprojectdiscussions` (`id`, `project_id`, `subject`, `description`, `staff_id`, `datecreated`) VALUES
(2000, 2000, 'Analiza obecnych tras', 'Przeanalizowałem trasy z ostatnich 3 miesięcy. Największe oszczędności można osiągnąć na trasie Warszawa-Kraków (obecnie 305 km, optymalna: 290 km).', 2, '2026-04-05 10:15:00'),
(2001, 2000, 'Propozycja zmian', 'Dobrze! Dodatkowo proponuję wprowadzić system "ride sharing" dla częściowych ładunków. Możemy zaoszczędzić nawet 20% na niektórych trasach.', 1, '2026-04-05 14:30:00'),
(2002, 2001, 'Wybór dostawcy GPS', 'Mamy 3 oferty systemów GPS: TrackerPro (15 000 zł), FleetMaster (18 000 zł), LogiTrack (22 000 zł). Rekomenduję FleetMaster - najlepszy stosunek ceny do jakości.', 2, '2026-04-12 09:45:00');

-- ============================================
-- 6. TASKS (Zadania dyspozytorskie)
-- ============================================

INSERT INTO `tblstafftasks` (`id`, `name`, `description`, `priority`, `dateadded`, `startdate`, `duedate`, `datefinished`, `addedfrom`, `status`, `rel_id`, `rel_type`) VALUES
(2000, 'Monitorowanie transportu do EuroBuild', 'Śledzenie transportu maszyn budowlanych do Krakowa. Kontakt z kierowcą co 2 godziny.', 1, '2026-04-10 08:00:00', '2026-04-10', '2026-04-10', '2026-04-10 18:00:00', 2, 5, NULL, NULL),
(2001, 'Planowanie tras na następny tydzień', 'Zaplanowanie tras dla 8 pojazdów na okres 15-22 kwietnia.', 2, '2026-04-12 09:00:00', '2026-04-12', '2026-04-14', NULL, 2, 2, 2000, 'project'),
(2002, 'Kontakt z klientem TechPol', 'Potwierdzenie terminu odbioru elektroniki w Krakowie.', 3, '2026-04-18 10:30:00', '2026-04-18', '2026-04-18', '2026-04-18 11:15:00', 2, 5, NULL, NULL),
(2003, 'Aktualizacja dokumentów floty', 'Aktualizacja dokumentów rejestracyjnych i ubezpieczeń dla 3 pojazdów.', 2, '2026-04-20 13:00:00', '2026-04-20', '2026-04-22', NULL, 2, 1, NULL, NULL);

-- ============================================
-- 7. LEADS (Nowe zapytania transportowe)
-- ============================================

INSERT INTO `tblleads` (`id`, `name`, `title`, `company`, `description`, `phonenumber`, `email`, `dateadded`, `status`, `source`, `assigned`) VALUES
(2000, 'FoodDistrib Sp. z o.o.', 'Dystrybucja żywności', 'FoodDistrib Sp. z o.o.', 'Zapytanie o stałą obsługę transportową dla 5 sklepów w regionie mazowieckim', '+48 701 234 567', 'biuro@fooddistrib.pl', '2026-04-22 11:30:00', 1, 2, 2),
(2001, 'Robert Mazur', 'Przewóz mebli', NULL, 'Potrzebuję transportu mebli z Warszawy do Wrocławia (2 transporty miesięcznie)', '+48 702 345 678', 'robert.mazur@email.pl', '2026-04-23 14:45:00', 2, 1, 2),
(2001, 'ChemiCorp S.A.', 'Przemysł chemiczny', 'ChemiCorp S.A.', 'Transport specjalistyczny produktów chemicznych (ADR) z Wrocławia do Gdańska', '+48 703 456 789', 'logistyka@chemicorp.pl', '2026-04-24 10:20:00', 1, 3, 2);

-- ============================================
-- 8. ITEMS (Usługi transportowe)
-- ============================================

INSERT INTO `tblitems` (`id`, `description`, `long_description`, `rate`, `tax`, `tax2`, `unit`, `group_id`) VALUES
(2000, 'Transport ciężarowy do 3,5t', 'Transport samochodem ciężarowym do 3,5 tony, do 500 km', 8.50, 1, 0, 'km', 1),
(2001, 'Transport ciężarowy do 10t', 'Transport samochodem ciężarowym do 10 ton, do 500 km', 10.00, 1, 0, 'km', 1),
(2002, 'Transport specjalny ADR', 'Transport materiałów niebezpiecznych z certyfikatem ADR', 15.00, 1, 0, 'km', 2),
(2003, 'Przeładunek portowy', 'Usługi przeładunkowe w porcie morskim', 1200.00, 1, 0, 'usługa', 3),
(2004, 'Ubezpieczenie cargo', 'Ubezpieczenie ładunku do 500 000 PLN', 2000.00, 1, 0, 'polisa', 4);

-- ============================================
-- 9. EXPENSES (Wydatki operacyjne)
-- ============================================

INSERT INTO `tblexpenses` (`id`, `category`, `amount`, `category`, `date`, `addedfrom`) VALUES
-- ============================================

INSERT INTO `tblexpenses` (`id`, `category`, `amount`, `date`, `addedfrom`, `clientid`, `project_id`, `description`) VALUES
(2000, 3, 8500.00, '2026-04-05', 1, NULL, NULL, 'Paliwo dla floty - kwiecień'),
(2001, 4, 3200.00, '2026-04-10', 1, NULL, NULL, 'Opłaty autostradowe'),
(2002, 5, 1500.00, '2026-04-15', 1, NULL, NULL, 'Myjnia pojazdów'),
(2003, 6, 45000.00, '2026-04-20', 1, NULL, NULL, 'Wynagrodzenia kierowców');

-- ============================================
-- 10. NOTES (Notatki logistyczne)
-- ============================================

INSERT INTO `tblnotes` (`id`, `rel_id`, `rel_type`, `description`, `date_contacted`, `addedfrom`, `dateadded`) VALUES
(2000, 2000, 'customer', 'Klient wymaga dokumentów transportowych w 2 egzemplarzach. Zawsze potwierdzać odbiór podpisem.', '2026-04-08 11:30:00', 1, '2026-04-08 12:00:00'),
(2001, 2001, 'customer', 'Kontaktować się tylko przez Annę Kowalską. Preferują komunikację mailową.', '2026-04-12 14:15:00', 2, '2026-04-12 14:30:00'),
(2002, 2003, 'customer', 'Ładunki wymagają kontroli temperatury (2-8°C). Sprawdzać przed każdym transportem.', '2026-04-18 09:45:00', 1, '2026-04-18 09:30:00', 2, '2026-04-18 10:00:00');

-- ============================================
-- END OF LOGISTICS DEMO DATA
-- ============================================