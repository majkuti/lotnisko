CREATE DATABASE lotnisko;
USE lotnisko;

CREATE TABLE samoloty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(255) NOT NULL,
    liczba_miejsc INT NOT NULL,
    liczba_miejsc_biznes INT NOT NULL,
    liczba_miejsc_ekonomiczna INT NOT NULL,
    data_ostatniego_przegladu DATE NOT NULL,
    status_samolotu ENUM('sprawny', 'w_serwisie', 'wycofany') NOT NULL    
);

CREATE TABLE lotniska (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(255) NOT NULL,
    miasto VARCHAR(255) NOT NULL,
    kraj VARCHAR(255) NOT NULL,
    kod_IATA VARCHAR(3) NOT NULL,
    terminal_count INT NOT NULL
);

CREATE TABLE linie_lotnicze (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(255) NOT NULL,
    kraj VARCHAR(255) NOT NULL,
    kod_IATA VARCHAR(2) NOT NULL,
    data_zalozenia DATE NOT NULL
);

CREATE TABLE samoloty_linii_lotniczych (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_samolotu INT NOT NULL,
    id_linii_lotniczych INT NOT NULL,
    data_zakupu DATE NOT NULL,
    FOREIGN KEY (id_samolotu) REFERENCES samoloty(id),
    FOREIGN KEY (id_linii_lotniczych) REFERENCES linie_lotnicze(id)
);

CREATE TABLE loty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_samolotu INT NOT NULL,
    id_lotniska_start INT NOT NULL,
    id_lotniska_koniec INT NOT NULL,
    id_linii_lotniczych INT NOT NULL,
    numer_lotu VARCHAR(10) NOT NULL,
    data_start DATETIME NOT NULL,
    data_koniec DATETIME NOT NULL,
    status_lotu ENUM('planowany', 'boarding', 'w_locie', 'wyladowal', 'opozniony', 'odwolany') NOT NULL,
    FOREIGN KEY (id_samolotu) REFERENCES samoloty(id),
    FOREIGN KEY (id_lotniska_start) REFERENCES lotniska(id),
    FOREIGN KEY (id_lotniska_koniec) REFERENCES lotniska(id),
    FOREIGN KEY (id_linii_lotniczych) REFERENCES linie_lotnicze(id)
);

CREATE TABLE pasazerowie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imie VARCHAR(255) NOT NULL,
    nazwisko VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefon VARCHAR(9) NOT NULL,
    haslo VARCHAR(255) NOT NULL,
    pesel VARCHAR(11) NOT NULL,
    frequent_flyer_points INT DEFAULT 0
);

CREATE TABLE rezerwacje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lotu INT NOT NULL,
    id_pasazera INT NOT NULL,
    numer_miejsca VARCHAR(4) NOT NULL,
    klasa_podrozy ENUM('ekonomiczna', 'biznes', 'pierwsza') NOT NULL,
    status_rezerwacji ENUM('potwierdzona', 'anulowana', 'zrealizowana') NOT NULL,
    data_rezerwacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cena DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_lotu) REFERENCES loty(id),
    FOREIGN KEY (id_pasazera) REFERENCES pasazerowie(id)
);

CREATE TABLE zaloga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imie VARCHAR(100) NOT NULL,
    nazwisko VARCHAR(100) NOT NULL,
    stanowisko ENUM('pilot', 'kopilot', 'stewardessa', 'steward') NOT NULL,
    data_zatrudnienia DATE NOT NULL,
    licencja VARCHAR(50),
    id_linii_lotniczych INT NOT NULL,
    FOREIGN KEY (id_linii_lotniczych) REFERENCES linie_lotnicze(id)
);

CREATE TABLE zaloga_lotu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lotu INT NOT NULL,
    id_pracownika INT NOT NULL,
    rola VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_lotu) REFERENCES loty(id),
    FOREIGN KEY (id_pracownika) REFERENCES zaloga(id)
);
CREATE TABLE gates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lotniska INT NOT NULL,
    numer_gate VARCHAR(10) NOT NULL,
    terminal INT NOT NULL,
    status ENUM('wolny', 'zajety', 'w_naprawie', 'zamkniety') NOT NULL,
    typ ENUM('krajowy', 'miedzynarodowy', 'schengen') NOT NULL,
    max_rozmiar_samolotu VARCHAR(10),
    FOREIGN KEY (id_lotniska) REFERENCES lotniska(id)
);

