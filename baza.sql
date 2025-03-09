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
