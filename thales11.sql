-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 02 juin 2024 à 16:30
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Base de données : `thales11`
-- Structure de la table `appartenance`

DROP TABLE IF EXISTS `appartenance`;
CREATE TABLE IF NOT EXISTS `appartenance` (
  `numAppart` int NOT NULL,
  `BP` int DEFAULT NULL,
  `Programme` int DEFAULT NULL,
  `Phases` int DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`numAppart`),
  KEY `fk_programmes` (`Programme`),
  KEY `fk_phases` (`Phases`),
  KEY `fk_bonnes_pratiques` (`BP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `appartenance`


-- Structure de la table `association`

DROP TABLE IF EXISTS `association`;
CREATE TABLE IF NOT EXISTS `association` (
  `numAssoc` int NOT NULL,
  `BP` int DEFAULT NULL,
  `numMC` int DEFAULT NULL,
  PRIMARY KEY (`numAssoc`),
  KEY `BP` (`BP`),
  KEY `FK_association_motscles` (`numMC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `association`



-- Structure de la table `bonnespratiques`

DROP TABLE IF EXISTS `bonnespratiques`;
CREATE TABLE IF NOT EXISTS `bonnespratiques` (
  `numBP` int NOT NULL,
  `nom` text,
  `texte` text,
  PRIMARY KEY (`numBP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `bonnespratiques`


-- Structure de la table `export_table`

DROP TABLE IF EXISTS `export_table`;
CREATE TABLE IF NOT EXISTS `export_table` (
  `numBP` int DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `texte` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `export_table`


-- Structure de la table `logs`

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure de la table `motscles`

DROP TABLE IF EXISTS `motscles`;
CREATE TABLE IF NOT EXISTS `motscles` (
  `numMC` int NOT NULL,
  `nomMotsCles` text,
  PRIMARY KEY (`numMC`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `motscles`



-- Structure de la table `phases`

DROP TABLE IF EXISTS `phases`;
CREATE TABLE IF NOT EXISTS `phases` (
  `numPhases` int NOT NULL,
  `nomPhase` text,
  PRIMARY KEY (`numPhases`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `phases`

-- Structure de la table `programmes`

DROP TABLE IF EXISTS `programmes`;
CREATE TABLE IF NOT EXISTS `programmes` (
  `numProg` int NOT NULL,
  `nomProgramme` text,
  PRIMARY KEY (`numProg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `programmes`



-- Structure de la table `utilisateurs`

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `login` varchar(20) NOT NULL,
  `prenom` text,
  `nom` text,
  `mdp` text,
  `droit` text,
  `bloque` tinyint(1) DEFAULT NULL,
  `tentative_login` int DEFAULT NULL,
  `statut` text,
  PRIMARY KEY (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Déchargement des données de la table `utilisateurs`


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
