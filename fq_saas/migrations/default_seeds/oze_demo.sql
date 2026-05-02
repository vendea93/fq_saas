-- FlowQuest Demo Data for OZE (Renewable Energy) Industry
-- Created: 2026-04-27
-- Safe IDs start from 3000 to avoid conflicts

-- ============================================
-- 1. DEMO CLIENTS (Klienci instalacji OZE)
-- ============================================

INSERT INTO `tblclients` (`userid`, `company`, `phonenumber`, `country`, `city`, `address`, `datecreated`, `active`, `addedfrom`) VALUES
(3000, 'Rodzina Nowaków - Prywatny', '+48 501 234 567', 176, 'Warszawa', 'ul. Słoneczna 15', '2026-03-10 10:00:00', 1, 1),
(3001, 'EcoBusiness Sp. z o.o.', '+48 502 345 678', 176, 'Kraków', 'ul. Ekologiczna 22', '2026-03-15 11:30:00', 1, 1),
(3002, 'Szkoła Podstawowa nr 5', '+48 503 456 789', 176, 'Poznań', 'ul. Szkolna 45', '2026-03-20 09:45:00', 1, 1),
(3003, 'Hotel Solaris', '+48 504 567 890', 176, 'Gdańsk', 'ul. Nadmorska 8', '2026-03-25 14:20:00', 1, 1),
(3004, 'Farma Wiatrowa "EkoPower"', '+48 505 678 901', 176, 'Szczecin', 'ul. Wiatraczna 33', '2026-04-01 16:10:00', 1, 1);

