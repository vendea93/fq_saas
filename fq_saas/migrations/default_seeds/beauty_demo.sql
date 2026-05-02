-- FlowQuest Demo Data for Beauty Industry
-- Created: 2026-04-27
-- Safe IDs start from 1000 to avoid conflicts

-- ============================================
-- 1. DEMO CLIENTS (Klienci salonu beauty)
-- ============================================

-- Salon "Perła Urody" - główny klient demo
INSERT INTO `tblclients` (`userid`, `company`, `phonenumber`, `country`, `city`, `address`, `datecreated`, `active`, `addedfrom`) VALUES
(1000, 'Perła Urody', '+48 123 456 789', 176, 'Warszawa', 'ul. Koszykowa 15', '2026-04-01 10:00:00', 1, 1),
(1001, 'OfficeStyle Sp. z o.o.', '+48 987 654 321', 176, 'Kraków', 'ul. Floriańska 22', '2026-04-02 11:00:00', 1, 1),
(1002, 'Marta Wiśniewska - Prywatna', '+48 555 123 456', 176, 'Warszawa', 'ul. Marszałkowska 45', '2026-04-03 09:30:00', 1, 1),
(1003, 'Klub Fitness Active', '+48 777 888 999', 176, 'Łódź', 'ul. Piotrkowska 123', '2026-04-04 14:20:00', 1, 1),
(1004, 'Hotel Grand Victoria', '+48 222 333 444', 176, 'Gdańsk', 'ul. Długa 67', '2026-04-05 16:45:00', 1, 1);

