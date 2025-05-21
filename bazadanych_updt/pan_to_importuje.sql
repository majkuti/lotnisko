-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Maj 21, 2025 at 10:59 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lotnisko`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `administratorzy`
--

CREATE TABLE `administratorzy` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `haslo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administratorzy`
--

INSERT INTO `administratorzy` (`id`, `email`, `haslo`) VALUES
(5, 'admin@mosinair.pl', '$2y$10$PrE7wvasLQsIHXmnWhaRiOydDa9ak9RQrIHN2QTgpr23a9/uO/Jou');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `gates`
--

CREATE TABLE `gates` (
  `id` int(11) NOT NULL,
  `id_lotniska` int(11) NOT NULL,
  `numer_gate` varchar(10) NOT NULL,
  `terminal` int(11) NOT NULL,
  `status` enum('wolny','zajety','w_naprawie','zamkniety') NOT NULL,
  `typ` enum('krajowy','miedzynarodowy','schengen') NOT NULL,
  `max_rozmiar_samolotu` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `linie_lotnicze`
--

CREATE TABLE `linie_lotnicze` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `kraj` varchar(255) NOT NULL,
  `kod_IATA` varchar(2) NOT NULL,
  `data_zalozenia` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `linie_lotnicze`
--

INSERT INTO `linie_lotnicze` (`id`, `nazwa`, `kraj`, `kod_IATA`, `data_zalozenia`) VALUES
(1, 'MosinAIR', 'Polska', 'MA', '2024-01-01');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `lotniska`
--

CREATE TABLE `lotniska` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `miasto` varchar(255) NOT NULL,
  `kraj` varchar(255) NOT NULL,
  `kod_IATA` varchar(3) NOT NULL,
  `terminal_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lotniska`
--

INSERT INTO `lotniska` (`id`, `nazwa`, `miasto`, `kraj`, `kod_IATA`, `terminal_count`) VALUES
(1, 'Lotnisko Chopina', 'Warszawa', 'Polska', 'WAW', 2),
(2, 'Heathrow', 'Londyn', 'Wielka Brytania', 'LHR', 5),
(3, 'Charles de Gaulle', 'Paryż', 'Francja', 'CDG', 3),
(4, 'AWD', 'dwa', 'dwa', 'dwa', 4);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `loty`
--

CREATE TABLE `loty` (
  `id` int(11) NOT NULL,
  `id_samolotu` int(11) NOT NULL,
  `id_lotniska_start` int(11) NOT NULL,
  `id_lotniska_koniec` int(11) NOT NULL,
  `id_linii_lotniczych` int(11) NOT NULL,
  `numer_lotu` varchar(10) NOT NULL,
  `data_start` datetime NOT NULL,
  `data_koniec` datetime NOT NULL,
  `status_lotu` enum('planowany','boarding','w_locie','wyladowal','opozniony','odwolany','aktywny','zakończony') NOT NULL,
  `id_gate_wylot` int(11) DEFAULT NULL,
  `id_gate_przylot` int(11) DEFAULT NULL,
  `cena` decimal(10,2) DEFAULT NULL,
  `dostepne_miejsca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loty`
--

INSERT INTO `loty` (`id`, `id_samolotu`, `id_lotniska_start`, `id_lotniska_koniec`, `id_linii_lotniczych`, `numer_lotu`, `data_start`, `data_koniec`, `status_lotu`, `id_gate_wylot`, `id_gate_przylot`, `cena`, `dostepne_miejsca`) VALUES
(47, 1, 4, 2, 1, '2137', '2025-04-30 03:29:00', '2025-05-02 03:29:00', 'aktywny', NULL, NULL, 2137.00, 92),
(48, 1, 1, 3, 1, '2115', '2025-04-29 14:35:00', '2025-04-29 17:35:00', 'planowany', NULL, NULL, 359.00, 186),
(49, 1, 1, 2, 1, '241', '2025-05-07 16:32:00', '2025-05-10 16:32:00', 'zakończony', NULL, NULL, 4324.00, 413);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pasazerowie`
--

