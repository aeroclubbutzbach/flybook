-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 16. Sep 2014 um 14:37
-- Server Version: 5.5.27
-- PHP-Version: 5.4.7

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
-- Tabellenstruktur für Tabelle `steuersaetze`
--

CREATE TABLE IF NOT EXISTS `steuersaetze` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `steuersatz` float(10,2) unsigned NOT NULL DEFAULT '0.00',
  `bemerkungen` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `steuersaetze`
--

INSERT INTO `steuersaetze` (`id`, `steuersatz`, `bemerkungen`) VALUES
(1, 0.00, '[AF] (Ausbildungsflug), [SF] (Schulflug), [WS] (Windenstart Schulung), [XX] (Werkstattflug)'),
(2, 7.00, '[FM] (F-Schlepp Mitglieder), [FS] (F-Schlepp), [MC] (Mitglied Charter), [WC] (Windenstart Charter)'),
(3, 19.00, '[FR] (F-Schlepp Fremde), [GA] (Gastflug)');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