-- Kontakty dla klientów
INSERT INTO `tblcontacts` (`id`, `userid`, `firstname`, `lastname`, `email`, `phonenumber`, `datecreated`, `password`) VALUES
(3000, 3000, 'Jan', 'Nowak', 'jan.nowak@email.pl', '+48 501 234 567', '2026-03-10 10:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3001, 3001, 'Anna', 'Kowalska', 'anna.kowalska@ecobusiness.pl', '+48 502 345 678', '2026-03-15 11:30:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3002, 3002, 'Tomasz', 'Wiśniewski', 'tomasz.wisniewski@sp5.poznan.pl', '+48 503 456 789', '2026-03-20 09:45:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3003, 3003, 'Magdalena', 'Dąbrowska', 'magdalena.dabrowska@hotelsolaris.pl', '+48 504 567 890', '2026-03-25 14:20:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3004, 3004, 'Piotr', 'Lewandowski', 'piotr.lewandowski@ekopower.pl', '+48 505 678 901', '2026-04-01 16:10:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- 2. INVOICES (Faktury za instalacje OZE)
-- ============================================

INSERT INTO `tblinvoices` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `duedate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(3000, 1, '2026-04-05 12:30:00', 3000, 'FV/OZE/2026/001', '2026-04-05', '2026-05-05', 2, 28500.00, 35055.00, 1),
(3001, 1, '2026-04-12 09:15:00', 3001, 'FV/OZE/2026/002', '2026-04-12', '2026-05-12', 2, 125000.00, 153750.00, 1),
(3002, 1, '2026-04-18 14:45:00', 3003, 'FV/OZE/2026/003', '2026-04-18', '2026-05-18', 2, 85000.00, 104550.00, 1),
(3003, 0, NULL, 3002, 'FV/OZE/2026/004', '2026-04-25', '2026-05-25', 1, 95000.00, 116850.00, 1);

-- Pozycje na fakturach
INSERT INTO `tblitems_in` (`id`, `rel_id`, `rel_type`, `description`, `long_description`, `qty`, `rate`, `unit`) VALUES
(3000, 3000, 'invoice', 'Panele fotowoltaiczne 8 kW', '24 panele Jinko Solar 335W + inwerter Huawei', 1, 18000.00, 'system'),
(3001, 3000, 'invoice', 'Konstrukcja montażowa', 'Konstrukcja aluminiowa + okablowanie', 1, 4500.00, 'komplet'),
(3002, 3000, 'invoice', 'Montaż i uruchomienie', 'Montaż przez certyfikowany zespół + szkolenie', 1, 6000.00, 'usługa'),
(3003, 3001, 'invoice', 'Instalacja przemysłowa 50 kW', 'System fotowoltaiczny dla firmy EcoBusiness', 1, 95000.00, 'system'),
(3004, 3001, 'invoice', 'Optymalizatory mocy', '30 optymalizatorów Tigo TS4-A', 30, 1000.00, 'szt');

-- ============================================
-- 3. CONTRACTS (Umowy serwisowe OZE)
-- ============================================

INSERT INTO `tblcontracts` (`id`, `content`, `description`, `subject`, `client`, `datestart`, `dateend`, `contract_value`, `trash`, `addedfrom`) VALUES
(3000, '<h2>Umowa serwisowa instalacji PV</h2><p>EkoEnergia Sp. z o.o. świadczy usługi serwisowe dla instalacji fotowoltaicznej klienta na następujących warunkach:</p><ul><li>Przeglądy półroczne (2x w roku)</li><li>Monitoring online wydajności systemu</li><li>Gwarancja reakcji w ciągu 48h</li><li>Naprawy gwarancyjne w cenie</li></ul>', 'Umowa serwisowa na 10 lat dla instalacji PV 8 kW', 'Umowa serwisowa - Rodzina Nowaków', 3000, '2026-04-01', '2036-04-01', 12000.00, 0, 1);

-- ============================================
-- 4. ESTIMATES (Wyceny instalacji)
-- ============================================

INSERT INTO `tblestimates` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `expirydate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(3000, 1, '2026-03-28 11:20:00', 3002, 'WYC/OZE/2026/001', '2026-03-28', '2026-04-28', 1, 95000.00, 116850.00, 1),
(3001, 1, '2026-04-03 15:30:00', 3004, 'WYC/OZE/2026/002', '2026-04-03', '2026-05-03', 1, 250000.00, 307500.00, 1),
(3002, 0, NULL, 3003, 'WYC/OZE/2026/003', '2026-04-20', '2026-05-20', 1, 120000.00, 147600.00, 1);

-- ============================================
-- 5. PROJECTS (Projekty instalacyjne OZE)
-- ============================================

INSERT INTO `tblprojects` (`id`, `name`, `description`, `status`, `start_date`, `deadline`, `addedfrom`) VALUES
(3000, 'Instalacja PV dla szkoły podstawowej', 'Kompleksowa instalacja fotowoltaiczna 40 kW dla Szkoły Podstawowej nr 5 w Poznaniu. Projekt z dofinansowaniem z programu "Czyste Powietrze".', 2, '2026-04-15', '2026-06-30', 1),
(3001, 'Audyt energetyczny hotelu Solaris', 'Kompleksowy audyt energetyczny z rekomendacjami modernizacji OZE dla 4-gwiazdkowego hotelu.', 3, '2026-04-20', '2026-05-31', 1);

-- Dyskusje w projekcie
INSERT INTO `tblprojectdiscussions` (`id`, `project_id`, `subject`, `description`, `staff_id`, `datecreated`) VALUES
(3000, 3000, 'Analiza zużycia energii szkoły', 'Przeanalizowałem rachunki za energię z ostatnich 12 miesięcy. Średnie miesięczne zużycie: 4500 kWh. Proponuję instalację 40 kW.', 2, '2026-04-03 10:15:00'),
(3001, 3000, 'Wybór komponentów', 'Rekomenduję panele Longi 450W (90 sztuk) + 3 inwertery Huawei 15 kW. Koszt: około 85 000 zł netto.', 2, '2026-04-04 14:30:00'),
(3002, 3000, 'Harmonogram montażu', 'Montaż możemy zaplanować na wakacje letnie (lipiec) żeby nie zakłócać pracy szkoły. Potrzebujemy 10 dni roboczych.', 1, '2026-04-05 09:45:00'),
(3003, 3001, 'Wstępne ustalenia z hotelem', 'Hotel zużywa rocznie 280 000 kWh. Głównie ogrzewanie i klimatyzacja. Proponuję instalację PV + pompy ciepła.', 2, '2026-04-22 11:20:00');

-- ============================================
-- 6. TASKS (Zadania instalacyjne)
-- ============================================

INSERT INTO `tblstafftasks` (`id`, `name`, `description`, `priority`, `dateadded`, `startdate`, `duedate`, `datefinished`, `addedfrom`, `status`, `rel_id`, `rel_type`) VALUES
(3000, 'Przygotowanie dokumentacji dofinansowania', 'Przygotowanie wniosku o dofinansowanie z programu "Czyste Powietrze" dla szkoły.', 1, '2026-04-10 08:00:00', '2026-04-10', '2026-04-17', '2026-04-16 15:30:00', 2, 5, 3000, 'project'),
(3001, 'Wizja lokalna u klienta Nowaków', 'Wizja lokalna i pomiary dachu przed instalacją PV.', 2, '2026-04-12 09:00:00', '2026-04-12', '2026-04-12', '2026-04-12 12:45:00', 2, 5, NULL, NULL),
(3002, 'Zamówienie paneli dla EcoBusiness', 'Zamówienie i logistyka 150 paneli dla instalacji 50 kW.', 1, '2026-04-18 10:30:00', '2026-04-18', '2026-04-25', NULL, 2, 2, NULL, NULL),
(3003, 'Serwis okresowy instalacji', 'Przegląd półroczny instalacji u klienta Nowaków + czyszczenie paneli.', 3, '2026-04-22 13:00:00', '2026-04-25', '2026-04-25', NULL, 2, 1, NULL, NULL);

-- ============================================
-- 7. LEADS (Nowe zapytania o OZE)
-- ============================================

INSERT INTO `tblleads` (`id`, `name`, `title`, `company`, `description`, `phonenumber`, `email`, `dateadded`, `status`, `source`, `assigned`) VALUES
(3000, 'Spółdzielnia Mieszkaniowa "Złota Jesień"', 'Wspólnota mieszkaniowa', 'SM "Złota Jesień"', 'Zapytanie o instalację PV na 3 budynkach mieszkalnych (łącznie 60 kW)', '+48 801 234 567', 'zarzad@zlotajesien.pl', '2026-04-20 11:30:00', 1, 2, 2),
(3001, 'Jan Kowalski', 'Właściciel domu', NULL, 'Interesuje się instalacją PV 10 kW z magazynem energii', '+48 802 345 678', 'jan.kowalski@email.pl', '2026-04-21 14:45:00', 2, 1, 2),
(3002, 'Farma "EkoUprawy"', 'Gospodarstwo rolne', 'Farma "EkoUprawy"', 'Zapytanie o instalację PV dla suszarni zbóż (30 kW) z opcją dotacji rolnej', '+48 803 456 789', 'biuro@ekouprawy.pl', '2026-04-22 10:20:00', 1, 3, 2);

-- ============================================
-- 8. ITEMS (Produkty i usługi OZE)
-- ============================================

INSERT INTO `tblitems` (`id`, `description`, `long_description`, `rate`, `tax`, `tax2`, `unit`, `group_id`) VALUES
(3000, 'Panel fotowoltaiczny 450W', 'Panel monokrystaliczny Longi 450W, 25 lat gwarancji', 450.00, 1, 0, 'szt', 1),
(3001, 'Inwerter 10 kW', 'Inwerter Huawei 10 kW, 10 lat gwarancji', 6500.00, 1, 0, 'szt', 2),
(3002, 'Konstrukcja montażowa', 'System montażowy aluminiowy dla dachów skośnych', 1800.00, 1, 0, 'komplet', 3),
(3003, 'Montaż instalacji PV', 'Montaż przez certyfikowany zespół + uruchomienie', 120.00, 1, 0, 'm2', 4),
(3004, 'Serwis roczny', 'Przegląd roczny + czyszczenie paneli + raport', 800.00, 1, 0, 'usługa', 5);

-- ============================================
-- 9. PROPOSALS (Oferty kompleksowe)
-- ============================================

INSERT INTO `tblproposals` (`id`, `subject`, `content`, `addedfrom`, `datecreated`, `total`, `subtotal`, `status`, `date`, `open_till`, `currency`, `project_id`, `discount_percent`, `discount_total`) VALUES
(3000, 'Kompleksowa instalacja PV 8 kW', '<h2>Oferta instalacji fotowoltaicznej 8 kW</h2><p>Kompleksowa instalacja dla domu jednorodzinnego z gwarancją i monitoringiem.</p><ul><li>24 panele 335W</li><li>Inwerter Huawei</li><li>Montaż w 3 dni</li><li>10 lat serwisu w cenie</li></ul>', 1, '2026-03-25 14:30:00', 35055.00, 28500.00, 4, '2026-03-25', '2026-04-25', 1, NULL, 0, 0);

-- ============================================
-- 10. NOTES (Notatki techniczne)
-- ============================================

INSERT INTO `tblnotes` (`id`, `rel_id`, `rel_type`, `description`, `date_contacted`, `addedfrom`, `dateadded`) VALUES
(3000, 3000, 'customer', 'Dach skierowany na południe, nachylenie 30°. Brak zacienień. Idealne warunki do instalacji PV.', '2026-04-12 10:30:00', 2, '2026-04-12 11:00:00'),
(3001, 3002, 'customer', 'Szkoła ma możliwość uzyskania 70% dofinansowania z programu "Czyste Powietrze". Termin składania wniosków: 30 kwietnia.', '2026-04-15 14:15:00', 1, '2026-04-15 14:30:00'),
(3002, 3001, 'customer', 'Klient zainteresowany magazynem energii w przyszłości. Zostawić miejsce w rozdzielni na dodatkowe komponenty.', '2026-04-18 09:45:00', 2, '2026-04-18 10:00:00');

-- ============================================
-- 11. TICKETS (Zgłoszenia serwisowe)
-- ============================================

INSERT INTO `tbltickets` (`ticketid`, `adminreplying`, `userid`, `contactid`, `email`, `name`, `department`, `priority`, `status`, `service`, `ticketkey`, `subject`, `message`, `admin`, `date`) VALUES
(3000, 0, 3000, 3000, 'jan.nowak@email.pl', 'Jan Nowak', 1, 1, 2, NULL, 'TICKET-0001', 'Spadek wydajności instalacji', 'Od tygodnia zauważam spadek produkcji o około 15%. System pokazuje brak błędów.', 2, '2026-04-20 15:30:00'),
(3001, 1, 3003, 3003, 'magdalena.dabrowska@hotelsolaris.pl', 'Magdalena Dąbrowska', 1, 2, 1, NULL, 'TICKET-0002', 'Zapytanie o rozszerzenie instalacji', 'Chcielibyśmy rozszerzyć naszą instalację o dodatkowe 20 kW. Proszę o kontakt.', 2, '2026-04-22 11:20:00');

-- ============================================
-- END OF OZE DEMO DATA
-- ============================================