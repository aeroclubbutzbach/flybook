-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 16. Sep 2014 um 14:20
-- Server Version: 5.1.66
-- PHP-Version: 5.3.27-1~dotdeb.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `usr_web97_4`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `preiskategorien`
--

CREATE TABLE IF NOT EXISTS `preiskategorien` (
  `id` char(2) NOT NULL,
  `kennzeichen` varchar(10) NOT NULL,
  `muster` varchar(20) NOT NULL,
  `rechnungstext` varchar(50) NOT NULL,
  `startart` varchar(20) NOT NULL,
  `flugart` varchar(50) NOT NULL,
  `mwst_satz` float(10,2) unsigned NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`,`kennzeichen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `preiskategorien`
--

INSERT INTO `preiskategorien` (`id`, `kennzeichen`, `muster`, `rechnungstext`, `startart`, `flugart`, `mwst_satz`) VALUES
('AF', 'D-KIEJ', 'SF 25 C Falke', 'Ausbildungsflug', 'Eigenstart', 'S - Schulflug', 0.00),
('AF', 'D-KIEO', 'SF 25 C Falke', 'Ausbildungsflug', 'Eigenstart', 'S - Schulflug', 0.00),
('AF', 'D-KOMH', 'HK36TTS Dimona', 'Ausbildungsflug', 'Eigenstart', 'S - Schulflug', 0.00),
('AF', 'D-MDYB', 'Dynamic WT9', 'Ausbildungsflug', 'Eigenstart', 'S - Schulflug', 0.00),
('AF', 'D-MLUM', 'Dynamic WT9', 'Ausbildungsflug', 'Eigenstart', 'S - Schulflug', 0.00),
('FM', 'D-KIEJ', 'SF 25 C Falke', 'F-Schlepp Mitglieder', 'Eigenstart', 'F - Flugzeug-Schleppflug', 7.00),
('FM', 'D-KIEO', 'SF 25 C Falke', 'F-Schlepp Mitglieder', 'Eigenstart', 'F - Flugzeug-Schleppflug', 7.00),
('FM', 'D-KOMH', 'HK36TTS Dimona', 'F-Schlepp Mitglieder', 'Eigenstart', 'F - Flugzeug-Schleppflug', 7.00),
('FM', 'D-MDYB', 'Dynamic WT9', 'F-Schlepp Mitglieder', 'Eigenstart', 'F - Flugzeug-Schleppflug', 7.00),
('FM', 'D-MLUM', 'Dynamic WT9', 'F-Schlepp Mitglieder', 'Eigenstart', 'F - Flugzeug-Schleppflug', 7.00),
('FR', 'D-KIEJ', 'SF 25 C Falke', 'F-Schlepp Fremde', 'Eigenstart', 'F - Flugzeug-Schleppflug', 19.00),
('FR', 'D-KIEO', 'SF 25 C Falke', 'F-Schlepp Fremde', 'Eigenstart', 'F - Flugzeug-Schleppflug', 19.00),
('FR', 'D-KOMH', 'HK36TTS Dimona', 'F-Schlepp Fremde', 'Eigenstart', 'F - Flugzeug-Schleppflug', 19.00),
('FR', 'D-MDYB', 'Dynamic WT9', 'F-Schlepp Fremde', 'Eigenstart', 'F - Flugzeug-Schleppflug', 19.00),
('FR', 'D-MLUM', 'Dynamic WT9', 'F-Schlepp Fremde', 'Eigenstart', 'F - Flugzeug-Schleppflug', 19.00),
('FS', 'D-0235', 'ASK 13', 'ASK 13 F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('FS', 'D-1158', 'LS-8', 'LS-8 F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('FS', 'D-3952', 'DG 303', 'DG 303 F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('FS', 'D-4642', 'Grunau Baby', 'Baby F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('FS', 'D-5115', 'LS-4', 'LS-4 F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('FS', 'D-8541', 'K8', 'K8 F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('GA', 'D-0235', 'ASK 13', 'Gastflug Windenstart', 'Windenstart', 'P - Passagierflug', 19.00),
('GA', 'D-KIEJ', 'SF 25 C Falke', 'Gastflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 19.00),
('GA', 'D-KIEO', 'SF 25 C Falke', 'Gastflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 19.00),
('GA', 'D-KOMH', 'HK36TTS Dimona', 'Gastflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 19.00),
('GA', 'D-KPIA', 'DG1001T', 'Gastflug Windenstart', 'Windenstart', 'P - Passagierflug', 19.00),
('GA', 'D-MDYB', 'Dynamic WT9', 'Gastflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 19.00),
('GA', 'D-MLUM', 'Dynamic WT9', 'Gastflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 19.00),
('GF', 'D-0235', 'ASK 13', 'Gastflug F-Schlepp', 'F-Schlepp', 'P - Passagierflug', 19.00),
('GF', 'D-KPIA', 'DG1001T', 'Gastflug F-Schlepp', 'F-Schlepp', 'P - Passagierflug', 19.00),
('MC', 'D-KIEJ', 'SF 25 C Falke', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('MC', 'D-KIEO', 'SF 25 C Falke', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('MC', 'D-KOMH', 'HK36TTS Dimona', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('MC', 'D-MAXT', 'Sunwheel', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('MC', 'D-MDYB', 'Dynamic WT9', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('MC', 'D-MLUM', 'Dynamic WT9', 'Mitglied-Charter', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('NF', 'D-KPIA', 'DG1001T', 'Fluggebühr F-Schlepp', 'F-Schlepp', 'N - Privatflug (nichtgewerblich)', 7.00),
('NW', 'D-KPIA', 'DG1001T', 'Fluggebühr', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('SF', 'D-KIEJ', 'SF 25 C Falke', 'Schulflug', 'Eigenstart', 'S - Schulflug', 0.00),
('SF', 'D-KIEO', 'SF 25 C Falke', 'Schulflug', 'Eigenstart', 'S - Schulflug', 0.00),
('SF', 'D-KOMH', 'HK36TTS Dimona', 'Schulflug', 'Eigenstart', 'S - Schulflug', 0.00),
('SF', 'D-KPIA', 'DG1001T', 'Schulflug F-Schlepp', 'F-Schlepp', 'S - Schulflug', 0.00),
('SF', 'D-MDYB', 'Dynamic WT9', 'Schulflug', 'Eigenstart', 'S - Schulflug', 0.00),
('SF', 'D-MLUM', 'Dynamic WT9', 'Schulflug', 'Eigenstart', 'S - Schulflug', 0.00),
('SW', 'D-KPIA', 'DG1001T', 'Fluggebühr', 'Windenstart', 'S - Schulflug', 0.00),
('WC', 'D-0235', 'ASK 13', 'ASK 13 Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WC', 'D-1158', 'LS-8', 'LS-8 Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WC', 'D-3952', 'DG 303', 'DG 303 Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WC', 'D-4642', 'Grunau Baby', 'Baby Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WC', 'D-5115', 'LS-4', 'LS-4 Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WC', 'D-8541', 'K8', 'K8 Windenstart Charter', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 7.00),
('WS', 'D-0235', 'ASK 13', 'ASK 13 Windenstart Schulung', 'Windenstart', 'S - Schulflug', 0.00),
('WS', 'D-1158', 'LS-8', 'LS-8 Windenstart Schulung', 'Windenstart', 'S - Schulflug', 0.00),
('WS', 'D-3952', 'DG 303', 'DG 303 Windenstart Schulung', 'Windenstart', 'S - Schulflug', 0.00),
('WS', 'D-5115', 'LS-4', 'LS-4 Windenstart Schulung', 'Windenstart', 'S - Schulflug', 0.00),
('WS', 'D-8541', 'K8', 'K8 Windenstart Schulung', 'Windenstart', 'S - Schulflug', 0.00),
('XX', 'D-KIEJ', 'SF 25 C Falke', 'Werkstattflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 0.00),
('XX', 'D-KIEO', 'SF 25 C Falke', 'Werkstattflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 0.00),
('XX', 'D-KOMH', 'HK36TTS Dimona', 'Werkstattflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 0.00),
('XX', 'D-KPIA', 'DG1001T', 'Werkstattflug', 'Windenstart', 'N - Privatflug (nichtgewerblich)', 0.00),
('XX', 'D-MDYB', 'Dynamic WT9', 'Werkstattflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 0.00),
('XX', 'D-MLUM', 'Dynamic WT9', 'Werkstattflug', 'Eigenstart', 'N - Privatflug (nichtgewerblich)', 0.00);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