-- Kontakty dla klientów
INSERT INTO `tblcontacts` (`id`, `userid`, `firstname`, `lastname`, `email`, `phonenumber`, `datecreated`, `password`) VALUES
(1000, 1000, 'Anna', 'Nowak', 'anna.nowak@perla-urody.pl', '+48 123 456 789', '2026-04-01 10:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- hasło: password
(1001, 1001, 'Marta', 'Wiśniewska', 'marta@officestyle.pl', '+48 987 654 321', '2026-04-02 11:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(1002, 1002, 'Marta', 'Wiśniewska', 'marta.wisniewska@email.pl', '+48 555 123 456', '2026-04-03 09:30:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(1003, 1003, 'Katarzyna', 'Kowalska', 'kasia@activefitness.pl', '+48 777 888 999', '2026-04-04 14:20:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(1004, 1004, 'Joanna', 'Dąbrowska', 'joanna.dabrowska@grandvictoria.pl', '+48 222 333 444', '2026-04-05 16:45:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- 2. INVOICES (Faktury za usługi beauty)
-- ============================================

INSERT INTO `tblinvoices` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `duedate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(1000, 1, '2026-04-10 14:30:00', 1001, 'FV/BEA/2026/001', '2026-04-10', '2026-05-10', 2, 850.00, 1043.50, 1),
(1001, 1, '2026-04-15 09:15:00', 1000, 'FV/BEA/2026/002', '2026-04-15', '2026-05-15', 2, 1200.00, 1476.00, 1),
(1002, 1, '2026-04-18 11:45:00', 1002, 'FV/BEA/2026/003', '2026-04-18', '2026-05-18', 2, 320.00, 393.60, 1),
(1003, 0, NULL, 1003, 'FV/BEA/2026/004', '2026-04-20', '2026-05-20', 1, 1800.00, 2214.00, 1);

-- Pozycje na fakturach
INSERT INTO `tblitems_in` (`id`, `rel_id`, `rel_type`, `description`, `long_description`, `qty`, `rate`, `unit`) VALUES
(1000, 1000, 'invoice', 'Manicure hybrydowy', 'Manicure hybrydowy z opcją french', 1, 120.00, 'usługa'),
(1001, 1000, 'invoice', 'Pedicure', 'Pedicure z pielęgnacją stóp', 1, 150.00, 'usługa'),
(1002, 1000, 'invoice', 'Przedłużanie rzęs', 'Przedłużanie rzęs 1:1', 1, 250.00, 'usługa'),
(1003, 1001, 'invoice', 'Pakiet miesięczny "Perła"', 'Pakiet 4 wizyt w miesiącu', 1, 450.00, 'pakiet'),
(1004, 1002, 'invoice', 'Regulacja brwi', 'Regulacja brwi z henna', 1, 80.00, 'usługa');

-- ============================================
-- 3. CONTRACTS (Umowy)
-- ============================================

INSERT INTO `tblcontracts` (`id`, `content`, `description`, `subject`, `client`, `datestart`, `dateend`, `contract_value`, `trash`, `addedfrom`) VALUES
(1000, '<h2>Umowa na obsługę korporacyjną</h2><p>Salon Perła Urody świadczy usługi beauty dla pracowników OfficeStyle Sp. z o.o. na następujących warunkach:</p><ul><li>10% rabatu na wszystkie usługi</li><li>Faktury miesięczne zbiorcze</li><li>Priorytetowe terminy</li></ul>', 'Umowa na usługi beauty dla pracowników firmy OfficeStyle', 'Umowa korporacyjna OfficeStyle', 1001, '2026-04-01', '2026-12-31', 12000.00, 0, 1);

-- ============================================
-- 4. ESTIMATES (Wyceny)
-- ============================================

INSERT INTO `tblestimates` (`id`, `sent`, `datesend`, `clientid`, `number`, `date`, `expirydate`, `status`, `subtotal`, `total`, `addedfrom`) VALUES
(1000, 1, '2026-04-05 16:20:00', 1002, 'WYC/BEA/2026/001', '2026-04-05', '2026-05-05', 1, 450.00, 553.50, 1),
(1001, 1, '2026-04-12 10:30:00', 1004, 'WYC/BEA/2026/002', '2026-04-12', '2026-05-12', 1, 2200.00, 2706.00, 1),
(1002, 0, NULL, 1003, 'WYC/BEA/2026/003', '2026-04-22', '2026-05-22', 1, 950.00, 1168.50, 1);

-- ============================================
-- 5. PROJECTS (Projekty)
-- ============================================

INSERT INTO `tblprojects` (`id`, `name`, `description`, `status`, `start_date`, `deadline`, `addedfrom`) VALUES
(1000, 'Rozszerzenie usług o henna brwi', 'Dodanie nowej usługi henna brwi do oferty salonu. Projekt obejmuje: badanie rynku, szkolenie personelu, kampanię marketingową.', 2, '2026-04-01', '2026-05-15', 1),
(1001, 'Kampania wiosenna "Nowy Look"', 'Kampania marketingowa na wiosnę z promocjami na usługi beauty.', 3, '2026-04-10', '2026-05-30', 1);

-- Członkowie projektu
INSERT INTO `tblprojectmembers` (`id`, `project_id`, `staff_id`) VALUES
(1000, 1000, 1), -- Właściciel
(1001, 1000, 2), -- Pracownik
(1002, 1001, 1),
(1003, 1001, 2);

-- Dyskusje w projekcie (interakcje)
INSERT INTO `tblprojectdiscussions` (`id`, `project_id`, `subject`, `description`, `staff_id`, `datecreated`) VALUES
(1000, 1000, 'Wyniki badania rynku', 'Sprawdziłem ceny henna brwi u konkurencji: BeautyLab - 90 zł, Glamour - 110 zł, Estetic - 85 zł. Proponuję cenę 95 zł z opcją odnowienia co 3 tygodnie za 70 zł.', 2, '2026-04-06 11:30:00'),
(1001, 1000, 'Reakcja na cenę', 'Dobry research! 95 zł to dobra cena wejściowa. Dodajmy też pakiet 4 zabiegów za 340 zł (85 zł za zabieg) - to zachęci do regularności.', 1, '2026-04-06 14:15:00'),
(1002, 1000, 'Szkolenie personelu', 'Umówiłam szkolenie z technik henna brwi na 20 kwietnia. Koszt: 1200 zł za 2 osoby.', 2, '2026-04-07 09:45:00');

-- ============================================
-- 6. TASKS (Zadania)
-- ============================================

INSERT INTO `tblstafftasks` (`id`, `name`, `description`, `priority`, `dateadded`, `startdate`, `duedate`, `datefinished`, `addedfrom`, `status`, `rel_id`, `rel_type`) VALUES
(1000, 'Badanie rynku - henna brwi', 'Sprawdzenie cen i ofert konkurencji dla usługi henna brwi.', 2, '2026-04-02 09:00:00', '2026-04-02', '2026-04-05', '2026-04-05 17:00:00', 2, 5, 1000, 'project'),
(1001, 'Szkolenie personelu', 'Szkolenie z techniki henna brwi dla Kasi i Asi.', 1, '2026-04-10 10:00:00', '2026-04-10', '2026-04-12', NULL, 2, 2, 1000, 'project'),
(1002, 'Projekt graficzny ulotek', 'Przygotowanie ulotek promujących nową usługę.', 3, '2026-04-15 11:30:00', '2026-04-15', '2026-04-20', NULL, 2, 1, 1000, 'project'),
(1003, 'Manicure dla klientki Wiśniewskiej', 'Manicure hybrydowy z opcją french - umówione na 14:00', 2, '2026-04-25 08:00:00', '2026-04-25', '2026-04-25', NULL, 2, 1, NULL, NULL);

-- ============================================
-- 7. LEADS (Leady)
-- ============================================

INSERT INTO `tblleads` (`id`, `name`, `title`, `company`, `description`, `phonenumber`, `email`, `dateadded`, `status`, `source`, `assigned`) VALUES
(1000, 'Agnieszka Nowak', 'Klientka prywatna', NULL, 'Zapytanie o przedłużanie rzęs volume', '+48 666 777 888', 'agnieszka.nowak@email.pl', '2026-04-24 15:30:00', 1, 1, 2),
(1001, 'Firma EventMaster', 'Organizacja eventów', 'EventMaster Sp. z o.o.', 'Zapytanie o pakiet usług beauty dla uczestników konferencji (50 osób)', '+48 111 222 333', 'biuro@eventmaster.pl', '2026-04-25 10:15:00', 1, 2, 2),
(1002, 'Julia Kowalczyk', 'Klientka prywatna', NULL, 'Interesuje się manicure hybrydowym i regulacją brwi', '+48 444 555 666', 'julia.kowalczyk@email.pl', '2026-04-26 13:45:00', 2, 3, 2);

-- ============================================
-- 8. ITEMS (Produkty/usługi)
-- ============================================

INSERT INTO `tblitems` (`id`, `description`, `long_description`, `rate`, `tax`, `tax2`, `unit`, `group_id`) VALUES
(1000, 'Manicure hybrydowy', 'Manicure hybrydowy z opcją french i pielęgnacją', 120.00, 1, 0, 'usługa', 1),
(1001, 'Pedicure', 'Pedicure z peelingiem i masażem stóp', 150.00, 1, 0, 'usługa', 1),
(1002, 'Przedłużanie rzęs 1:1', 'Przedłużanie rzęs metoda 1:1', 250.00, 1, 0, 'usługa', 1),
(1003, 'Henna brwi', 'Henna brwi z regulacją i pielęgnacją', 95.00, 1, 0, 'usługa', 1),
(1004, 'Pakiet miesięczny "Perła"', 'Pakiet 4 wizyt w miesiącu - dowolne usługi', 450.00, 1, 0, 'pakiet', 2);

-- ============================================
-- 9. KNOWLEDGE BASE (Baza wiedzy - celowo niedostępna)
-- ============================================

INSERT INTO `tblknowledgebase` (`articleid`, `articlegroup`, `subject`, `description`, `slug`, `active`, `datecreated`, `article_order`, `staff_article`) VALUES
(1000, 1, '[NIEDOSTĘPNE] Procedury salonu', 'Procedury bezpieczeństwa i higieny pracy w salonie beauty.', 'procedury-salonu', 0, '2026-04-01 10:00:00', 1, 1),
(1001, 1, 'FAQ dla klientów', 'Najczęściej zadawane pytania przez klientów salonu.', 'faq-klienci', 1, '2026-04-02 11:00:00', 2, 0);

-- ============================================
-- 10. NOTES (Notatki dla klientów)
-- ============================================

INSERT INTO `tblnotes` (`id`, `rel_id`, `rel_type`, `description`, `date_contacted`, `addedfrom`, `dateadded`) VALUES
(1000, 1002, 'customer', 'Klientka preferuje delikatne odcienie różu. Ma alergię na niektóre produkty do przedłużania rzęs - sprawdzić skład.', '2026-04-18 14:30:00', 2, '2026-04-18 15:00:00'),
(1001, 1001, 'customer', 'Firma płaci terminowo. Kontaktować się zawsze przez Martę Wiśniewską.', '2026-04-12 10:15:00', 1, '2026-04-12 10:30:00');

-- ============================================
-- END OF BEAUTY DEMO DATA
-- ============================================