CREATE TABLE `pasazerowie` (
  `id` int(11) NOT NULL,
  `imie` varchar(255) NOT NULL,
  `nazwisko` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefon` varchar(9) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `pesel` varchar(11) NOT NULL,
  `frequent_flyer_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacje`
--

CREATE TABLE `rezerwacje` (
  `id` int(11) NOT NULL,
  `id_lotu` int(11) NOT NULL,
  `id_pasazera` int(11) NOT NULL,
  `numer_miejsca` varchar(4) NOT NULL,
  `klasa_podrozy` enum('ekonomiczna','biznes','pierwsza') NOT NULL,
  `status_rezerwacji` enum('potwierdzona','anulowana','zrealizowana') NOT NULL,
  `data_rezerwacji` timestamp NOT NULL DEFAULT current_timestamp(),
  `cena` decimal(10,2) NOT NULL,
  `liczba_miejsc` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rezerwacje`
--

INSERT INTO `rezerwacje` (`id`, `id_lotu`, `id_pasazera`, `numer_miejsca`, `klasa_podrozy`, `status_rezerwacji`, `data_rezerwacji`, `cena`, `liczba_miejsc`) VALUES
(7, 47, 1, 'A28', 'ekonomiczna', 'anulowana', '2025-04-29 12:35:08', 2137.00, 1),
(8, 48, 1, 'A5', 'ekonomiczna', 'potwierdzona', '2025-04-29 12:36:55', 1077.00, 1),
(9, 47, 1, 'A12', 'ekonomiczna', 'anulowana', '2025-04-29 14:43:18', 17096.00, 1),
(10, 49, 1, 'A21', 'ekonomiczna', 'anulowana', '2025-04-29 14:43:47', 4324.00, 1),
(11, 47, 1, 'A30', 'ekonomiczna', 'potwierdzona', '2025-05-20 21:10:52', 2137.00, 1),
(12, 48, 1, 'A28', 'ekonomiczna', 'potwierdzona', '2025-05-20 21:10:56', 359.00, 1),
(13, 49, 1, 'A14', 'ekonomiczna', 'potwierdzona', '2025-05-20 21:11:02', 43240.00, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `samoloty`
--

CREATE TABLE `samoloty` (
  `id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `liczba_miejsc` int(11) NOT NULL,
  `liczba_miejsc_biznes` int(11) NOT NULL,
  `liczba_miejsc_ekonomiczna` int(11) NOT NULL,
  `data_ostatniego_przegladu` date NOT NULL,
  `status_samolotu` enum('sprawny','w_serwisie','wycofany') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `samoloty`
--

INSERT INTO `samoloty` (`id`, `model`, `liczba_miejsc`, `liczba_miejsc_biznes`, `liczba_miejsc_ekonomiczna`, `data_ostatniego_przegladu`, `status_samolotu`) VALUES
(1, 'Boeing 737', 180, 20, 160, '2024-01-15', 'sprawny');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `samoloty_linii_lotniczych`
--

CREATE TABLE `samoloty_linii_lotniczych` (
  `id` int(11) NOT NULL,
  `id_samolotu` int(11) NOT NULL,
  `id_linii_lotniczych` int(11) NOT NULL,
  `data_zakupu` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `samoloty_linii_lotniczych`
--

INSERT INTO `samoloty_linii_lotniczych` (`id`, `id_samolotu`, `id_linii_lotniczych`, `data_zakupu`) VALUES
(2, 1, 1, '2024-01-01');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `data_rejestracji` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `imie`, `nazwisko`, `email`, `haslo`, `data_rejestracji`) VALUES
(1, 'Jak', 'dawdawdaw', 'aa@aa.pl', '$2y$10$eMyYEGrCMbE4A2HRQ/50Y.hRKj8gBCOYbh7n/iSutjS0DvACfPUMi', '2025-03-10 00:22:41'),
(3, 'Krystian', 'zawada', 'ax@wp.pl', '$2y$10$mAR/7LRgEmAN5P4N.yyK/ev1/oknx/ocYuB2Q.tdV6..dO2duWFBK', '2025-03-10 22:57:31'),
(4, 'alan', 'jaros', 'jaros@wp.pl', '$2y$10$w/BJIOokua8OXwPYKi.EAO4hAf6MbRgHJCqqJOux9BUmmTFhCx.vu', '2025-04-28 11:25:14');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zaloga`
--

CREATE TABLE `zaloga` (
  `id` int(11) NOT NULL,
  `imie` varchar(100) NOT NULL,
  `nazwisko` varchar(100) NOT NULL,
  `stanowisko` enum('pilot','kopilot','stewardessa','steward') NOT NULL,
  `data_zatrudnienia` date NOT NULL,
  `licencja` varchar(50) DEFAULT NULL,
  `id_linii_lotniczych` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zaloga_lotu`
--

CREATE TABLE `zaloga_lotu` (
  `id` int(11) NOT NULL,
  `id_lotu` int(11) NOT NULL,
  `id_pracownika` int(11) NOT NULL,
  `rola` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `administratorzy`
--
ALTER TABLE `administratorzy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `gates`
--
ALTER TABLE `gates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_lotniska` (`id_lotniska`);

--
-- Indeksy dla tabeli `linie_lotnicze`
--
ALTER TABLE `linie_lotnicze`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `lotniska`
--
ALTER TABLE `lotniska`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `loty`
--
ALTER TABLE `loty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_samolotu` (`id_samolotu`),
  ADD KEY `id_lotniska_start` (`id_lotniska_start`),
  ADD KEY `id_lotniska_koniec` (`id_lotniska_koniec`),
  ADD KEY `id_linii_lotniczych` (`id_linii_lotniczych`),
  ADD KEY `id_gate_wylot` (`id_gate_wylot`),
  ADD KEY `id_gate_przylot` (`id_gate_przylot`);

--
-- Indeksy dla tabeli `pasazerowie`
--
ALTER TABLE `pasazerowie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_lotu` (`id_lotu`),
  ADD KEY `rezerwacje_ibfk_2` (`id_pasazera`);

--
-- Indeksy dla tabeli `samoloty`
--
ALTER TABLE `samoloty`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `samoloty_linii_lotniczych`
--
ALTER TABLE `samoloty_linii_lotniczych`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_samolotu` (`id_samolotu`),
  ADD KEY `id_linii_lotniczych` (`id_linii_lotniczych`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `zaloga`
--
ALTER TABLE `zaloga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_linii_lotniczych` (`id_linii_lotniczych`);

--
-- Indeksy dla tabeli `zaloga_lotu`
--
ALTER TABLE `zaloga_lotu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_lotu` (`id_lotu`),
  ADD KEY `id_pracownika` (`id_pracownika`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administratorzy`
--
ALTER TABLE `administratorzy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gates`
--
ALTER TABLE `gates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `linie_lotnicze`
--
ALTER TABLE `linie_lotnicze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lotniska`
--
ALTER TABLE `lotniska`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loty`
--
ALTER TABLE `loty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pasazerowie`
--
ALTER TABLE `pasazerowie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `samoloty`
--
ALTER TABLE `samoloty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `samoloty_linii_lotniczych`
--
ALTER TABLE `samoloty_linii_lotniczych`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `zaloga`
--
ALTER TABLE `zaloga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zaloga_lotu`
--
ALTER TABLE `zaloga_lotu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gates`
--
ALTER TABLE `gates`
  ADD CONSTRAINT `gates_ibfk_1` FOREIGN KEY (`id_lotniska`) REFERENCES `lotniska` (`id`);

--
-- Constraints for table `loty`
--
ALTER TABLE `loty`
  ADD CONSTRAINT `loty_ibfk_1` FOREIGN KEY (`id_samolotu`) REFERENCES `samoloty` (`id`),
  ADD CONSTRAINT `loty_ibfk_2` FOREIGN KEY (`id_lotniska_start`) REFERENCES `lotniska` (`id`),
  ADD CONSTRAINT `loty_ibfk_3` FOREIGN KEY (`id_lotniska_koniec`) REFERENCES `lotniska` (`id`),
  ADD CONSTRAINT `loty_ibfk_4` FOREIGN KEY (`id_linii_lotniczych`) REFERENCES `linie_lotnicze` (`id`),
  ADD CONSTRAINT `loty_ibfk_5` FOREIGN KEY (`id_gate_wylot`) REFERENCES `gates` (`id`),
  ADD CONSTRAINT `loty_ibfk_6` FOREIGN KEY (`id_gate_przylot`) REFERENCES `gates` (`id`);

--
-- Constraints for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD CONSTRAINT `rezerwacje_ibfk_1` FOREIGN KEY (`id_lotu`) REFERENCES `loty` (`id`),
  ADD CONSTRAINT `rezerwacje_ibfk_2` FOREIGN KEY (`id_pasazera`) REFERENCES `uzytkownicy` (`id`);

--
-- Constraints for table `samoloty_linii_lotniczych`
--
ALTER TABLE `samoloty_linii_lotniczych`
  ADD CONSTRAINT `samoloty_linii_lotniczych_ibfk_1` FOREIGN KEY (`id_samolotu`) REFERENCES `samoloty` (`id`),
  ADD CONSTRAINT `samoloty_linii_lotniczych_ibfk_2` FOREIGN KEY (`id_linii_lotniczych`) REFERENCES `linie_lotnicze` (`id`);

--
-- Constraints for table `zaloga`
--
ALTER TABLE `zaloga`
  ADD CONSTRAINT `zaloga_ibfk_1` FOREIGN KEY (`id_linii_lotniczych`) REFERENCES `linie_lotnicze` (`id`);

--
-- Constraints for table `zaloga_lotu`
--
ALTER TABLE `zaloga_lotu`
  ADD CONSTRAINT `zaloga_lotu_ibfk_1` FOREIGN KEY (`id_lotu`) REFERENCES `loty` (`id`),
  ADD CONSTRAINT `zaloga_lotu_ibfk_2` FOREIGN KEY (`id_pracownika`) REFERENCES `zaloga` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
