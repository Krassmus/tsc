-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. Mai 2010 um 23:18
-- Server Version: 5.1.37
-- PHP-Version: 5.2.11

--
-- Datenbank: `tsc`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `alive`
--

CREATE TABLE alive (
  force_id int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `bilder`
--

CREATE TABLE bilder (
  id int(11) NOT NULL AUTO_INCREMENT,
  filename char(100) DEFAULT NULL,
  matrix int(11) DEFAULT NULL,
  autor_force_id int(11) DEFAULT NULL,
  beschreibung text,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `contact`
--

CREATE TABLE contact (
  force1 int(11) DEFAULT NULL,
  force2 int(11) DEFAULT NULL
);

-- --------------------------------------------------------


CREATE TABLE `datafields` (
  `table` varchar(128) NOT NULL,
  `field` varchar(128) NOT NULL,
  `id` bigint(20) NOT NULL,
  `value` text NOT NULL,
  `mkdate` bigint(20) NOT NULL,
  `chdate` bigint(20) NOT NULL,
  PRIMARY KEY (`table`,`field`,`id`)
) ENGINE=MyISAM;


--
-- Tabellenstruktur f�r Tabelle `forces`
--

CREATE TABLE forces (
  id int(11) NOT NULL AUTO_INCREMENT,
  name char(40) DEFAULT NULL,
  picture int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `forum`
--

CREATE TABLE forum (
  id int(11) NOT NULL AUTO_INCREMENT,
  topic text,
  content text,
  autor_force int(11) DEFAULT NULL,
  matrix int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `gruppe`
--

CREATE TABLE gruppe (
  id int(11) NOT NULL AUTO_INCREMENT,
  name char(40) DEFAULT NULL,
  year int(11) DEFAULT NULL,
  founder_login char(40) DEFAULT NULL,
  freigegeben tinyint(1) DEFAULT '0',
  PRIMARY KEY (id)
) AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `haushalt`
--

CREATE TABLE haushalt (
  id int(11) NOT NULL AUTO_INCREMENT,
  filename char(100) DEFAULT NULL,
  force_id int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  comment text,
  PRIMARY KEY (id)
) AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `languages`
--

CREATE TABLE languages (
  id varchar(32) NOT NULL,
  original text NOT NULL,
  translation text NOT NULL,
  language varchar(128) NOT NULL,
  mkdate int(11) NOT NULL,
  PRIMARY KEY (id,translation(32)),
  KEY language (language)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `live_messages`
--

CREATE TABLE live_messages (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  content text,
  from_force int(11) DEFAULT NULL,
  to_force int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `matrix`
--

CREATE TABLE matrix (
  name char(100) DEFAULT NULL,
  eintrag text,
  version timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  autor_force int(11) DEFAULT NULL,
  gruppe int(11) DEFAULT NULL,
  bild char(100) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `messages`
--

CREATE TABLE messages (
  id int(11) NOT NULL AUTO_INCREMENT,
  fromforce int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  content text,
  frompicture int(11) DEFAULT NULL,
  pdf tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `pdfdocs`
--

CREATE TABLE pdfdocs (
  id int(11) NOT NULL AUTO_INCREMENT,
  filename char(100) DEFAULT NULL,
  autor_force_id int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `relate_f_gr`
--

CREATE TABLE relate_f_gr (
  force_id int(11) DEFAULT NULL,
  gruppe_id int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `relate_master_gr`
--

CREATE TABLE relate_master_gr (
  spieler char(40) DEFAULT NULL,
  gruppe_id int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `relate_messages`
--

CREATE TABLE relate_messages (
  ms_id int(11) DEFAULT NULL,
  toforce int(11) DEFAULT NULL,
  yet_read tinyint(1) DEFAULT NULL,
  topicture int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `relate_sp_f`
--

CREATE TABLE relate_sp_f (
  spieler char(40) DEFAULT NULL,
  force_id int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `spieler`
--

CREATE TABLE spieler (
  login char(40) NOT NULL,
  password char(40) DEFAULT NULL,
  online_as int(11) DEFAULT NULL,
  notiz text,
  backgroundimage int(11) DEFAULT NULL,
  headercolor int(11) DEFAULT '0',
  font char(40) DEFAULT NULL,
  headerfont char(40) DEFAULT NULL,
  headerfontsize int(11) DEFAULT NULL,
  fontsize int(11) DEFAULT NULL,
  titlealert tinyint(1) DEFAULT '1',
  PRIMARY KEY (login),
  UNIQUE KEY login (login)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `year`
--

CREATE TABLE year (
  gruppe int(11) NOT NULL,
  zeit timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  jahreszahl int(11) DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `years`
--

CREATE TABLE years (
  gruppe int(11) DEFAULT NULL,
  year int(11) DEFAULT NULL,
  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