ALTER TABLE loty
ADD COLUMN id_gate_wylot INT,
ADD COLUMN id_gate_przylot INT,
ADD FOREIGN KEY (id_gate_wylot) REFERENCES gates(id),
ADD FOREIGN KEY (id_gate_przylot) REFERENCES gates(id);

CREATE TABLE `administratorzy` (
  id int(11) NOT NULL,
  email varchar(255) NOT NULL,
  haslo varchar(255) NOT NULL
) 

INSERT INTO `loty` (`id`, `id_samolotu`, `id_lotniska_start`, `id_lotniska_koniec`, `id_linii_lotniczych`, `numer_lotu`, `data_start`, `data_koniec`, `status_lotu`, `id_gate_wylot`, `id_gate_przylot`) VALUES
(7, 1, 1, 2, 1, 'MA101', '2024-02-15 08:30:00', '2024-02-15 10:45:00', 'planowany', NULL, NULL),
(8, 2, 3, 4, 1, 'MA202', '2024-02-16 12:15:00', '2024-02-16 14:30:00', 'planowany', NULL, NULL),
(9, 3, 2, 1, 1, 'MA303', '2024-02-17 16:45:00', '2024-02-17 18:15:00', 'planowany', NULL, NULL),
(10, 0, 1, 2, 0, 'MA101', '2024-01-15 08:00:00', '2024-01-15 10:30:00', 'planowany', NULL, NULL),
(11, 0, 2, 3, 0, 'MA102', '2024-01-16 09:15:00', '2024-01-16 11:45:00', 'planowany', NULL, NULL),
(12, 0, 3, 1, 0, 'MA103', '2024-01-17 12:00:00', '2024-01-17 14:30:00', 'planowany', NULL, NULL),
(13, 0, 1, 4, 0, 'MA104', '2024-01-18 14:30:00', '2024-01-18 17:00:00', 'planowany', NULL, NULL),
(14, 0, 4, 2, 0, 'MA105', '2024-01-19 07:45:00', '2024-01-19 10:15:00', 'planowany', NULL, NULL),
(15, 0, 2, 1, 0, 'MA106', '2024-01-20 16:00:00', '2024-01-20 18:30:00', 'planowany', NULL, NULL),
(16, 0, 3, 4, 0, 'MA107', '2024-01-21 11:30:00', '2024-01-21 14:00:00', 'planowany', NULL, NULL),
(17, 0, 4, 3, 0, 'MA108', '2024-01-22 13:45:00', '2024-01-22 16:15:00', 'planowany', NULL, NULL),
(18, 0, 1, 3, 0, 'MA109', '2024-01-23 10:00:00', '2024-01-23 12:30:00', 'planowany', NULL, NULL),
(19, 0, 2, 4, 0, 'MA110', '2024-01-24 15:15:00', '2024-01-24 17:45:00', 'planowany', NULL, NULL);

INSERT INTO `samoloty` (`id`, `model`, `liczba_miejsc`, `liczba_miejsc_biznes`, `liczba_miejsc_ekonomiczna`, `data_ostatniego_przegladu`, `status_samolotu`) VALUES
(1, 'Boeing 737', 180, 20, 160, '2024-01-15', 'sprawny');

INSERT INTO `samoloty_linii_lotniczych` (`id`, `id_samolotu`, `id_linii_lotniczych`, `data_zakupu`) VALUES
(2, 1, 1, '2024-01-01');

INSERT INTO `uzytkownicy` (`id`, `imie`, `nazwisko`, `email`, `haslo`, `data_rejestracji`) VALUES
(1, 'aa', 'aa', 'aa@aa.pl', '$2y$10$eMyYEGrCMbE4A2HRQ/50Y.hRKj8gBCOYbh7n/iSutjS0DvACfPUMi', '2025-03-10 00:22:41'),
(2, 'aa', 'aa', 'a2@op.pl', '$2y$10$ney9R5HKww0Lh3UDo4eNN.0SJvoscjN.ydQ5TPQlkVZ5YOUGje07S', '2025-03-10 00:26:35'),
(3, 'Krystian', 'zawada', 'ax@wp.pl', '$2y$10$mAR/7LRgEmAN5P4N.yyK/ev1/oknx/ocYuB2Q.tdV6..dO2duWFBK', '2025-03-10 22:57:31');