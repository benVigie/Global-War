-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Dim 17 Novembre 2013 à 13:19
-- Version du serveur: 5.5.20
-- Version de PHP: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `risk`
--

-- --------------------------------------------------------

--
-- Structure de la table `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `board_id` int(11) NOT NULL AUTO_INCREMENT,
  `board_game` int(11) NOT NULL,
  `board_player` int(11) NOT NULL,
  `board_place` int(11) NOT NULL,
  `board_units` int(11) NOT NULL,
  `board_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`board_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `chat`
--

CREATE TABLE IF NOT EXISTS `chat` (
  `chat_id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_player_id` int(11) NOT NULL,
  `chat_private` int(11) NOT NULL,
  `chat_game_id` int(11) NOT NULL,
  `chat_message` text NOT NULL,
  `chat_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_status` enum('pending','ended') NOT NULL,
  `game_nb_players` int(11) NOT NULL,
  `game_current_player` int(11) NOT NULL,
  `game_start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `game_end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `game_periods`
--

CREATE TABLE IF NOT EXISTS `game_periods` (
  `gp_start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `gp_player_of_the_previous_period` int(11) NOT NULL,
  PRIMARY KEY (`gp_start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `player_nick` varchar(32) NOT NULL,
  `player_pass` varchar(64) NOT NULL,
  `player_mail` varchar(64) NOT NULL,
  `player_score` int(11) NOT NULL,
  `player_global_score` int(11) NOT NULL,
  `player_notification` tinyint(4) NOT NULL,
  `player_available` tinyint(4) NOT NULL,
  `player_inscription` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `players_in_games`
--

CREATE TABLE IF NOT EXISTS `players_in_games` (
  `pig_game` int(11) NOT NULL,
  `pig_player` int(11) NOT NULL,
  `pig_player_status` enum('alive','winner','two','three','four','giveup') NOT NULL,
  `pig_order` int(11) NOT NULL,
  `pig_renf_max` int(11) NOT NULL,
  `pig_renf_number` int(11) NOT NULL,
  `pig_bonus` int(11) NOT NULL,
  `pig_color` varchar(7) NOT NULL,
  PRIMARY KEY (`pig_game`,`pig_player`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `strokes`
--

CREATE TABLE IF NOT EXISTS `strokes` (
  `stroke_id` int(11) NOT NULL AUTO_INCREMENT,
  `stroke_game` int(11) NOT NULL,
  `stroke_player` int(11) NOT NULL,
  `stroke_type` enum('renforcement','attack','move','done') NOT NULL,
  `stroke_board_src` int(11) NOT NULL,
  `stroke_board_dest` int(11) NOT NULL,
  `stroke_value_src` int(11) NOT NULL,
  `stroke_value_dest` int(11) NOT NULL,
  `stroke_infos_src` varchar(8) NOT NULL,
  `stroke_infos_dest` varchar(8) NOT NULL,
  `stroke_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stroke_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
