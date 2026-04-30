-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 28 avr. 2026 à 14:56
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `app_tgi`
--

-- --------------------------------------------------------

--
-- Structure de la table `alertes`
--

CREATE TABLE `alertes` (
  `id` int(11) NOT NULL,
  `type_alerte` enum('delai_pv','delai_instruction','audience_proche','mandat_expire','retard_pv','retard_instruction','appel_expire','delai_detention','autre') NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `niveau` enum('info','warning','danger') DEFAULT 'info',
  `dossier_id` int(11) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `est_lue` tinyint(1) DEFAULT 0,
  `destinataire_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audiences`
--

CREATE TABLE `audiences` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `salle_id` int(11) DEFAULT NULL,
  `numero_audience` varchar(60) DEFAULT NULL,
  `date_audience` datetime NOT NULL,
  `type_audience` enum('correctionnelle','criminelle','civile','commerciale','instruction','autre') DEFAULT 'correctionnelle',
  `statut` enum('planifiee','en_cours','terminee','reportee','annulee','tenue','renvoyee') DEFAULT 'planifiee',
  `president_id` int(11) DEFAULT NULL,
  `greffier_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `motif_renvoi` text DEFAULT NULL,
  `date_renvoi` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `audiences`
--

INSERT INTO `audiences` (`id`, `dossier_id`, `salle_id`, `numero_audience`, `date_audience`, `type_audience`, `statut`, `president_id`, `greffier_id`, `notes`, `motif_renvoi`, `date_renvoi`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'AUD N°001/2026/TGI-NY', '2026-04-18 10:00:00', 'correctionnelle', 'planifiee', 10, 11, '', NULL, NULL, 1, '2026-04-18 05:43:41', '2026-04-18 05:43:41');

-- --------------------------------------------------------

--
-- Structure de la table `avocats`
--

CREATE TABLE `avocats` (
  `id` int(11) NOT NULL,
  `matricule` varchar(30) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `barreau` varchar(100) NOT NULL DEFAULT 'Barreau de Niamey',
  `numero_ordre` varchar(50) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `date_inscription` date DEFAULT NULL,
  `statut` enum('actif','suspendu','radié','honoraire') NOT NULL DEFAULT 'actif',
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `sexe` enum('M','F') DEFAULT 'M',
  `specialite` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avocat_dossier`
--

CREATE TABLE `avocat_dossier` (
  `id` int(11) NOT NULL,
  `avocat_id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `role_avocat` enum('defense','partie_civile','expert','autre') NOT NULL DEFAULT 'defense',
  `date_mandat` date DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cabinets_instruction`
--

CREATE TABLE `cabinets_instruction` (
  `id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `libelle` varchar(100) DEFAULT NULL,
  `juge_id` int(11) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cabinets_instruction`
--

INSERT INTO `cabinets_instruction` (`id`, `numero`, `libelle`, `juge_id`, `actif`) VALUES
(1, 'CAB-01', 'Doyen des Juges d\'Instruction', NULL, 1),
(2, 'CAB-02', 'Cabinet Droit Commun Mineur N°1', NULL, 1),
(3, 'CAB-03', 'Cabinet Droit Commun Mineur N°2', NULL, 1),
(4, 'CAB-04', 'Cabinet Droit Commun Majeur N°1', NULL, 1),
(5, 'CAB-05', 'Cabinet Droit Commun Majeur N°2', NULL, 1),
(6, 'CAB-06', 'Cabinet Pôle Économique et Financier N°1', NULL, 1),
(7, 'CAB-07', 'Cabinet Pôle Économique et Financier N°2', NULL, 1),
(8, 'CAB-08', 'Cabinet Pôle Antiterroriste', NULL, 1),
(9, 'VVVV', 'VVVV', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `casier_judiciaire_condamnations`
--

CREATE TABLE `casier_judiciaire_condamnations` (
  `id` int(11) NOT NULL,
  `personne_id` int(11) NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `date_condamnation` date NOT NULL,
  `juridiction` varchar(200) DEFAULT 'TGI-HC Niamey',
  `infraction` text NOT NULL,
  `peine` text NOT NULL,
  `date_fin_peine` date DEFAULT NULL,
  `gracie` tinyint(1) NOT NULL DEFAULT 0,
  `date_grace` date DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `casier_judiciaire_personnes`
--

CREATE TABLE `casier_judiciaire_personnes` (
  `id` int(11) NOT NULL,
  `nin` varchar(30) DEFAULT NULL COMMENT 'Numéro d''Identification National',
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(200) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `sexe` enum('M','F') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commissions_rogatoires`
--

CREATE TABLE `commissions_rogatoires` (
  `id` int(11) NOT NULL,
  `numero_cr` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `type_cr` enum('nationale','internationale') NOT NULL DEFAULT 'nationale',
  `autorite_destinataire` varchar(250) NOT NULL,
  `date_envoi` date NOT NULL,
  `objet` text NOT NULL,
  `date_retour` date DEFAULT NULL,
  `resultats` text DEFAULT NULL,
  `statut` enum('envoyee','executee','retour','classee') NOT NULL DEFAULT 'envoyee',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `communes`
--

CREATE TABLE `communes` (
  `id` int(11) NOT NULL,
  `departement_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `communes`
--

INSERT INTO `communes` (`id`, `departement_id`, `nom`, `code`, `latitude`, `longitude`) VALUES
(1, 1, 'Aderbissinat', '1011', NULL, NULL),
(2, 2, 'Agadez', '1021', NULL, NULL),
(3, 3, 'Arlit', '1031', NULL, NULL),
(4, 3, 'Dannet', '1032', NULL, NULL),
(5, 3, 'Gougaram', '1033', NULL, NULL),
(6, 4, 'Bilma', '1041', NULL, NULL),
(7, 4, 'Dirkou', '1042', NULL, NULL),
(8, 4, 'Djado', '1043', NULL, NULL),
(9, 4, 'Fachi', '1044', NULL, NULL),
(10, 5, 'Iferouane', '1051', NULL, NULL),
(11, 5, 'Timia', '1052', NULL, NULL),
(12, 6, 'Ingall', '1061', NULL, NULL),
(13, 7, 'Tassara', '1071', NULL, NULL),
(14, 8, 'Kao', '1081', NULL, NULL),
(15, 8, 'Tchintabaraden', '1082', NULL, NULL),
(16, 9, 'Dabaga', '1091', NULL, NULL),
(17, 9, 'Tabelot', '1092', NULL, NULL),
(18, 9, 'Tchirozerine', '1093', NULL, NULL),
(19, 10, 'Bosso', '2011', NULL, NULL),
(20, 10, 'Toumour', '2012', NULL, NULL),
(21, 11, 'Chétimari', '2021', NULL, NULL),
(22, 11, 'Diffa', '2022', NULL, NULL),
(23, 11, 'Gueskérou', '2023', NULL, NULL),
(24, 12, 'Goudoumaria', '2031', NULL, NULL),
(25, 13, 'Foulatari', '2041', NULL, NULL),
(26, 13, 'Maïné Soroa', '2042', NULL, NULL),
(27, 13, 'N\'Gelbeyli', '2043', NULL, NULL),
(28, 14, 'N\'Gourti', '2051', NULL, NULL),
(29, 15, 'Kabelawa', '2061', NULL, NULL),
(30, 15, 'N\'Guigmi', '2062', NULL, NULL),
(31, 16, 'Birni N\'Gaouré', '3011', NULL, NULL),
(32, 16, 'Fabidji', '3012', NULL, NULL),
(33, 16, 'Fakara', '3013', NULL, NULL),
(34, 16, 'Harika', '3014', NULL, NULL),
(35, 16, 'Kankandi', '3015', NULL, NULL),
(36, 16, 'Kiota', '3016', NULL, NULL),
(37, 16, 'Koygolo', '3017', NULL, NULL),
(38, 16, 'N\'Gonga', '3018', NULL, NULL),
(39, 17, 'Dioundiou', '3021', NULL, NULL),
(40, 17, 'Kara Kara', '3022', NULL, NULL),
(41, 17, 'Zabori', '3023', NULL, NULL),
(42, 18, 'Dan Kassari', '3031', NULL, NULL),
(43, 18, 'Dogon Kiria', '3032', NULL, NULL),
(44, 18, 'Dogondoutchi', '3033', NULL, NULL),
(45, 18, 'Kiéché', '3034', NULL, NULL),
(46, 18, 'Matankari', '3035', NULL, NULL),
(47, 18, 'Soucoucoutane', '3036', NULL, NULL),
(48, 19, 'Dosso', '3041', NULL, NULL),
(49, 19, 'Farrey', '3042', NULL, NULL),
(50, 19, 'Garankedeye', '3043', NULL, NULL),
(51, 19, 'Goberi', '3044', NULL, NULL),
(52, 19, 'Gorouol Bangou', '3045', NULL, NULL),
(53, 19, 'Kargui Bangou', '3046', NULL, NULL),
(54, 19, 'Mokko', '3047', NULL, NULL),
(55, 19, 'Sakadamna', '3048', NULL, NULL),
(56, 19, 'Sambera', '3049', NULL, NULL),
(57, 19, 'Tessa', '3050', NULL, NULL),
(58, 19, 'Tombo Koarey', '3051', NULL, NULL),
(59, 20, 'Falmey', '3051', NULL, NULL),
(60, 20, 'Guilladjé', '3052', NULL, NULL),
(61, 21, 'Bana', '3061', NULL, NULL),
(62, 21, 'Bengou', '3062', NULL, NULL),
(63, 21, 'Gaya', '3063', NULL, NULL),
(64, 21, 'Tanda', '3064', NULL, NULL),
(65, 21, 'Tounouga', '3065', NULL, NULL),
(66, 21, 'Yélou', '3066', NULL, NULL),
(67, 22, 'Falwel', '3071', NULL, NULL),
(68, 22, 'Loga', '3072', NULL, NULL),
(69, 22, 'Sokorbe', '3073', NULL, NULL),
(70, 23, 'Doumega', '3081', NULL, NULL),
(71, 23, 'Guéchémé', '3082', NULL, NULL),
(72, 23, 'Koré Maïroua', '3083', NULL, NULL),
(73, 23, 'Tibiri', '3084', NULL, NULL),
(74, 24, 'Aguié', '4011', NULL, NULL),
(75, 24, 'Tchadoua', '4012', NULL, NULL),
(76, 25, 'Adjékoria', '4021', NULL, NULL),
(77, 25, 'Azagor', '4022', NULL, NULL),
(78, 25, 'Bader Goula', '4023', NULL, NULL),
(79, 25, 'Birnin Lallé', '4024', NULL, NULL),
(80, 25, 'Dakoro', '4025', NULL, NULL),
(81, 25, 'Dan Goulbi', '4026', NULL, NULL),
(82, 25, 'Korahanné', '4027', NULL, NULL),
(83, 25, 'Kornaka', '4028', NULL, NULL),
(84, 25, 'Maïyara', '4029', NULL, NULL),
(85, 25, 'Roumbou', '4030', NULL, NULL),
(86, 25, 'Sabon Machi', '4031', NULL, NULL),
(87, 25, 'Tagriss', '4032', NULL, NULL),
(88, 26, 'Gangara', '4031', NULL, NULL),
(89, 26, 'Gazaoua', '4032', NULL, NULL),
(90, 27, 'Chadakori', '4041', NULL, NULL),
(91, 27, 'Guidan Roumdji', '4042', NULL, NULL),
(92, 27, 'Guidan Sori', '4043', NULL, NULL),
(93, 27, 'Saé Saboua', '4044', NULL, NULL),
(94, 27, 'Tibiri', '4045', NULL, NULL),
(95, 28, 'Dan Issa', '4051', NULL, NULL),
(96, 28, 'Djirataoua', '4052', NULL, NULL),
(97, 28, 'Gabi', '4053', NULL, NULL),
(98, 28, 'Madarounfa', '4054', NULL, NULL),
(99, 28, 'Safo', '4055', NULL, NULL),
(100, 28, 'Serkin Yamma', '4056', NULL, NULL),
(101, 29, 'Attantané', '4061', NULL, NULL),
(102, 29, 'El Allassane Maïrerey', '4062', NULL, NULL),
(103, 29, 'Guidan Amoumoune', '4063', NULL, NULL),
(104, 29, 'Issawane', '4064', NULL, NULL),
(105, 29, 'Kanembakaché', '4065', NULL, NULL),
(106, 29, 'Mayahi', '4066', NULL, NULL),
(107, 29, 'Serkin Haoussa', '4067', NULL, NULL),
(108, 29, 'Tchaké', '4068', NULL, NULL),
(109, 30, 'Baoudéta', '4071', NULL, NULL),
(110, 30, 'Hawandawaki', '4072', NULL, NULL),
(111, 30, 'Koona', '4073', NULL, NULL),
(112, 30, 'Korgom', '4074', NULL, NULL),
(113, 30, 'Maïjirgui', '4075', NULL, NULL),
(114, 30, 'Ourafane', '4076', NULL, NULL),
(115, 30, 'Tessaoua', '4077', NULL, NULL),
(116, 31, 'Maradi 1', '4081', NULL, NULL),
(117, 31, 'Maradi 2', '4082', NULL, NULL),
(118, 31, 'Maradi 3', '4083', NULL, NULL),
(119, 32, 'Abalak', '5011', NULL, NULL),
(120, 32, 'Akoubounou', '5012', NULL, NULL),
(121, 32, 'Azèye', '5013', NULL, NULL),
(122, 32, 'Tabalak', '5014', NULL, NULL),
(123, 32, 'Tamaya', '5015', NULL, NULL),
(124, 33, 'Bagaroua', '5021', NULL, NULL),
(125, 34, 'Allela', '5031', NULL, NULL),
(126, 34, 'Bazaga', '5032', NULL, NULL),
(127, 34, 'Birni N\'Konni', '5033', NULL, NULL),
(128, 34, 'Tsernaoua', '5034', NULL, NULL),
(129, 35, 'Allakeye', '5041', NULL, NULL),
(130, 35, 'Baban Katami', '5042', NULL, NULL),
(131, 35, 'Bouza', '5043', NULL, NULL),
(132, 35, 'Déoulé', '5044', NULL, NULL),
(133, 35, 'Karofane', '5045', NULL, NULL),
(134, 35, 'Tabotaki', '5046', NULL, NULL),
(135, 35, 'Tama', '5047', NULL, NULL),
(136, 36, 'Badaguichiri', '5051', NULL, NULL),
(137, 36, 'Illéla', '5052', NULL, NULL),
(138, 36, 'Tajaé', '5053', NULL, NULL),
(139, 37, 'Garhanga', '5061', NULL, NULL),
(140, 37, 'Ibohamane', '5062', NULL, NULL),
(141, 37, 'Keita', '5063', NULL, NULL),
(142, 37, 'Tamaské', '5064', NULL, NULL),
(143, 38, 'Azarori', '5071', NULL, NULL),
(144, 38, 'Bangui', '5072', NULL, NULL),
(145, 38, 'Galma Koudawatché', '5073', NULL, NULL),
(146, 38, 'Madaoua', '5074', NULL, NULL),
(147, 38, 'Ourno', '5075', NULL, NULL),
(148, 38, 'Sabon Guida', '5076', NULL, NULL),
(149, 39, 'Dogueraoua', '5081', NULL, NULL),
(150, 39, 'Malbaza', '5082', NULL, NULL),
(151, 40, 'Afala', '5091', NULL, NULL),
(152, 40, 'Bambeye', '5092', NULL, NULL),
(153, 40, 'Barmou', '5093', NULL, NULL),
(154, 40, 'Kalfou', '5094', NULL, NULL),
(155, 40, 'Takanamatt', '5095', NULL, NULL),
(156, 40, 'Tébaram', '5096', NULL, NULL),
(157, 41, 'Dakoussa', '5101', NULL, NULL),
(158, 41, 'Garagoumsa', '5102', NULL, NULL),
(159, 41, 'Tirmini', '5103', NULL, NULL),
(160, 42, 'Tillia', '5111', NULL, NULL),
(161, 43, 'Tahoua Commune 1', '5121', NULL, NULL),
(162, 43, 'Tahoua Commune 2', '5122', NULL, NULL),
(163, 44, 'Abala', '6011', NULL, NULL),
(164, 44, 'Sanam', '6012', NULL, NULL),
(165, 45, 'Ayorou', '6021', NULL, NULL),
(166, 45, 'Inatès', '6022', NULL, NULL),
(167, 46, 'Tagazar', '6031', NULL, NULL),
(168, 47, 'Banibangou', '6041', NULL, NULL),
(169, 48, 'Bankilaré', '6051', NULL, NULL),
(170, 49, 'Damana', '6061', NULL, NULL),
(171, 49, 'Filingué', '6062', NULL, NULL),
(172, 49, 'Imanan', '6063', NULL, NULL),
(173, 49, 'Kourféye Centre', '6064', NULL, NULL),
(174, 50, 'Dargol', '6071', NULL, NULL),
(175, 50, 'Gothèye', '6072', NULL, NULL),
(176, 51, 'Bitinkodji', '6081', NULL, NULL),
(177, 51, 'Dantchandou', '6082', NULL, NULL),
(178, 51, 'Hamdallaye', '6083', NULL, NULL),
(179, 51, 'Karma', '6084', NULL, NULL),
(180, 51, 'Kirtachi', '6085', NULL, NULL),
(181, 51, 'Kollo', '6086', NULL, NULL),
(182, 51, 'Kouré', '6087', NULL, NULL),
(183, 51, 'Liboré', '6088', NULL, NULL),
(184, 51, 'N\'Dounga', '6089', NULL, NULL),
(185, 51, 'Namaro', '6090', NULL, NULL),
(186, 51, 'Youri', '6091', NULL, NULL),
(187, 52, 'Dingazi Banda', '6091', NULL, NULL),
(188, 52, 'Ouallam', '6092', NULL, NULL),
(189, 52, 'Simiri', '6093', NULL, NULL),
(190, 52, 'Tondikiwindi', '6094', NULL, NULL),
(191, 53, 'Ouro Gueladio', '6101', NULL, NULL),
(192, 53, 'Say', '6102', NULL, NULL),
(193, 53, 'Tamou', '6103', NULL, NULL),
(194, 54, 'Diagourou', '6111', NULL, NULL),
(195, 54, 'Goroual', '6112', NULL, NULL),
(196, 54, 'Kokorou', '6113', NULL, NULL),
(197, 54, 'Méhana', '6114', NULL, NULL),
(198, 54, 'Téra', '6115', NULL, NULL),
(199, 55, 'Anzourou', '6121', NULL, NULL),
(200, 55, 'Bibiyergou', '6122', NULL, NULL),
(201, 55, 'Dessa', '6123', NULL, NULL),
(202, 55, 'Kourteye', '6124', NULL, NULL),
(203, 55, 'Sakoïra', '6125', NULL, NULL),
(204, 55, 'Sindar', '6126', NULL, NULL),
(205, 55, 'Tillabéri', '6127', NULL, NULL),
(206, 56, 'Makalondi', '6131', NULL, NULL),
(207, 56, 'Torodi', '6132', NULL, NULL),
(208, 57, 'Tarka', '7011', NULL, NULL),
(209, 58, 'Bermo', '7021', NULL, NULL),
(210, 58, 'Gadabédji', '7022', NULL, NULL),
(211, 59, 'Alberkaram', '7031', NULL, NULL),
(212, 59, 'Damagaram Takaya', '7032', NULL, NULL),
(213, 59, 'Guidimouni', '7033', NULL, NULL),
(214, 59, 'Kagna Wame', '7034', NULL, NULL),
(215, 59, 'Mazamni', '7035', NULL, NULL),
(216, 59, 'Moa', '7036', NULL, NULL),
(217, 60, 'Dogo Dogo', '7041', NULL, NULL),
(218, 60, 'Dungass', '7042', NULL, NULL),
(219, 60, 'Gouchi', '7043', NULL, NULL),
(220, 60, 'Mallaoua', '7044', NULL, NULL),
(221, 61, 'Alakos', '7051', NULL, NULL),
(222, 61, 'Bouné', '7052', NULL, NULL),
(223, 61, 'Gamou', '7053', NULL, NULL),
(224, 61, 'Gouré', '7054', NULL, NULL),
(225, 61, 'Guidiguir', '7055', NULL, NULL),
(226, 61, 'Kellé', '7056', NULL, NULL),
(227, 62, 'Dan Barto', '7061', NULL, NULL),
(228, 62, 'Daoutché', '7062', NULL, NULL),
(229, 62, 'Doungou', '7063', NULL, NULL),
(230, 62, 'Ichernaoua', '7064', NULL, NULL),
(231, 62, 'Kantché', '7065', NULL, NULL),
(232, 62, 'Kourni', '7066', NULL, NULL),
(233, 62, 'Matamèye', '7067', NULL, NULL),
(234, 62, 'Tsouni', '7068', NULL, NULL),
(235, 62, 'Yaouri', '7069', NULL, NULL),
(236, 63, 'Bandé', '7071', NULL, NULL),
(237, 63, 'Dan Tchio', '7072', NULL, NULL),
(238, 63, 'Kouaya', '7073', NULL, NULL),
(239, 63, 'Magaria', '7074', NULL, NULL),
(240, 63, 'Sassoumdoum', '7075', NULL, NULL),
(241, 63, 'Wacha', '7076', NULL, NULL),
(242, 63, 'Yékoua', '7077', NULL, NULL),
(243, 64, 'Dala Koleram', '7081', NULL, NULL),
(244, 64, 'Dogo', '7082', NULL, NULL),
(245, 64, 'Droum', '7083', NULL, NULL),
(246, 64, 'Gaffati', '7084', NULL, NULL),
(247, 64, 'Gouna', '7085', NULL, NULL),
(248, 64, 'Hamdara', '7086', NULL, NULL),
(249, 64, 'Mirriah', '7087', NULL, NULL),
(250, 64, 'Zermou', '7088', NULL, NULL),
(251, 65, 'Falenko', '7091', NULL, NULL),
(252, 65, 'Gangara', '7092', NULL, NULL),
(253, 65, 'Olléléwa', '7093', NULL, NULL),
(254, 65, 'Tanout', '7094', NULL, NULL),
(255, 65, 'Tenhya', '7095', NULL, NULL),
(256, 66, 'Tesker', '7101', NULL, NULL),
(257, 67, 'Zinder 1', '7111', NULL, NULL),
(258, 67, 'Zinder 2', '7112', NULL, NULL),
(259, 67, 'Zinder 3', '7113', NULL, NULL),
(260, 67, 'Zinder 4', '7114', NULL, NULL),
(261, 67, 'Zinder 5', '7115', NULL, NULL),
(262, 68, 'Niamey 1', '8011', NULL, NULL),
(263, 68, 'Niamey 2', '8012', NULL, NULL),
(264, 68, 'Niamey 3', '8013', NULL, NULL),
(265, 68, 'Niamey 4', '8014', NULL, NULL),
(266, 68, 'Niamey 5', '8015', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `communes_geo`
--

CREATE TABLE `communes_geo` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `departement_nom` varchar(150) DEFAULT NULL,
  `region_nom` varchar(100) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `code_commune` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `communes_geo`
--

INSERT INTO `communes_geo` (`id`, `nom`, `departement_nom`, `region_nom`, `longitude`, `latitude`, `code_commune`) VALUES
(1, 'BANKILARE', 'Bankilaré', 'Tillabéri', 2.949000, 14.067000, 'NER006005001'),
(2, 'GOROUOL', 'Téra', 'Tillabéri', 2.968200, 14.530000, 'NER006011002'),
(3, 'KOKOROU', 'Téra', 'Tillabéri', 3.318200, 14.530000, 'NER006011003'),
(4, 'TERA', 'Téra', 'Tillabéri', 2.968200, 14.880000, 'NER006011005'),
(5, 'DESSA', 'Tillabéri', 'Tillabéri', 2.335800, 13.429000, 'NER006012003'),
(6, 'MEHANA', 'Téra', 'Tillabéri', 2.618200, 14.880000, 'NER006011004'),
(7, 'SINDER', 'Tillabéri', 'Tillabéri', 2.335800, 13.779000, 'NER006012006'),
(8, 'SAKOIRA', 'Tillabéri', 'Tillabéri', 1.985800, 13.779000, 'NER006012005'),
(9, 'BIBIYERGOU', 'Tillabéri', 'Tillabéri', 1.985800, 13.429000, 'NER006012002'),
(10, 'TILLABERI', 'Tillabéri', 'Tillabéri', 1.635800, 14.129000, 'NER006012007'),
(11, 'DIAGOUROU', 'Téra', 'Tillabéri', 2.618200, 14.530000, 'NER006011001'),
(12, 'GOTHEYE', 'Gothèye', 'Tillabéri', 2.981600, 13.145000, 'NER006007002'),
(13, 'DARGOL', 'Gothèye', 'Tillabéri', 2.631600, 13.145000, 'NER006007001'),
(14, 'KOURTEYE', 'Tillabéri', 'Tillabéri', 1.635800, 13.779000, 'NER006012004'),
(15, 'KARMA', 'Kollo', 'Tillabéri', 2.523600, 14.567000, 'NER006008004'),
(16, 'NAMARO', 'Kollo', 'Tillabéri', 1.823600, 15.267000, 'NER006008010'),
(17, 'TORODI', 'Torodi', 'Tillabéri', 3.192800, 14.905000, 'NER006013002'),
(18, 'MAKALONDI', 'Torodi', 'Tillabéri', 2.842800, 14.905000, 'NER006013001'),
(19, 'OURO GUELADJO', 'Say', 'Tillabéri', 2.762800, 14.076000, 'NER006010001'),
(20, 'TAMOU', 'Say', 'Tillabéri', 2.762800, 14.426000, 'NER006010003'),
(21, 'KIRTACHI', 'Kollo', 'Tillabéri', 1.473600, 14.917000, 'NER006008005'),
(22, 'FALMEY', 'Falmey', 'Dosso', 2.981200, 12.815400, 'NER003005001'),
(23, 'GAYA', 'Gaya', 'Dosso', 3.090200, 13.084400, 'NER003006003'),
(24, 'BENGOU', 'Gaya', 'Dosso', 2.740200, 13.084400, 'NER003006002'),
(25, 'TOUNOUGA', 'Gaya', 'Dosso', 2.740200, 13.434400, 'NER003006005'),
(26, 'BANA', 'Gaya', 'Dosso', 2.390200, 13.084400, 'NER003006001'),
(27, 'TANDA', 'Gaya', 'Dosso', 2.390200, 13.434400, 'NER003006004'),
(28, 'YELOU', 'Gaya', 'Dosso', 3.090200, 13.434400, 'NER003006006'),
(29, 'SAMBERA', 'Dosso', 'Dosso', 1.856800, 13.729400, 'NER003004008'),
(30, 'GUILLADJE', 'Falmey', 'Dosso', 3.331200, 12.815400, 'NER003005002'),
(31, 'GOLLE', 'Dosso', 'Dosso', 2.906800, 13.029400, 'NER003004004'),
(32, 'FAREY', 'Dosso', 'Dosso', 2.206800, 13.029400, 'NER003004002'),
(33, 'TESSA', 'Dosso', 'Dosso', 2.206800, 13.729400, 'NER003004009'),
(34, 'DIOUNDIOU', 'Dioundiou', 'Dosso', 3.429200, 12.762800, 'NER003002001'),
(35, 'ZABORI', 'Dioundiou', 'Dosso', 3.429200, 13.112800, 'NER003002003'),
(36, 'KARAKARA', 'Dioundiou', 'Dosso', 3.779200, 12.762800, 'NER003002002'),
(37, 'GUECHEME', 'Tibiri', 'Dosso', 2.513600, 12.234800, 'NER003008002'),
(38, 'KARGUIBANGOU', 'Dosso', 'Dosso', 2.206800, 13.379400, 'NER003004006'),
(39, 'DOUMEGA', 'Tibiri', 'Dosso', 2.163600, 12.234800, 'NER003008001'),
(40, 'DOSSO', 'Dosso', 'Dosso', 1.856800, 13.029400, 'NER003004001'),
(41, 'GOROUBANKASSAM', 'Dosso', 'Dosso', 1.856800, 13.379400, 'NER003004005'),
(42, 'BIRNI NGAOURE', 'Boboye', 'Dosso', 3.396600, 13.048600, 'NER003001001'),
(43, 'KANKANDI', 'Boboye', 'Dosso', 3.746600, 13.398600, 'NER003001005'),
(44, 'FABIDJI', 'Boboye', 'Dosso', 3.746600, 13.048600, 'NER003001002'),
(45, 'FAKARA', 'Boboye', 'Dosso', 4.096600, 13.048600, 'NER003001003'),
(46, 'BITINKODJI', 'Kollo', 'Tillabéri', 1.473600, 14.567000, 'NER006008001'),
(47, 'YOURI', 'Kollo', 'Tillabéri', 2.173600, 15.267000, 'NER006008011'),
(48, 'SAY', 'Say', 'Tillabéri', 3.112800, 14.076000, 'NER006010002'),
(49, 'KOLLO', 'Kollo', 'Tillabéri', 1.823600, 14.917000, 'NER006008006'),
(50, 'KOURE', 'Kollo', 'Tillabéri', 2.173600, 14.917000, 'NER006008007'),
(51, 'NDOUNGA', 'Kollo', 'Tillabéri', 1.473600, 15.267000, 'NER006008009'),
(52, 'LIBORE', 'Kollo', 'Tillabéri', 2.523600, 14.917000, 'NER006008008'),
(53, 'NIAMEY 4', 'Ville de Niamey', 'Niamey', 1.858000, 13.602200, 'NER008001004'),
(54, 'NIAMEY 5', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001005'),
(55, 'NIAMEY 1', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001001'),
(56, 'NIAMEY 2', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001002'),
(57, 'NIAMEY 3', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001003'),
(58, 'HAMDALLAYE', 'Kollo', 'Tillabéri', 2.173600, 14.567000, 'NER006008003'),
(59, 'DIANTCHANDOU', 'Kollo', 'Tillabéri', 1.823600, 14.567000, 'NER006008002'),
(60, 'KOYGOLO', 'Boboye', 'Dosso', 3.396600, 13.748600, 'NER003001007'),
(61, 'TAGAZAR', 'Balleyara', 'Tillabéri', 2.230600, 13.635000, 'NER006003001'),
(62, 'LOGA', 'Loga', 'Dosso', 2.417600, 12.950000, 'NER003007002'),
(63, 'FALWEL', 'Loga', 'Dosso', 2.067600, 12.950000, 'NER003007001'),
(64, 'MOKKO', 'Dosso', 'Dosso', 2.556800, 13.379400, 'NER003004007'),
(65, 'SOKORBE', 'Loga', 'Dosso', 2.067600, 13.300000, 'NER003007003'),
(66, 'GARANKEDEY', 'Dosso', 'Dosso', 2.556800, 13.029400, 'NER003004003'),
(67, 'KIOTA', 'Boboye', 'Dosso', 4.096600, 13.398600, 'NER003001006'),
(68, 'HARIKANASSOU', 'Boboye', 'Dosso', 3.396600, 13.398600, 'NER003001004'),
(69, 'NGONGA', 'Boboye', 'Dosso', 3.746600, 13.748600, 'NER003001008'),
(70, 'SIMIRI', 'Ouallam', 'Tillabéri', 1.882800, 14.082000, 'NER006009003'),
(71, 'OUALLAM', 'Ouallam', 'Tillabéri', 2.232800, 13.732000, 'NER006009002'),
(72, 'DINGAZI', 'Ouallam', 'Tillabéri', 1.882800, 13.732000, 'NER006009001'),
(73, 'FILINGUE', 'Filingué', 'Tillabéri', 2.615200, 14.702000, 'NER006006001'),
(74, 'IMANAN', 'Filingué', 'Tillabéri', 2.265200, 15.052000, 'NER006006002'),
(75, 'TONDIKANDIA', 'Filingué', 'Tillabéri', 0.000000, 0.000000, 'NER006006004'),
(76, 'KOURFEYE CENTRE', 'Filingué', 'Tillabéri', 2.615200, 15.052000, 'NER006006003'),
(77, 'TONDIKIWINDI', 'Ouallam', 'Tillabéri', 2.232800, 14.082000, 'NER006009004'),
(78, 'ANZOUROU', 'Tillabéri', 'Tillabéri', 1.635800, 13.429000, 'NER006012001'),
(79, 'INATES', 'Ayorou', 'Tillabéri', 1.709600, 14.411000, 'NER006002002'),
(80, 'AYEROU', 'Ayorou', 'Tillabéri', 1.359600, 14.411000, 'NER006002001'),
(81, 'BANIBANGOU', 'Banibangou', 'Tillabéri', 2.824200, 13.713000, 'NER006004001'),
(82, 'ABALA', 'Abala', 'Tillabéri', 1.530800, 13.729000, 'NER006001001'),
(83, 'SANAM', 'Abala', 'Tillabéri', 1.880800, 13.729000, 'NER006001002'),
(84, 'TILLIA', 'Tillia', 'Tahoua', 4.414600, 15.069000, 'NER005012001'),
(85, 'TIBIRI DOUTCHI', 'Tibiri', 'Dosso', 2.513600, 12.584800, 'NER003008004'),
(86, 'KORE MAIROUA', 'Tibiri', 'Dosso', 2.163600, 12.584800, 'NER003008003'),
(87, 'TOMBOKOIREY 1', 'Dosso', 'Dosso', 2.556800, 13.729400, 'NER003004010'),
(88, 'TOMBOKOIREY 2', 'Dosso', 'Dosso', 0.000000, 0.000000, 'NER003004011'),
(89, 'MATANKARI', 'Dogondoutchi', 'Dosso', 2.244200, 13.192000, 'NER003003005'),
(90, 'DOGONDOUTCHI', 'Dogondoutchi', 'Dosso', 2.594200, 12.842000, 'NER003003002'),
(91, 'DAN KASSARI', 'Dogondoutchi', 'Dosso', 1.894200, 12.842000, 'NER003003001'),
(92, 'KIECHE', 'Dogondoutchi', 'Dosso', 1.894200, 13.192000, 'NER003003004'),
(93, 'SOUCOUCOUTANE', 'Dogondoutchi', 'Dosso', 2.594200, 13.192000, 'NER003003006'),
(94, 'DOGONKIRIA', 'Dogondoutchi', 'Dosso', 2.244200, 12.842000, 'NER003003003'),
(95, 'ALLELA', 'Birni N\'Konni', 'Tahoua', 5.509200, 14.602000, 'NER005003001'),
(96, 'BIRNI N\'KONNI', 'Birni N\'Konni', 'Tahoua', 5.509200, 14.952000, 'NER005003003'),
(97, 'BAZAGA', 'Birni N\'Konni', 'Tahoua', 5.859200, 14.602000, 'NER005003002'),
(98, 'ILLELA', 'Illéla', 'Tahoua', 4.649600, 15.228000, 'NER005005002'),
(99, 'BAGAROUA', 'Bagaroua', 'Tahoua', 3.987400, 14.959000, 'NER005002001'),
(100, 'TEBARAM', 'Tahoua', 'Tahoua', 4.431000, 14.842000, 'NER005009006'),
(101, 'BAMBEYE', 'Tahoua', 'Tahoua', 4.081000, 14.492000, 'NER005009002'),
(102, 'TAKANAMAT', 'Tahoua', 'Tahoua', 4.081000, 14.842000, 'NER005009005'),
(103, 'AFFALA', 'Tahoua', 'Tahoua', 3.731000, 14.492000, 'NER005009001'),
(104, 'TAHOUA1', 'Ville de Tahoua', 'Tahoua', 5.900400, 13.855000, 'NER005013001'),
(105, 'TAHOUA2', 'Ville de Tahoua', 'Tahoua', 6.250400, 13.855000, 'NER005013002'),
(106, 'KALFOU', 'Tahoua', 'Tahoua', 3.731000, 14.842000, 'NER005009004'),
(107, 'BARMOU', 'Tahoua', 'Tahoua', 4.431000, 14.492000, 'NER005009003'),
(108, 'TABALAK', 'Abalak', 'Tahoua', 3.918200, 15.592000, 'NER005001004'),
(109, 'KEITA', 'Keita', 'Tahoua', 4.563600, 13.958000, 'NER005006003'),
(110, 'AKOUBOUNOU', 'Abalak', 'Tahoua', 4.268200, 15.242000, 'NER005001002'),
(111, 'KAO', 'Tchintabaraden', 'Tahoua', 8.598000, 20.295400, 'NER005011001'),
(112, 'ABALAK', 'Abalak', 'Tahoua', 3.918200, 15.242000, 'NER005001001'),
(113, 'TAMASKE', 'Keita', 'Tahoua', 4.913600, 13.958000, 'NER005006004'),
(114, 'IBOHAMANE', 'Keita', 'Tahoua', 4.913600, 13.608000, 'NER005006002'),
(115, 'GARHANGA', 'Keita', 'Tahoua', 4.563600, 13.608000, 'NER005006001'),
(116, 'ALLAKAYE', 'Bouza', 'Tahoua', 5.185400, 14.241000, 'NER005004001'),
(117, 'DEOULE', 'Bouza', 'Tahoua', 5.185400, 14.591000, 'NER005004004'),
(118, 'BOUZA', 'Bouza', 'Tahoua', 5.885400, 14.241000, 'NER005004003'),
(119, 'TAMA', 'Bouza', 'Tahoua', 5.185400, 14.941000, 'NER005004007'),
(120, 'BADAGUICHIRI', 'Illola', 'Tahoua', 4.299600, 15.228000, 'NER005005001'),
(121, 'TAJAE', 'Illéla', 'Tahoua', 4.299600, 15.578000, 'NER005005003'),
(122, 'MALBAZA', 'Malbaza', 'Tahoua', 6.128000, 15.563000, 'NER005008002'),
(123, 'DOGUERAWA', 'Malbaza', 'Tahoua', 5.778000, 15.563000, 'NER005008001'),
(124, 'TSERNAOUA', 'Birni N\'Konni', 'Tahoua', 7.220600, 14.073000, 'NER005003004'),
(125, 'GALMA KOUDAWATCHE', 'Madaoua', 'Tahoua', 4.807800, 14.922000, 'NER005007003'),
(126, 'SABON GUIDA', 'Madaoua', 'Tahoua', 4.807800, 15.272000, 'NER005007006'),
(127, 'BANGUI', 'Madaoua', 'Tahoua', 4.457800, 14.922000, 'NER005007002'),
(128, 'TABOTAKI', 'Bouza', 'Tahoua', 5.885400, 14.591000, 'NER005004006'),
(129, 'BABANKATAMI', 'Bouza', 'Tahoua', 5.535400, 14.241000, 'NER005004002'),
(130, 'KAROFANE', 'Bouza', 'Tahoua', 5.535400, 14.591000, 'NER005004005'),
(131, 'MADAOUA', 'Madaoua', 'Tahoua', 9.159200, 13.484800, 'NER005007004'),
(132, 'OURNO', 'Madaoua', 'Tahoua', 4.457800, 15.272000, 'NER005007005'),
(133, 'ADJEKORIA', 'Dakoro', 'Maradi', 5.820800, 12.564600, 'NER004003001'),
(134, 'AZARORI', 'Madaoua', 'Tahoua', 4.107800, 14.922000, 'NER005007001'),
(135, 'KORNAKA', 'Dakoro', 'Maradi', 6.870800, 12.914600, 'NER004003008'),
(136, 'BIRNI LALLE', 'Dakoro', 'Maradi', 6.870800, 12.564600, 'NER004003004'),
(137, 'DAN GOULBI', 'Dakoro', 'Maradi', 6.170800, 12.914600, 'NER004003006'),
(138, 'GUIDAN ROUMDJI', 'Guidan Roumdji', 'Maradi', 7.101000, 13.716400, 'NER004005002'),
(139, 'CHADAKORI', 'Guidan Roumdji', 'Maradi', 6.751000, 13.716400, 'NER004005001'),
(140, 'GUIDAN SORI', 'Guidan Roumdji', 'Maradi', 7.451000, 13.716400, 'NER004005003'),
(141, 'TIBIRI MARADI', 'Guidan Roumdji', 'Maradi', 7.101000, 14.066400, 'NER004005005'),
(142, 'SARKIN YAMMA', 'Madarounfa', 'Maradi', 7.435000, 13.996800, 'NER004006006'),
(143, 'SAFO', 'Madarounfa', 'Maradi', 7.085000, 13.996800, 'NER004006005'),
(144, 'GABI', 'Madarounfa', 'Maradi', 7.435000, 13.646800, 'NER004006003'),
(145, 'MADAROUNFA', 'Madarounfa', 'Maradi', 6.735000, 13.996800, 'NER004006004'),
(146, 'DAN ISSA', 'Madarounfa', 'Maradi', 6.735000, 13.646800, 'NER004006001'),
(147, 'MARADI 3', 'Ville de Maradi', 'Maradi', 6.201200, 13.861200, 'NER004007003'),
(148, 'DJIRATAWA', 'Madarounfa', 'Maradi', 7.085000, 13.646800, 'NER004006002'),
(149, 'SAE SABOUA', 'Guidan Roumdji', 'Maradi', 6.751000, 14.066400, 'NER004005004'),
(150, 'TCHADOUA', 'Aguié', 'Maradi', 7.072800, 13.075400, 'NER004001002'),
(151, 'AGUIE', 'Aguié', 'Maradi', 6.722800, 13.075400, 'NER004001001'),
(152, 'GANGARA AGUIE', 'Gazaoua', 'Maradi', 0.000000, 0.000000, 'NER004004001'),
(153, 'GAZAOUA', 'Gazaoua', 'Maradi', 6.380000, 13.602200, 'NER004004002'),
(154, 'SABON MACHI', 'Dakoro', 'Maradi', 6.520800, 13.264600, 'NER004003011'),
(155, 'MAIYARA', 'Dakoro', 'Maradi', 5.820800, 13.264600, 'NER004003009'),
(156, 'SARKIN HAOUSSA', 'Mayahi', 'Maradi', 7.104600, 14.233000, 'NER004008007'),
(157, 'MAYAHI', 'Mayahi', 'Maradi', 7.804600, 13.883000, 'NER004008006'),
(158, 'KANAN BAKACHE', 'Mayahi', 'Maradi', 7.454600, 13.883000, 'NER004008005'),
(159, 'ATTANTANE', 'Mayahi', 'Maradi', 7.104600, 13.533000, 'NER004008001'),
(160, 'GUIDAN AMOUMOUNE', 'Mayahi', 'Maradi', 7.804600, 13.533000, 'NER004008003'),
(161, 'TESSAOUA', 'Tessaoua', 'Maradi', 6.400600, 13.784200, 'NER004009007'),
(162, 'MAIJIRGUI', 'Tessaoua', 'Maradi', 6.750600, 13.434200, 'NER004009005'),
(163, 'GARAGOUMSA', 'Takeita', 'Zinder', 6.130400, 15.350000, 'NER007008002'),
(164, 'BAOUDETTA', 'Tessaoua', 'Maradi', 6.400600, 13.084200, 'NER004009001'),
(165, 'KOONA', 'Tessaoua', 'Maradi', 7.100600, 13.084200, 'NER004009003'),
(166, 'KORGOM', 'Tessaoua', 'Maradi', 6.400600, 13.434200, 'NER004009004'),
(167, 'KANTCHE', 'Kantché', 'Zinder', 7.570600, 14.073000, 'NER007005005'),
(168, 'DAOUCHE', 'Kantché', 'Zinder', 7.570600, 13.723000, 'NER007005002'),
(169, 'ICHIRNAWA', 'Kantché', 'Zinder', 0.000000, 0.000000, 'NER007005004'),
(170, 'MATAMEY', 'Kantché', 'Zinder', 7.220600, 14.423000, 'NER007005007'),
(171, 'TSAOUNI', 'Kantché', 'Zinder', 7.570600, 14.423000, 'NER007005008'),
(172, 'HAWANDAWAKI', 'Tessaoua', 'Maradi', 6.750600, 13.084200, 'NER004009002'),
(173, 'DOUNGOU', 'Kantché', 'Zinder', 7.920600, 13.723000, 'NER007005003'),
(174, 'DROUM', 'Mirriah', 'Zinder', 8.688600, 13.777400, 'NER007007002'),
(175, 'TIRMINI', 'Takeita', 'Zinder', 5.780400, 15.700000, 'NER007008003'),
(176, 'DOGO', 'Mirriah', 'Zinder', 8.338600, 13.777400, 'NER007007001'),
(177, 'GOUNA', 'Mirriah', 'Zinder', 8.338600, 14.127400, 'NER007007004'),
(178, 'WACHA', 'Magaria', 'Zinder', 9.535800, 13.781800, 'NER007006006'),
(179, 'DUNGASS', 'Dungass', 'Zinder', 9.159200, 13.134800, 'NER007003002'),
(180, 'GOUCHI', 'Dungass', 'Zinder', 8.809200, 13.484800, 'NER007003003'),
(181, 'MALLAWA', 'Dungass', 'Zinder', 0.000000, 0.000000, 'NER007003004'),
(182, 'GUIDIMOUNI', 'Damagaram Takaya', 'Zinder', 9.442200, 13.013200, 'NER007002003'),
(183, 'HAMDARA', 'Mirriah', 'Zinder', 8.688600, 14.127400, 'NER007007005'),
(184, 'MIRRIAH', 'Mirriah', 'Zinder', 7.988600, 14.477400, 'NER007007007'),
(185, 'KOLLERAM', 'Mirriah', 'Zinder', 7.988600, 13.777400, 'NER007007006'),
(186, 'ZINDER 5', 'Ville de Zinder', 'Zinder', 8.485000, 14.128000, 'NER007011005'),
(187, 'ZINDER 4', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011004'),
(188, 'GAFFATI', 'Mirriah', 'Zinder', 7.988600, 14.127400, 'NER007007003'),
(189, 'ZERMOU', 'Mirriah', 'Zinder', 8.338600, 14.477400, 'NER007007008'),
(190, 'ZINDER 3', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011003'),
(191, 'ZINDER 2', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011002'),
(192, 'ZINDER 1', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011001'),
(193, 'DAKOUSSA', 'Takeita', 'Zinder', 5.780400, 15.350000, 'NER007008001'),
(194, 'WAME', 'Damagaram Takaya', 'Zinder', 8.742200, 13.363200, 'NER007002006'),
(195, 'ALBARKARAM', 'Damagaram Takaya', 'Zinder', 8.742200, 13.013200, 'NER007002001'),
(196, 'DAMAGARAM TAKAYA', 'Damagaram Takaya', 'Zinder', 9.092200, 13.013200, 'NER007002002'),
(197, 'MAZAMNI', 'Damagaram Takaya', 'Zinder', 9.092200, 13.363200, 'NER007002004'),
(198, 'OLLELEWA', 'Tanout', 'Zinder', 8.379000, 12.734800, 'NER007009003'),
(199, 'GOURE', 'Gouré', 'Zinder', 9.335000, 13.262400, 'NER007004004'),
(200, 'GUIDIGUIR', 'Gouré', 'Zinder', 9.685000, 13.262400, 'NER007004005'),
(201, 'KELLE', 'Gouré', 'Zinder', 10.035000, 13.262400, 'NER007004006'),
(202, 'GAMOU', 'Gouré', 'Zinder', 10.035000, 12.912400, 'NER007004003'),
(203, 'MOA', 'Damagaram Takaya', 'Zinder', 9.442200, 13.363200, 'NER007002005'),
(204, 'BOUNE', 'Gouré', 'Zinder', 9.685000, 12.912400, 'NER007004002'),
(205, 'GOUDOUMARIA', 'Goudoumaria', 'Diffa', 13.839000, 12.734600, 'NER002003001'),
(206, 'MAINE SOROA', 'Mainé-Soroa', 'Diffa', 12.840000, 13.111600, 'NER002004002'),
(207, 'FOULATARI', 'Mainé-Soroa', 'Diffa', 12.490000, 13.111600, 'NER002004001'),
(208, 'CHETIMARI', 'Diffa', 'Diffa', 13.396000, 13.130800, 'NER002002001'),
(209, 'NGUELBELY', 'Mainé-Soroa', 'Diffa', 12.490000, 13.461600, 'NER002004003'),
(210, 'KABLEWA', 'N\'Guigmi', 'Diffa', 13.106000, 13.899400, 'NER002006001'),
(211, 'GUESKEROU', 'Diffa', 'Diffa', 13.396000, 13.480800, 'NER002002003'),
(212, 'DIFFA', 'Diffa', 'Diffa', 13.746000, 13.130800, 'NER002002002'),
(213, 'TOUMOUR', 'Bosso', 'Diffa', 12.468000, 12.389000, 'NER002001002'),
(214, 'BOSSO', 'Bosso', 'Diffa', 12.118000, 12.389000, 'NER002001001'),
(215, 'NGUIGMI', 'N\'Guigmi', 'Diffa', 13.456000, 13.899400, 'NER002006002'),
(216, 'NGOURTI', 'N\'Gourti', 'Diffa', 12.371000, 12.974600, 'NER002005001'),
(217, 'TESKER', 'Tesker', 'Zinder', 9.610600, 14.330600, 'NER007010001'),
(218, 'AZEYE', 'Abalak', 'Tahoua', 4.618200, 15.242000, 'NER005001003'),
(219, 'DAKORO', 'Dakoro', 'Maradi', 5.820800, 12.914600, 'NER004003005'),
(220, 'BADER GOULA', 'Dakoro', 'Maradi', 6.520800, 12.564600, 'NER004003003'),
(221, 'ROUMBOU 1', 'Dakoro', 'Maradi', 6.170800, 13.264600, 'NER004003010'),
(222, 'AZAGOR', 'Dakoro', 'Maradi', 6.170800, 12.564600, 'NER004003002'),
(223, 'BERMO', 'Bermo', 'Maradi', 8.550000, 14.186600, 'NER004002001'),
(224, 'KORAHANE', 'Dakoro', 'Maradi', 6.520800, 12.914600, 'NER004003007'),
(225, 'GADABEDJI', 'Bermo', 'Maradi', 8.900000, 14.186600, 'NER004002002'),
(226, 'GANGARA TANOUT', 'Tanout', 'Zinder', 0.000000, 0.000000, 'NER007009002'),
(227, 'FALENKO', 'Tanout', 'Zinder', 7.679000, 12.734800, 'NER007009001'),
(228, 'OURAFANE', 'Tessaoua', 'Maradi', 7.100600, 13.434200, 'NER004009006'),
(229, 'EL ALLASSANE MAIREYREY', 'Mayahi', 'Maradi', 7.454600, 13.533000, 'NER004008002'),
(230, 'ISSAWANE', 'Mayahi', 'Maradi', 7.104600, 13.883000, 'NER004008004'),
(231, 'TCHAKE', 'Mayahi', 'Maradi', 7.454600, 14.233000, 'NER004008008'),
(232, 'TANOUT', 'Tanout', 'Zinder', 7.679000, 13.084800, 'NER007009004'),
(233, 'ALAKOSS', 'Gouré', 'Zinder', 9.335000, 12.912400, 'NER007004001'),
(234, 'TAGRISS', 'Dakoro', 'Maradi', 6.870800, 13.264600, 'NER004003012'),
(235, 'TARKA', 'Belbedji', 'Zinder', 9.639400, 14.401000, 'NER007001001'),
(236, 'TENHYA', 'Tanout', 'Zinder', 8.029000, 13.084800, 'NER007009005'),
(237, 'ADERBISSINAT', 'Aderbissinat', 'Agadez', 6.973000, 20.177000, 'NER001001001'),
(238, 'TABELOT', 'Tchirozerine', 'Agadez', 6.308000, 18.549200, 'NER001006003'),
(239, 'DABAGA', 'Tchirozerine', 'Agadez', 5.958000, 18.549200, 'NER001006002'),
(240, 'TCHIROZERINE', 'Tchirozerine', 'Agadez', 5.958000, 18.899200, 'NER001006004'),
(241, 'AGADEZ', 'Tchirozerine', 'Agadez', 7.881000, 18.647400, 'NER001006001'),
(242, 'INGALL', 'Ingall', 'Agadez', 9.009000, 18.634600, 'NER001005001'),
(243, 'TASSARA', 'Tassara', 'Tahoua', 9.105000, 20.289000, 'NER005010001'),
(244, 'TCHINTABARADEN', 'Tchintabaraden', 'Tahoua', 8.948000, 20.295400, 'NER005011002'),
(245, 'TAMAYA', 'Abalak', 'Tahoua', 4.268200, 15.592000, 'NER005001005'),
(246, 'DJADO', 'Bilma', 'Agadez', 8.482000, 20.732800, 'NER001003003'),
(247, 'DIRKOU', 'Bilma', 'Agadez', 8.832000, 20.382800, 'NER001003002'),
(248, 'BILMA', 'Bilma', 'Agadez', 8.482000, 20.382800, 'NER001003001'),
(249, 'DOGO DOGO', 'Dungass', 'Zinder', 8.809200, 13.134800, 'NER007003001'),
(250, 'DANTCHIAO', 'Magaria', 'Zinder', 9.185800, 13.431800, 'NER007006002'),
(251, 'MAGARIA', 'Magaria', 'Zinder', 8.029000, 12.734800, 'NER007006004'),
(252, 'BANDE', 'Magaria', 'Zinder', 8.835800, 13.431800, 'NER007006001'),
(253, 'YEKOUA', 'Magaria', 'Zinder', 8.835800, 14.131800, 'NER007006007'),
(254, 'SASSOUMBROUM', 'Magaria', 'Zinder', 9.185800, 13.781800, 'NER007006005'),
(255, 'DAN BARTO', 'Kantché', 'Zinder', 7.220600, 13.723000, 'NER007005001'),
(256, 'KOURNI', 'Kantché', 'Zinder', 7.920600, 14.073000, 'NER007005006'),
(257, 'YAOURI', 'Kantché', 'Zinder', 7.920600, 14.423000, 'NER007005009'),
(258, 'KWAYA', 'Magaria', 'Zinder', 9.535800, 13.431800, 'NER007006003'),
(259, 'FACHI', 'Bilma', 'Agadez', 8.832000, 20.732800, 'NER001003004'),
(260, 'TIMIA', 'Iférouane', 'Agadez', 8.784000, 21.348200, 'NER001004002'),
(261, 'IFEROUANE', 'Iférouane', 'Agadez', 8.434000, 21.348200, 'NER001004001'),
(262, 'GOUGARAM', 'Arlit', 'Agadez', 6.446000, 20.902400, 'NER001002003'),
(263, 'DANNET', 'Arlit', 'Agadez', 6.796000, 20.552400, 'NER001002002'),
(264, 'MARADI 1', 'Ville de Maradi', 'Maradi', 6.201200, 13.511200, 'NER004007001'),
(265, 'MARADI 2', 'Ville de Maradi', 'Maradi', 6.551200, 13.511200, 'NER004007002'),
(266, 'ARLIT', 'Arlit', 'Agadez', 6.446000, 20.552400, 'NER001002001');

-- --------------------------------------------------------

--
-- Structure de la table `controles_judiciaires`
--

CREATE TABLE `controles_judiciaires` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `ordonnance_id` int(11) DEFAULT NULL,
  `type_controle` enum('controle_judiciaire','liberte_provisoire','liberte_sous_caution') NOT NULL DEFAULT 'controle_judiciaire',
  `personne_nom` varchar(100) NOT NULL,
  `personne_prenom` varchar(100) DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `obligations` text NOT NULL,
  `observations` text DEFAULT NULL,
  `statut` enum('actif','leve','viole','expire') NOT NULL DEFAULT 'actif',
  `date_levee` datetime DEFAULT NULL,
  `motif_levee` text DEFAULT NULL,
  `violations` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id`, `region_id`, `nom`, `code`) VALUES
(1, 1, 'Aderbissinat', '101'),
(2, 1, 'Agadez', '102'),
(3, 1, 'Arlit', '103'),
(4, 1, 'Bilma', '104'),
(5, 1, 'Iferouane', '105'),
(6, 1, 'Ingall', '106'),
(7, 1, 'Tassara', '107'),
(8, 1, 'Tchintabaraden', '108'),
(9, 1, 'Tchirozerine', '109'),
(10, 2, 'Bosso', '201'),
(11, 2, 'Diffa', '202'),
(12, 2, 'Goudoumaria', '203'),
(13, 2, 'Maïné Soroa', '204'),
(14, 2, 'N\'Gourti', '205'),
(15, 2, 'N\'Guigmi', '206'),
(16, 3, 'Boboye', '301'),
(17, 3, 'Dioundiou', '302'),
(18, 3, 'Dogondoutchi', '303'),
(19, 3, 'Dosso', '304'),
(20, 3, 'Falmey', '305'),
(21, 3, 'Gaya', '306'),
(22, 3, 'Loga', '307'),
(23, 3, 'Tibiri', '308'),
(24, 4, 'Aguié', '401'),
(25, 4, 'Dakoro', '402'),
(26, 4, 'Gazaoua', '403'),
(27, 4, 'Guidan Roumdji', '404'),
(28, 4, 'Madarounfa', '405'),
(29, 4, 'Mayahi', '406'),
(30, 4, 'Tessaoua', '407'),
(31, 4, 'Maradi', '408'),
(32, 5, 'Abalak', '501'),
(33, 5, 'Bagaroua', '502'),
(34, 5, 'Birni N\'Konni', '503'),
(35, 5, 'Bouza', '504'),
(36, 5, 'Illéla', '505'),
(37, 5, 'Keita', '506'),
(38, 5, 'Madaoua', '507'),
(39, 5, 'Malbaza', '508'),
(40, 5, 'Tahoua', '509'),
(41, 5, 'Takanamatt', '510'),
(42, 5, 'Tillia', '511'),
(43, 5, 'Ville de Tahoua', '512'),
(44, 6, 'Abala', '601'),
(45, 6, 'Ayorou', '602'),
(46, 6, 'Balleyara', '603'),
(47, 6, 'Banibangou', '604'),
(48, 6, 'Bankilaré', '605'),
(49, 6, 'Filingué', '606'),
(50, 6, 'Gothèye', '607'),
(51, 6, 'Kollo', '608'),
(52, 6, 'Ouallam', '609'),
(53, 6, 'Say', '610'),
(54, 6, 'Téra', '611'),
(55, 6, 'Tillabéri', '612'),
(56, 6, 'Torodi', '613'),
(57, 7, 'Belbédji', '701'),
(58, 7, 'Bermo', '702'),
(59, 7, 'Damagaram Takaya', '703'),
(60, 7, 'Dungass', '704'),
(61, 7, 'Gouré', '705'),
(62, 7, 'Kantché', '706'),
(63, 7, 'Magaria', '707'),
(64, 7, 'Mirriah', '708'),
(65, 7, 'Tanout', '709'),
(66, 7, 'Tesker', '710'),
(67, 7, 'Zinder', '711'),
(68, 8, 'Niamey', '801');

-- --------------------------------------------------------

--
-- Structure de la table `detenus`
--

CREATE TABLE `detenus` (
  `id` int(11) NOT NULL,
  `numero_ecrou` varchar(50) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `surnom_alias` varchar(100) DEFAULT NULL,
  `nom_mere` varchar(100) DEFAULT NULL,
  `statut_matrimonial` enum('celibataire','marie','divorce','veuf') DEFAULT 'celibataire',
  `nombre_enfants` int(11) DEFAULT 0,
  `sexe` enum('M','F') NOT NULL DEFAULT 'M',
  `photo_identite` varchar(255) DEFAULT NULL,
  `maison_arret_id` int(11) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `profession` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `type_detention` enum('provisoire','condamne','prevenu','inculpe','detenu_provisoire','mis_en_examen','autre') DEFAULT 'provisoire',
  `date_incarceration` date DEFAULT NULL,
  `date_liberation_prevue` date DEFAULT NULL,
  `date_liberation_effective` date DEFAULT NULL,
  `cellule` varchar(50) DEFAULT NULL,
  `etablissement` varchar(200) DEFAULT NULL,
  `statut` enum('incarcere','libere','transfere','evade','decede') DEFAULT 'incarcere',
  `infractions_retenues` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `detenus`
--

INSERT INTO `detenus` (`id`, `numero_ecrou`, `nom`, `prenom`, `surnom_alias`, `nom_mere`, `statut_matrimonial`, `nombre_enfants`, `sexe`, `photo_identite`, `maison_arret_id`, `date_naissance`, `lieu_naissance`, `nationalite`, `profession`, `adresse`, `dossier_id`, `jugement_id`, `type_detention`, `date_incarceration`, `date_liberation_prevue`, `date_liberation_effective`, `cellule`, `etablissement`, `statut`, `infractions_retenues`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ECR0001/2026', 'Ali', 'ARZIKA', 'KOUDIZE', 'MINTOU SINKA', 'marie', 5, 'M', 'uploads/photos_detenus/det_dae062d59f963628_1776491861.jpg', 1, '1990-04-18', 'SOKORBE', 'Nigérienne', 'REVENDEUR', NULL, 3, NULL, 'prevenu', '2026-04-18', '2026-12-18', NULL, '', 'Maison d&#039;Arrêt de Niamey', 'incarcere', NULL, '', NULL, '2026-04-18 05:57:41', '2026-04-18 05:57:41');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `audience_id` int(11) DEFAULT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `nom_original` varchar(300) NOT NULL,
  `nom_stockage` varchar(300) NOT NULL,
  `chemin_fichier` varchar(500) DEFAULT NULL,
  `type_document` enum('pv','acte_saisine','piece_jointe','jugement','ordonnance','pv_audience','autre') NOT NULL DEFAULT 'piece_jointe',
  `mime_type` varchar(100) DEFAULT NULL,
  `taille_octets` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_by_role` varchar(50) DEFAULT NULL COMMENT 'Role de l utilisateur qui a uploadé',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `dossier_id`, `pv_id`, `audience_id`, `jugement_id`, `nom_original`, `nom_stockage`, `chemin_fichier`, `type_document`, `mime_type`, `taille_octets`, `description`, `uploaded_by`, `uploaded_by_role`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_1/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'test', 1, NULL, '2026-04-17 17:01:17'),
(2, 2, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_2/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'TEST', 1, NULL, '2026-04-17 20:54:09'),
(3, 3, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_3/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'TEST', 1, NULL, '2026-04-17 21:01:43'),
(4, 3, NULL, NULL, NULL, 'whatsapp_image_2025-11-25_at_17.20_40.jpg', 'b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'uploads/documents/dossier_3/b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'piece_jointe', 'image/jpeg', 153485, 'gg', 1, NULL, '2026-04-17 21:25:27'),
(5, 3, NULL, NULL, NULL, 'whatsapp_image_2025-11-25_at_17.20_40.jpg', 'b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'uploads/documents/dossier_3/b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'piece_jointe', 'image/jpeg', 153485, NULL, 1, NULL, '2026-04-17 23:11:17'),
(6, 3, NULL, NULL, NULL, 'WhatsApp Image 2026-04-14 at 16.51.49.jpeg', '0e827f44df1fa653_WhatsApp_Image_2026-04-14_at_16.51.49.jpeg', 'uploads/documents/dossier_3/0e827f44df1fa653_WhatsApp_Image_2026-04-14_at_16.51.49.jpeg', 'piece_jointe', 'image/jpeg', 260836, NULL, 1, NULL, '2026-04-18 06:05:03');

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `id` int(11) NOT NULL,
  `numero_rg` varchar(60) NOT NULL,
  `numero_rp` varchar(60) DEFAULT NULL,
  `numero_ri` varchar(60) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `mode_poursuite` enum('aucun','CD','FD','CRCP','RI') DEFAULT 'aucun' COMMENT 'Mode de poursuite : AUCUN, Citation Directe, Flagrant délit, CRCP, Réquisitoire Introductif',
  `intitule` varchar(255) NOT NULL,
  `objet` text DEFAULT NULL,
  `motif_classement` text DEFAULT NULL,
  `date_classement` date DEFAULT NULL,
  `motif_declassement` text DEFAULT NULL,
  `date_declassement` datetime DEFAULT NULL,
  `declasse_par` int(11) DEFAULT NULL,
  `statut_avant_classement` varchar(60) DEFAULT NULL,
  `type_affaire` enum('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
  `nature` enum('correctionnel','instructionnel','civil','commercial','criminel') DEFAULT 'correctionnel',
  `statut` enum('nouveau','parquet','en_instruction','instruction','en_audience','juge','classe','appel','transfere') NOT NULL DEFAULT 'nouveau',
  `date_enregistrement` date NOT NULL,
  `date_limite_traitement` date DEFAULT NULL,
  `date_instruction_debut` date DEFAULT NULL,
  `date_instruction_fin` date DEFAULT NULL,
  `est_antiterroriste` tinyint(1) DEFAULT 0,
  `region_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `juge_siege_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dossiers`
--

INSERT INTO `dossiers` (`id`, `numero_rg`, `numero_rp`, `numero_ri`, `pv_id`, `substitut_id`, `cabinet_id`, `mode_poursuite`, `intitule`, `objet`, `motif_classement`, `date_classement`, `motif_declassement`, `date_declassement`, `declasse_par`, `statut_avant_classement`, `type_affaire`, `nature`, `statut`, `date_enregistrement`, `date_limite_traitement`, `date_instruction_debut`, `date_instruction_fin`, `est_antiterroriste`, `region_id`, `departement_id`, `commune_id`, `juge_siege_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RG N°002/2026/TGI-NY', 'RP N°001/2026/PARQUET', 'RI N°001/2026/INSTR', 1, 5, 1, 'aucun', '', 'examen', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_instruction', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 16:09:56', '2026-04-17 20:02:34'),
(2, 'RG N°004/2026/TGI-NY', 'RP N°002/2026/PARQUET', 'RI N°002/2026/INSTR', 2, 6, 2, 'RI', '', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_instruction', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 20:53:43', '2026-04-17 20:53:43'),
(3, 'RG N°006/2026/TGI-NY', 'RP N°003/2026/PARQUET', 'RI N°003/2026/INSTR', 3, 7, 3, 'FD', '', 'TESTT', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_audience', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 21:01:24', '2026-04-18 05:43:41');

-- --------------------------------------------------------

--
-- Structure de la table `dossier_avocats`
--

CREATE TABLE `dossier_avocats` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `avocat_id` int(11) NOT NULL,
  `partie_id` int(11) DEFAULT NULL,
  `role_avocat` varchar(100) DEFAULT 'défenseur',
  `date_mandat` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_pvs`
--

CREATE TABLE `dossier_pvs` (
  `dossier_id` int(11) NOT NULL,
  `pv_id` int(11) NOT NULL,
  `date_jonction` timestamp NOT NULL DEFAULT current_timestamp(),
  `joint_par` int(11) DEFAULT NULL COMMENT 'user_id du substitut ayant fait la jonction'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jonction plusieurs PVs → un dossier (fusion par le substitut)';

--
-- Déchargement des données de la table `dossier_pvs`
--

INSERT INTO `dossier_pvs` (`dossier_id`, `pv_id`, `date_jonction`, `joint_par`) VALUES
(1, 1, '2026-04-27 10:19:25', NULL),
(2, 2, '2026-04-27 10:19:25', NULL),
(3, 3, '2026-04-27 10:19:25', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `droits_utilisateurs`
--

CREATE TABLE `droits_utilisateurs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `fonctionnalite_id` int(11) DEFAULT NULL,
  `accorde` tinyint(1) DEFAULT 1 COMMENT '1=accordé, 0=révoqué',
  `accorde_par` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `droits_utilisateurs`
--

INSERT INTO `droits_utilisateurs` (`id`, `user_id`, `menu_id`, `fonctionnalite_id`, `accorde`, `accorde_par`, `updated_at`) VALUES
(44, 1, 1, NULL, 1, 1, '2026-04-19 09:48:50'),
(45, 1, 2, NULL, 1, 1, '2026-04-19 09:48:50'),
(46, 1, 3, NULL, 1, 1, '2026-04-19 09:48:50'),
(47, 1, 4, NULL, 1, 1, '2026-04-19 09:48:50'),
(48, 1, 5, NULL, 1, 1, '2026-04-19 09:48:50'),
(49, 1, 6, NULL, 1, 1, '2026-04-19 09:48:50'),
(50, 1, 7, NULL, 1, 1, '2026-04-19 09:48:50'),
(51, 1, 8, NULL, 1, 1, '2026-04-19 09:48:50'),
(52, 1, 9, NULL, 1, 1, '2026-04-19 09:48:50'),
(53, 1, 10, NULL, 1, 1, '2026-04-19 09:48:50'),
(54, 1, 11, NULL, 1, 1, '2026-04-19 09:48:50'),
(55, 1, 12, NULL, 1, 1, '2026-04-19 09:48:50'),
(56, 1, 13, NULL, 1, 1, '2026-04-19 09:48:50'),
(57, 1, 14, NULL, 1, 1, '2026-04-19 09:48:50'),
(58, 1, 15, NULL, 1, 1, '2026-04-19 09:48:50'),
(59, 1, 16, NULL, 1, 1, '2026-04-19 09:48:50'),
(60, 1, 17, NULL, 1, 1, '2026-04-19 09:48:50'),
(61, 1, 18, NULL, 1, 1, '2026-04-19 09:48:50'),
(62, 1, 19, NULL, 1, 1, '2026-04-19 09:48:50'),
(63, 1, 20, NULL, 1, 1, '2026-04-19 09:48:50'),
(64, 1, NULL, 1, 1, 1, '2026-04-19 09:48:50'),
(65, 1, NULL, 2, 1, 1, '2026-04-19 09:48:50'),
(66, 1, NULL, 3, 1, 1, '2026-04-19 09:48:50'),
(67, 1, NULL, 4, 1, 1, '2026-04-19 09:48:50'),
(68, 1, NULL, 5, 1, 1, '2026-04-19 09:48:50'),
(69, 1, NULL, 6, 1, 1, '2026-04-19 09:48:50'),
(70, 1, NULL, 25, 1, 1, '2026-04-19 09:48:50'),
(71, 1, NULL, 26, 1, 1, '2026-04-19 09:48:50'),
(72, 1, NULL, 7, 1, 1, '2026-04-19 09:48:50'),
(73, 1, NULL, 8, 1, 1, '2026-04-19 09:48:50'),
(74, 1, NULL, 9, 1, 1, '2026-04-19 09:48:50'),
(75, 1, NULL, 10, 1, 1, '2026-04-19 09:48:50'),
(76, 1, NULL, 11, 1, 1, '2026-04-19 09:48:50'),
(77, 1, NULL, 12, 1, 1, '2026-04-19 09:48:50'),
(78, 1, NULL, 13, 1, 1, '2026-04-19 09:48:50'),
(79, 1, NULL, 14, 1, 1, '2026-04-19 09:48:50'),
(80, 1, NULL, 15, 1, 1, '2026-04-19 09:48:50'),
(81, 1, NULL, 18, 1, 1, '2026-04-19 09:48:50'),
(82, 1, NULL, 19, 1, 1, '2026-04-19 09:48:50'),
(83, 1, NULL, 16, 1, 1, '2026-04-19 09:48:50'),
(84, 1, NULL, 17, 1, 1, '2026-04-19 09:48:50'),
(85, 1, NULL, 20, 1, 1, '2026-04-19 09:48:50'),
(86, 1, NULL, 21, 1, 1, '2026-04-19 09:48:50'),
(87, 1, NULL, 22, 1, 1, '2026-04-19 09:48:50'),
(88, 1, NULL, 23, 1, 1, '2026-04-19 09:48:50'),
(89, 1, NULL, 24, 1, 1, '2026-04-19 09:48:50'),
(107, 11, 2, NULL, 1, 1, '2026-04-22 17:45:16'),
(108, 11, 12, NULL, 1, 1, '2026-04-22 17:45:16'),
(109, 11, NULL, 1, 1, 1, '2026-04-22 17:45:16'),
(110, 11, NULL, 2, 1, 1, '2026-04-22 17:45:16'),
(111, 11, NULL, 3, 1, 1, '2026-04-22 17:45:16'),
(112, 11, NULL, 4, 1, 1, '2026-04-22 17:45:16'),
(113, 11, NULL, 5, 1, 1, '2026-04-22 17:45:16'),
(114, 11, NULL, 6, 1, 1, '2026-04-22 17:45:16'),
(115, 11, NULL, 25, 1, 1, '2026-04-22 17:45:16'),
(116, 11, NULL, 26, 1, 1, '2026-04-22 17:45:16'),
(117, 11, NULL, 23, 1, 1, '2026-04-22 17:45:16'),
(118, 11, NULL, 24, 1, 1, '2026-04-22 17:45:17'),
(119, 7, 2, NULL, 1, 1, '2026-04-22 17:58:39'),
(120, 7, 8, NULL, 1, 1, '2026-04-22 17:58:39'),
(121, 7, NULL, 1, 1, 1, '2026-04-22 17:58:39'),
(122, 7, NULL, 2, 1, 1, '2026-04-22 17:58:39'),
(123, 7, NULL, 3, 1, 1, '2026-04-22 17:58:39'),
(124, 7, NULL, 4, 1, 1, '2026-04-22 17:58:39'),
(125, 7, NULL, 5, 1, 1, '2026-04-22 17:58:39'),
(126, 7, NULL, 6, 1, 1, '2026-04-22 17:58:39'),
(127, 7, NULL, 25, 1, 1, '2026-04-22 17:58:39'),
(128, 7, NULL, 26, 1, 1, '2026-04-22 17:58:39'),
(129, 7, NULL, 7, 1, 1, '2026-04-22 17:58:39'),
(130, 7, NULL, 8, 1, 1, '2026-04-22 17:58:39'),
(131, 7, NULL, 9, 1, 1, '2026-04-22 17:58:39'),
(132, 7, NULL, 10, 1, 1, '2026-04-22 17:58:39'),
(133, 7, NULL, 11, 1, 1, '2026-04-22 17:58:39'),
(134, 7, NULL, 12, 1, 1, '2026-04-22 17:58:39');

-- --------------------------------------------------------

--
-- Structure de la table `expertises_judiciaires`
--

CREATE TABLE `expertises_judiciaires` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `ordonnance_id` int(11) DEFAULT NULL,
  `type_expertise` enum('medico_legale','psychiatrique','comptable','technique','balistique','graphologique','informatique','autre') NOT NULL,
  `expert_nom` varchar(150) NOT NULL,
  `expert_qualification` varchar(200) DEFAULT NULL,
  `date_mission` date NOT NULL,
  `delai_depot` date DEFAULT NULL,
  `objet_expertise` text NOT NULL,
  `date_depot_rapport` date DEFAULT NULL,
  `conclusions` text DEFAULT NULL,
  `statut` enum('ordonnee','en_cours','deposee','validee','contestee') NOT NULL DEFAULT 'ordonnee',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expertises_judiciaires`
--

INSERT INTO `expertises_judiciaires` (`id`, `dossier_id`, `ordonnance_id`, `type_expertise`, `expert_nom`, `expert_qualification`, `date_mission`, `delai_depot`, `objet_expertise`, `date_depot_rapport`, `conclusions`, `statut`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'psychiatrique', 'uuu', '', '2026-04-18', NULL, 'hjhj', NULL, NULL, 'ordonnee', 1, '2026-04-17 23:08:02', '2026-04-17 23:08:02');

-- --------------------------------------------------------

--
-- Structure de la table `fonctionnalites`
--

CREATE TABLE `fonctionnalites` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fonctionnalites`
--

INSERT INTO `fonctionnalites` (`id`, `code`, `libelle`, `menu_id`, `description`, `actif`) VALUES
(1, 'pv_creer', 'Créer un PV', 2, NULL, 1),
(2, 'pv_modifier', 'Modifier un PV', 2, NULL, 1),
(3, 'pv_affecter', 'Affecter un substitut', 2, NULL, 1),
(4, 'pv_classer', 'Classer sans suite', 2, NULL, 1),
(5, 'pv_declasser', 'Déclasser un PV', 2, NULL, 1),
(6, 'pv_transferer', 'Transférer un PV', 2, NULL, 1),
(7, 'dossier_creer', 'Créer un dossier', 3, NULL, 1),
(8, 'dossier_modifier', 'Modifier un dossier', 3, NULL, 1),
(9, 'dossier_classer', 'Classer sans suite', 3, NULL, 1),
(10, 'dossier_declasser', 'Déclasser un dossier', 3, NULL, 1),
(11, 'dossier_instruction', 'Envoyer en instruction', 3, NULL, 1),
(12, 'dossier_pieces', 'Gérer les pièces jointes', 3, NULL, 1),
(13, 'audience_creer', 'Planifier une audience', 4, NULL, 1),
(14, 'jugement_creer', 'Saisir un jugement', 5, NULL, 1),
(15, 'jugement_appel', 'Enregistrer un appel', 5, NULL, 1),
(16, 'mandat_creer', 'Créer un mandat', 7, NULL, 1),
(17, 'mandat_statut', 'Mettre à jour statut', 7, NULL, 1),
(18, 'detenu_creer', 'Enregistrer un détenu', 6, NULL, 1),
(19, 'detenu_liberer', 'Libérer un détenu', 6, NULL, 1),
(20, 'config_cabinets', 'Gérer les cabinets', 11, NULL, 1),
(21, 'config_substituts', 'Gérer les substituts', 11, NULL, 1),
(22, 'config_parametres', 'Paramètres du tribunal', 11, NULL, 1),
(23, 'plainte_creer', 'Déposer une plainte', 12, NULL, 1),
(24, 'plainte_traiter', 'Traiter une plainte', 12, NULL, 1),
(25, 'mec_creer', 'Saisir une mise en cause', 2, NULL, 1),
(26, 'mec_decision', 'Décider poursuite/non poursuite', 2, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `fonctions_parquet`
--

CREATE TABLE `fonctions_parquet` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `type_role` enum('procureur','substitut','autre') NOT NULL DEFAULT 'substitut',
  `ordre` int(10) UNSIGNED DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fonctions_parquet`
--

INSERT INTO `fonctions_parquet` (`id`, `code`, `libelle`, `type_role`, `ordre`, `actif`, `created_at`) VALUES
(1, 'procureur', 'Procureur de la République', 'procureur', 1, 1, '2026-04-17 16:04:24'),
(2, 'procureur_adjoint', 'Procureur de la République Adjoint(e)', 'procureur', 2, 1, '2026-04-17 16:04:24'),
(3, 'substitut_1', 'Substitut N°1', 'substitut', 3, 1, '2026-04-17 16:04:24'),
(4, 'substitut_2', 'Substitut N°2', 'substitut', 4, 1, '2026-04-17 16:04:24'),
(5, 'substitut_3', 'Substitut N°3', 'substitut', 5, 1, '2026-04-17 16:04:24'),
(6, 'substitut_4', 'Substitut N°4', 'substitut', 6, 1, '2026-04-17 16:04:24'),
(7, 'substitut_5', 'Substitut N°5', 'substitut', 7, 1, '2026-04-17 16:04:24'),
(8, 'substitut_6', 'Substitut N°6', 'substitut', 8, 1, '2026-04-17 16:04:24'),
(9, 'substitut_7', 'Substitut N°7', 'substitut', 9, 1, '2026-04-17 16:04:24'),
(10, 'substitut_8', 'Substitut N°8', 'substitut', 10, 1, '2026-04-17 16:04:24'),
(11, 'substitut_9', 'Substitut N°9', 'substitut', 11, 1, '2026-04-17 16:04:24'),
(12, 'substitut_10', 'Substitut N°10', 'substitut', 12, 1, '2026-04-17 16:04:24'),
(13, 'substitut_11', 'Substitut N°11', 'substitut', 13, 1, '2026-04-17 16:04:24'),
(14, 'substitut_12', 'Substitut N°12', 'substitut', 14, 1, '2026-04-17 16:04:24'),
(15, 'substitut_13', 'Substitut N°13', 'substitut', 15, 1, '2026-04-17 16:04:24'),
(16, 'substitut_14', 'Substitut N°14', 'substitut', 16, 1, '2026-04-17 16:04:24'),
(17, 'substitut_15', 'Substitut N°15', 'substitut', 17, 1, '2026-04-17 16:04:24'),
(18, 'substitut_16', 'Substitut N°16', 'substitut', 18, 1, '2026-04-17 16:04:24'),
(19, 'substitut_17', 'Substitut N°17', 'substitut', 19, 1, '2026-04-17 16:04:24'),
(20, 'substitut_18', 'Substitut N°18', 'substitut', 20, 1, '2026-04-17 16:04:24'),
(21, 'substitut_19', 'Substitut N°19', 'substitut', 21, 1, '2026-04-17 16:04:24'),
(22, 'substitut_20', 'Substitut N°20', 'substitut', 22, 1, '2026-04-17 16:04:24'),
(23, 'substitut_21', 'Substitut N°21', 'substitut', 23, 1, '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `infractions`
--

CREATE TABLE `infractions` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `categorie` enum('criminelle','correctionnelle','contraventionnelle') NOT NULL,
  `peine_min_mois` int(11) DEFAULT NULL,
  `peine_max_mois` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `infractions`
--

INSERT INTO `infractions` (`id`, `code`, `libelle`, `categorie`, `peine_min_mois`, `peine_max_mois`, `created_at`) VALUES
(1, 'INF-001', 'Meurtre avec préméditation', 'criminelle', 120, 999, '2026-04-17 16:04:24'),
(2, 'INF-002', 'Viol', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(3, 'INF-003', 'Vol à main armée', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(4, 'INF-004', 'Terrorisme et association de malfaiteurs', 'criminelle', 120, 999, '2026-04-17 16:04:24'),
(5, 'INF-005', 'Trafic de stupéfiants', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(6, 'INF-006', 'Enlèvement et séquestration', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(7, 'INF-007', 'Escroquerie et abus de confiance', 'correctionnelle', 12, 60, '2026-04-17 16:04:24'),
(8, 'INF-008', 'Détournement de deniers publics', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(9, 'INF-009', 'Corruption active et passive', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(10, 'INF-010', 'Coups et blessures volontaires', 'correctionnelle', 6, 36, '2026-04-17 16:04:24'),
(11, 'INF-011', 'Vol simple', 'correctionnelle', 3, 24, '2026-04-17 16:04:24'),
(12, 'INF-012', 'Faux et usage de faux', 'correctionnelle', 12, 36, '2026-04-17 16:04:24'),
(13, 'INF-013', 'Trafic illicite de migrants', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(14, 'INF-014', 'Ivresse publique et manifeste', 'contraventionnelle', 0, 1, '2026-04-17 16:04:24'),
(15, 'INF-015', 'Tapage nocturne et trouble à l\'ordre public', 'contraventionnelle', 0, 1, '2026-04-17 16:04:24'),
(16, 'INF-016', 'Vol de nuit dans une habitation', 'criminelle', 24, 120, '2026-04-17 20:52:30'),
(17, 'INF-017', 'Escroquerie', 'correctionnelle', 12, 60, '2026-04-17 20:52:30'),
(18, 'INF-018', 'Vol à main armé', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(19, 'INF-019', 'Coup et blessures volontaire', 'correctionnelle', 6, 36, '2026-04-17 20:52:30'),
(20, 'INF-020', 'Trafic international de drogue à haut risque', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(21, 'INF-021', 'AMT', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(22, 'INF-022', 'Blanchiment', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(23, 'INF-023', 'Enrichissement illicite', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(24, 'INF-024', 'Détournement des deniers publics', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(25, 'INF-025', 'Infanticide', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(26, 'INF-026', 'Viol', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(27, 'INF-027', 'Abus de confiance', 'correctionnelle', 12, 60, '2026-04-17 20:52:30'),
(28, 'INF-028', 'Faux et usage de faux', 'correctionnelle', 12, 36, '2026-04-17 20:52:30'),
(29, 'INF-029', 'Coup mortel', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(30, 'INF-030', 'Assassinat', 'criminelle', 240, 999, '2026-04-17 20:52:30'),
(31, 'INF-031', 'Détention illégale d\'arme à feu', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(32, 'INF-032', 'Accès illégal dans un système informatisé', 'correctionnelle', 12, 36, '2026-04-17 20:52:30'),
(33, 'INF-033', 'Concussion', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(34, 'INF-034', 'Viol sur mineur de moins de 13 ans', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(35, 'INF-035', 'Financement du terrorisme', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(36, 'INF-036', 'Vol avec violence', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(37, 'INF-037', 'Terrorisme', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(38, 'RJ', 'RENSEIGNEMENT JUDICIAIRE', 'correctionnelle', NULL, NULL, '2026-04-22 15:44:46'),
(39, 'ES', 'EXAMEN DE SITUATION', 'criminelle', NULL, NULL, '2026-04-22 15:45:17');

-- --------------------------------------------------------

--
-- Structure de la table `jugements`
--

CREATE TABLE `jugements` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `audience_id` int(11) DEFAULT NULL,
  `numero_jugement` varchar(80) NOT NULL,
  `date_jugement` date NOT NULL,
  `type_jugement` enum('correctionnel','criminel','civil','commercial','avant_dire_droit','autre') NOT NULL DEFAULT 'correctionnel',
  `nature_jugement` enum('condamnation','relaxe','acquittement','non_lieu','renvoi','autre') NOT NULL DEFAULT 'condamnation',
  `dispositif` text NOT NULL,
  `peine_principale` varchar(255) DEFAULT NULL,
  `duree_peine_mois` int(11) DEFAULT NULL,
  `montant_amende` decimal(15,2) DEFAULT NULL,
  `dommages_interets` decimal(15,2) DEFAULT NULL,
  `sursis` tinyint(1) DEFAULT 0,
  `duree_sursis_mois` int(11) DEFAULT NULL,
  `appel_possible` tinyint(1) DEFAULT 0,
  `appel_interjecte` tinyint(1) DEFAULT 0,
  `date_limite_appel` date DEFAULT NULL,
  `date_appel` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `greffier_id` int(11) DEFAULT NULL,
  `redige_par` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maisons_arret`
--

CREATE TABLE `maisons_arret` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `ville` varchar(100) NOT NULL DEFAULT '',
  `region_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `capacite` int(11) DEFAULT 0,
  `population_actuelle` int(11) DEFAULT 0,
  `population_hommes` int(11) DEFAULT 0,
  `population_femmes` int(11) DEFAULT 0,
  `directeur` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `maisons_arret`
--

INSERT INTO `maisons_arret` (`id`, `nom`, `ville`, `region_id`, `commune_id`, `capacite`, `population_actuelle`, `population_hommes`, `population_femmes`, `directeur`, `telephone`, `adresse`, `actif`, `created_at`) VALUES
(1, 'Maison d\'Arrêt de Niamey', 'Niamey', NULL, NULL, 600, 450, 420, 30, 'Commandant Seydou MAIGA', '+227 20 73 40 00', 'Quartier Plateau, Niamey', 1, '2026-04-17 16:04:24'),
(2, 'Maison d\'Arrêt de Zinder', 'Zinder', NULL, NULL, 300, 210, 195, 15, 'Commandant Moutari HASSANE', '+227 20 51 04 10', 'Vieux Zinder, Zinder', 1, '2026-04-17 16:04:24'),
(3, 'Maison d\'Arrêt de Maradi', 'Maradi', NULL, NULL, 250, 180, 165, 15, 'Commandant Abdou LAWALI', '+227 20 41 03 55', 'Quartier Dan Goulbi, Maradi', 1, '2026-04-17 16:04:24'),
(4, 'Maison d\'Arrêt de Tahoua', 'Tahoua', NULL, NULL, 200, 130, 120, 10, 'Commandant Harouna ISSA', '+227 20 61 02 80', 'Quartier Administratif, Tahoua', 1, '2026-04-17 16:04:24'),
(5, 'Maison d\'Arrêt d\'Agadez', 'Agadez', NULL, NULL, 100, 0, 55, 5, 'Commandant Souleymane ALI', '+227 20 44 05 00', 'Centre-ville, Agadez', 1, '2026-04-17 16:04:24'),
(6, 'Maison d\'Arrêt de Dosso', 'Dosso', NULL, NULL, 150, 90, 82, 8, 'Commandant Ibrahim GARBA', '+227 20 65 01 12', 'Centre-ville, Dosso', 1, '2026-04-17 16:04:24'),
(7, 'Maison d\'Arrêt de Diffa', 'Diffa', NULL, NULL, 80, 50, 46, 4, 'Commandant Amadou BELLO', '+227 20 55 06 10', 'Centre-ville, Diffa', 1, '2026-04-17 16:04:24'),
(8, 'Centre de Détention de Kollo', 'Kollo', NULL, NULL, 100, 70, 65, 5, 'Lieutenant Adamou SOULEY', '+227 20 47 00 21', 'Route de Dosso, Kollo', 1, '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `mandats`
--

CREATE TABLE `mandats` (
  `id` int(11) NOT NULL,
  `numero` varchar(80) NOT NULL,
  `type_mandat` enum('arret','depot','amener','comparution','perquisition','liberation') NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `detenu_id` int(11) DEFAULT NULL,
  `partie_id` int(11) DEFAULT NULL,
  `nouveau_nom` varchar(150) DEFAULT NULL,
  `nouveau_prenom` varchar(150) DEFAULT NULL,
  `nouveau_ddn` date DEFAULT NULL,
  `nouveau_nationalite` varchar(100) DEFAULT 'Nigérienne',
  `nouveau_adresse` text DEFAULT NULL,
  `nouveau_profession` varchar(200) DEFAULT NULL,
  `motif` text NOT NULL,
  `infraction_libelle` text DEFAULT NULL,
  `lieu_execution` text DEFAULT NULL,
  `emetteur_id` int(11) NOT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date DEFAULT NULL,
  `statut` enum('emis','signifie','execute','annule','expire') NOT NULL DEFAULT 'emis',
  `date_execution` date DEFAULT NULL,
  `executant_nom` varchar(200) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mandats`
--

INSERT INTO `mandats` (`id`, `numero`, `type_mandat`, `dossier_id`, `detenu_id`, `partie_id`, `nouveau_nom`, `nouveau_prenom`, `nouveau_ddn`, `nouveau_nationalite`, `nouveau_adresse`, `nouveau_profession`, `motif`, `infraction_libelle`, `lieu_execution`, `emetteur_id`, `date_emission`, `date_expiration`, `statut`, `date_execution`, `executant_nom`, `observations`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'MAND N°001/2026/TGI-NY', 'arret', NULL, NULL, NULL, 'DG CAIMA', 'LAOUALI', '1993-04-17', 'Nigérienne', NULL, NULL, 'test', 'ttt', 'tttt', 1, '2026-04-17', '2026-10-17', 'execute', '2026-04-17', NULL, NULL, '2026-04-17 16:07:29', '2026-04-17 21:31:50', 1),
(2, 'MAND N°002/2026/TGI-NY', 'depot', 3, NULL, NULL, 'SANI', 'SIDIKOU', '1990-04-22', 'Nigérienne', 'BOBIEL', 'COMMERCANTE', 'ESCROQUERIE, vol en reuinion', NULL, 'NIAMEY', 1, '2026-04-22', '2026-10-22', 'emis', NULL, NULL, NULL, '2026-04-22 17:18:20', '2026-04-22 17:18:20', 1);

-- --------------------------------------------------------

--
-- Structure de la table `mec_infractions`
--

CREATE TABLE `mec_infractions` (
  `id` int(11) NOT NULL,
  `mec_id` int(11) NOT NULL COMMENT 'mises_en_cause.id',
  `infraction_id` int(11) NOT NULL,
  `type` enum('unite','substitut') NOT NULL DEFAULT 'unite',
  `est_complicite` tinyint(1) DEFAULT 0,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Infractions retenues par mis en cause';

-- --------------------------------------------------------

--
-- Structure de la table `membres_audience`
--

CREATE TABLE `membres_audience` (
  `id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom_externe` varchar(200) DEFAULT NULL,
  `role_audience` enum('president','greffier','assesseur_1','assesseur_2','jure_1','jure_2','procureur','substitut','juge_assesseur','avocat_defense','avocat_partie_civile','greffier_adjoint','autre') NOT NULL DEFAULT 'autre',
  `observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `membres_audience`
--

INSERT INTO `membres_audience` (`id`, `audience_id`, `user_id`, `nom_externe`, `role_audience`, `observations`) VALUES
(1, 1, 2, NULL, 'assesseur_1', NULL),
(2, 1, 3, NULL, 'assesseur_2', NULL),
(3, 1, NULL, 'AAAAA', 'jure_1', NULL),
(4, 1, NULL, 'BBBBBB', 'jure_2', NULL),
(5, 1, 7, NULL, 'procureur', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `ordre` int(10) UNSIGNED DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menus`
--

INSERT INTO `menus` (`id`, `code`, `libelle`, `icone`, `url`, `parent_id`, `ordre`, `actif`) VALUES
(1, 'dashboard', 'Tableau de bord', 'bi-speedometer2', '/dashboard', NULL, 1, 1),
(2, 'pv', 'Procès-Verbaux', 'bi-file-text', '/pv', NULL, 2, 1),
(3, 'dossiers', 'Dossiers', 'bi-folder2-open', '/dossiers', NULL, 3, 1),
(4, 'audiences', 'Audiences', 'bi-calendar-week', '/audiences', NULL, 4, 1),
(5, 'jugements', 'Jugements', 'bi-hammer', '/jugements', NULL, 5, 1),
(6, 'detenus', 'Population Carcérale', 'bi-person-lock', '/detenus', NULL, 6, 1),
(7, 'mandats', 'Mandats de Justice', 'bi-file-ruled', '/mandats', NULL, 7, 1),
(8, 'carte', 'Carte Antiterroriste', 'bi-map', '/carte', NULL, 8, 1),
(9, 'alertes', 'Alertes', 'bi-bell', '/alertes', NULL, 9, 1),
(10, 'users', 'Utilisateurs', 'bi-people', '/users', NULL, 10, 1),
(11, 'config', 'Configuration', 'bi-gear-fill', '/config', NULL, 11, 1),
(12, 'plaintes', 'Plaintes', 'bi-megaphone', '/plaintes', NULL, 25, 1),
(13, 'avocats', 'Barreau / Avocats', 'bi-person-badge', '/avocats', NULL, 26, 1),
(14, 'casier_judiciaire', 'Casier judiciaire', 'bi-person-vcard', '/casier-judiciaire', NULL, 27, 1),
(15, 'voies_recours', 'Voies de recours', 'bi-arrow-repeat', '/voies-recours', NULL, 28, 1),
(16, 'ordonnances', 'Ordonnances JI', 'bi-file-earmark-text', '/ordonnances', NULL, 29, 1),
(17, 'controles_judiciaires', 'Contrôles judiciaires', 'bi-shield-check', '/controles-judiciaires', NULL, 30, 1),
(18, 'expertises', 'Expertises', 'bi-microscope', '/expertises', NULL, 31, 1),
(19, 'commissions_rogatoires', 'Commissions rogatoires', 'bi-send', '/commissions-rogatoires', NULL, 32, 1),
(20, 'scelles', 'Scellés', 'bi-archive', '/scelles', NULL, 33, 1);

-- --------------------------------------------------------

--
-- Structure de la table `mises_en_cause`
--

CREATE TABLE `mises_en_cause` (
  `id` int(11) NOT NULL,
  `pv_id` int(11) NOT NULL COMMENT 'PV concerné',
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL COMMENT 'Alias / surnom',
  `nom_mere` varchar(150) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(200) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `sexe` enum('M','F','Inconnu') DEFAULT 'M',
  `profession` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `statut` enum('mise_en_cause','prevenu','temoin','autre') NOT NULL DEFAULT 'mise_en_cause',
  `statut_autre_detail` varchar(200) DEFAULT NULL COMMENT 'Précision si statut=autre',
  `photo` varchar(255) DEFAULT NULL,
  `personne_contacter_nom` varchar(200) DEFAULT NULL COMMENT 'Personne à contacter',
  `personne_contacter_tel` varchar(30) DEFAULT NULL,
  `personne_contacter_lien` varchar(100) DEFAULT NULL COMMENT 'Lien (père, mère, époux...)',
  `est_connu_archives` tinyint(1) DEFAULT 0 COMMENT '1 = déjà connu dans les archives',
  `nb_affaires_precedentes` int(11) DEFAULT 0 COMMENT 'Nombre d affaires antérieures',
  `notes_antecedents` text DEFAULT NULL,
  `decision_substitut` enum('poursuivi','non_poursuivi','en_attente') DEFAULT 'en_attente',
  `motif_non_poursuite` text DEFAULT NULL,
  `date_decision` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL COMMENT 'Substitut chargé de ce mis en cause (hérite du PV)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mises en cause saisies au moment de l enregistrement du PV';

--
-- Déchargement des données de la table `mises_en_cause`
--

INSERT INTO `mises_en_cause` (`id`, `pv_id`, `nom`, `prenom`, `alias`, `nom_mere`, `date_naissance`, `lieu_naissance`, `nationalite`, `sexe`, `profession`, `adresse`, `telephone`, `statut`, `statut_autre_detail`, `photo`, `personne_contacter_nom`, `personne_contacter_tel`, `personne_contacter_lien`, `est_connu_archives`, `nb_affaires_precedentes`, `notes_antecedents`, `decision_substitut`, `motif_non_poursuite`, `date_decision`, `created_by`, `substitut_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'DETENUS 1', 'ARZIKA', 'cobra', 'MINTOU SINKA', '1999-04-19', NULL, 'Nigérienne', 'M', 'REVENDEUR', NULL, NULL, 'prevenu', NULL, 'uploads/photos_mec/mec_38fe96c8e62dbb44_1776588618.jpeg', 'HJHJHHJHJ', NULL, NULL, 1, 8, 'HHHHHHH', 'en_attente', NULL, NULL, 1, NULL, '2026-04-19 08:50:18', '2026-04-19 08:50:18'),
(2, 5, 'AZIZ ET AUTRES', '', NULL, NULL, NULL, NULL, 'Nigérienne', 'M', NULL, NULL, NULL, 'mise_en_cause', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'en_attente', NULL, NULL, 1, NULL, '2026-04-22 16:38:58', '2026-04-22 16:38:58'),
(3, 6, 'ABIBOULAYE MOUNKAILA ET AUTRES', '', NULL, NULL, NULL, NULL, 'Nigérienne', 'M', NULL, NULL, NULL, 'mise_en_cause', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'poursuivi', NULL, '2026-04-22', 11, NULL, '2026-04-22 16:39:39', '2026-04-22 16:51:35'),
(4, 6, 'GARBA', '', NULL, NULL, NULL, NULL, 'Nigérienne', 'M', NULL, NULL, NULL, 'mise_en_cause', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'poursuivi', NULL, '2026-04-22', 1, NULL, '2026-04-22 16:48:22', '2026-04-22 16:48:51'),
(5, 6, 'MOUSTAPHA', '', NULL, NULL, NULL, NULL, 'Nigérienne', 'M', NULL, NULL, NULL, 'mise_en_cause', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'non_poursuivi', 'test', '2026-04-22', 1, NULL, '2026-04-22 16:48:29', '2026-04-22 16:49:12'),
(6, 7, 'ABDOU', '', NULL, NULL, NULL, NULL, 'Nigérienne', 'M', NULL, NULL, NULL, 'mise_en_cause', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 'en_attente', NULL, NULL, 11, NULL, '2026-04-22 16:54:04', '2026-04-22 16:54:04');

-- --------------------------------------------------------

--
-- Structure de la table `mouvements_dossier`
--

CREATE TABLE `mouvements_dossier` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type_mouvement` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ancien_statut` varchar(60) DEFAULT NULL,
  `nouveau_statut` varchar(60) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mouvements_dossier`
--

INSERT INTO `mouvements_dossier` (`id`, `dossier_id`, `user_id`, `type_mouvement`, `description`, `ancien_statut`, `nouveau_statut`, `created_at`) VALUES
(1, 1, 1, 'creation', 'Dossier créé depuis PV RG N°001/2026/TGI-NY', NULL, 'en_instruction', '2026-04-17 16:09:56'),
(2, 1, 1, 'classement', 'Classé sans suite', 'en_instruction', 'classe', '2026-04-17 16:33:46'),
(3, 1, 1, 'declassement', 'Déclassé : ssss', 'classe', 'parquet', '2026-04-17 16:34:00'),
(4, 1, 1, 'affectation_instruction', 'Affecté au cabinet d\'instruction', 'parquet', 'en_instruction', '2026-04-17 20:02:34'),
(5, 2, 1, 'creation', 'Dossier créé depuis PV RG N°003/2026/TGI-NY — Mode de poursuite : Réquisitoire Introductif', NULL, 'en_instruction', '2026-04-17 20:53:43'),
(6, 3, 1, 'creation', 'Dossier créé depuis PV RG N°005/2026/TGI-NY — Mode de poursuite : Flagrant Délit', NULL, 'en_instruction', '2026-04-17 21:01:24'),
(7, 1, 1, 'ordonnance', 'Ordonnance ORD-2026-0001 créée', NULL, NULL, '2026-04-18 06:02:15');

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances`
--

CREATE TABLE `ordonnances` (
  `id` int(11) NOT NULL,
  `numero_ordonnance` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `juge_id` int(11) DEFAULT NULL,
  `type_ordonnance` enum('renvoi','non_lieu','detention','liberation','saisie','perquisition','commission_rogatoire','autre') NOT NULL,
  `date_ordonnance` date NOT NULL,
  `contenu` text NOT NULL,
  `observations` text DEFAULT NULL,
  `statut` enum('projet','signee','notifiee','executee') NOT NULL DEFAULT 'projet',
  `date_signature` datetime DEFAULT NULL,
  `date_notification` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ordonnances`
--

INSERT INTO `ordonnances` (`id`, `numero_ordonnance`, `dossier_id`, `juge_id`, `type_ordonnance`, `date_ordonnance`, `contenu`, `observations`, `statut`, `date_signature`, `date_notification`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ORD-2026-0001', 1, 8, 'detention', '2026-04-18', 'jughhhhhhh', 'chghgffghhjfgfgtyj', 'signee', '2026-04-18 07:02:27', NULL, 1, '2026-04-18 06:02:15', '2026-04-18 06:02:27');

-- --------------------------------------------------------

--
-- Structure de la table `parametres_tribunal`
--

CREATE TABLE `parametres_tribunal` (
  `id` int(11) NOT NULL,
  `session_timeout_minutes` int(11) DEFAULT 60 COMMENT 'Durée de session en minutes',
  `max_login_attempts` int(11) DEFAULT 5 COMMENT 'Tentatives de connexion max avant blocage',
  `lockout_minutes` int(11) DEFAULT 15 COMMENT 'Durée de blocage en minutes',
  `cle` varchar(100) NOT NULL,
  `valeur` text DEFAULT NULL,
  `groupe` varchar(50) NOT NULL DEFAULT 'general',
  `libelle` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type_champ` enum('text','textarea','number','boolean','email','tel','url','color','select') NOT NULL DEFAULT 'text',
  `options_json` text DEFAULT NULL COMMENT 'JSON pour les selects',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parametres_tribunal`
--

INSERT INTO `parametres_tribunal` (`id`, `session_timeout_minutes`, `max_login_attempts`, `lockout_minutes`, `cle`, `valeur`, `groupe`, `libelle`, `description`, `type_champ`, `options_json`, `updated_at`, `updated_by`) VALUES
(1, 60, 5, 15, 'tribunal_nom_court', 'TGI-NY', 'identite', 'Nom court (sigle)', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(2, 60, 5, 15, 'tribunal_nom_complet', 'Tribunal de Grande Instance Hors Classe de Niamey', 'identite', 'Nom complet du tribunal', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(3, 60, 5, 15, 'tribunal_ville', 'Niamey', 'identite', 'Ville', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(4, 60, 5, 15, 'tribunal_pays', 'République du Niger', 'identite', 'Pays', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(5, 60, 5, 15, 'tribunal_adresse', 'Avenue de la Mairie — B.P. 466 — Niamey, République du Niger', 'identite', 'Adresse postale', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(6, 60, 5, 15, 'tribunal_telephone', '+227 20 73 20 00', 'identite', 'Téléphone', NULL, 'tel', NULL, '2026-04-17 17:08:05', 1),
(7, 60, 5, 15, 'tribunal_email', 'contact@tgi-niamey.ne', 'identite', 'Email institutionnel', NULL, 'email', NULL, '2026-04-17 17:08:05', 1),
(8, 60, 5, 15, 'tribunal_website', '', 'identite', 'Site web', NULL, 'url', NULL, '2026-04-17 17:08:05', 1),
(9, 60, 5, 15, 'tribunal_devise', 'Fraternité — Travail — Progrès', 'identite', 'Devise nationale', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(10, 60, 5, 15, 'doc_entete_ligne1', 'REPUBLIQUE DU NIGER', 'documents', 'En-tête ligne 1', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(11, 60, 5, 15, 'doc_entete_ligne2', 'MINISTÈRE DE LA JUSTICE', 'documents', 'En-tête ligne 2', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(12, 60, 5, 15, 'doc_entete_ligne3', 'Tribunal de Grande Instance Hors Classe de Niamey', 'documents', 'En-tête ligne 3', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(13, 60, 5, 15, 'doc_pied_page', 'Document officiel — TGI-NY — Niamey — République du Niger', 'documents', 'Pied de page par défaut', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(14, 60, 5, 15, 'doc_qr_code_actif', '1', 'documents', 'Activer le QR code sur les mandats', NULL, 'boolean', NULL, '2026-04-17 17:08:05', 1),
(15, 60, 5, 15, 'doc_qr_code_base_url', 'tjrtjrjrj', 'documents', 'URL de base pour les QR codes', NULL, 'url', NULL, '2026-04-17 17:08:05', 1),
(16, 60, 5, 15, 'delai_pv_jours', '30', 'delais', 'Délai traitement PV (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(17, 60, 5, 15, 'delai_instruction_mois', '6', 'delais', 'Délai instruction (mois)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(18, 60, 5, 15, 'delai_alerte_audience_jours', '3', 'delais', 'Alerte avant audience (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(19, 60, 5, 15, 'delai_appel_jours', '30', 'delais', 'Délai appel (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(20, 60, 5, 15, 'delai_detention_prov_mois', '6', 'delais', 'Délai max détention provisoire (mois)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(21, 60, 5, 15, 'num_prefix_rg', 'RG N°', 'numerotation', 'Préfixe numéro RG', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(22, 60, 5, 15, 'num_prefix_rp', 'RP N°', 'numerotation', 'Préfixe numéro RP', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(23, 60, 5, 15, 'num_prefix_ri', 'RI N°', 'numerotation', 'Préfixe numéro RI', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(24, 60, 5, 15, 'num_suffix_rg', 'TGI-NY', 'numerotation', 'Suffixe numéro RG', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(25, 60, 5, 15, 'num_suffix_rp', 'PARQUET', 'numerotation', 'Suffixe numéro RP', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(26, 60, 5, 15, 'num_suffix_ri', 'INSTR', 'numerotation', 'Suffixe numéro RI', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(27, 60, 5, 15, 'theme_couleur_primaire', '#0a2342', 'affichage', 'Couleur primaire', NULL, 'color', NULL, '2026-04-17 17:08:05', 1),
(28, 60, 5, 15, 'items_par_page', '20', 'affichage', 'Éléments par page', NULL, 'number', NULL, '2026-04-17 17:08:05', 1);

-- --------------------------------------------------------

--
-- Structure de la table `parties`
--

CREATE TABLE `parties` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `type_partie` enum('prevenu','victime','partie_civile','temoin','expert','autre') NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parties`
--

INSERT INTO `parties` (`id`, `dossier_id`, `type_partie`, `nom`, `prenom`, `date_naissance`, `nationalite`, `profession`, `adresse`, `telephone`, `notes`, `created_at`) VALUES
(1, 3, '', 'SCP', 'ARZIKA', NULL, 'Nigérienne', '', '', 'KIMBA', NULL, '2026-04-18 05:41:55'),
(2, 3, 'prevenu', 'SANI', 'SIDIKOU', '1990-04-22', 'Nigérienne', 'COMMERCANTE', 'BOBIEL', NULL, NULL, '2026-04-22 17:18:20');

-- --------------------------------------------------------

--
-- Structure de la table `plaintes`
--

CREATE TABLE `plaintes` (
  `id` int(11) NOT NULL,
  `numero_plainte` varchar(60) NOT NULL COMMENT 'Numéro de référence unique',
  `date_plainte` date NOT NULL,
  `date_reception` date NOT NULL DEFAULT curdate(),
  `plaignant_nom` varchar(150) NOT NULL,
  `plaignant_prenom` varchar(100) DEFAULT NULL,
  `plaignant_telephone` varchar(30) DEFAULT NULL,
  `plaignant_adresse` text DEFAULT NULL,
  `plaignant_email` varchar(150) DEFAULT NULL,
  `plaignant_qualite` enum('personne_physique','personne_morale','administration','autre') DEFAULT 'personne_physique',
  `mis_en_cause_nom` varchar(200) DEFAULT NULL,
  `mis_en_cause_adresse` text DEFAULT NULL,
  `nature_plainte` varchar(255) NOT NULL,
  `description_faits` text DEFAULT NULL,
  `lieu_faits` varchar(255) DEFAULT NULL,
  `date_faits` date DEFAULT NULL,
  `pieces_jointes` varchar(255) DEFAULT NULL COMMENT 'Chemin fichier joint',
  `statut` enum('deposee','en_examen','transmise_pv','classee','irrecevable') NOT NULL DEFAULT 'deposee',
  `pv_id` int(11) DEFAULT NULL COMMENT 'PV créé suite à la plainte',
  `motif_classement` text DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL COMMENT 'Substitut chargé',
  `observations` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestion des plaintes reçues au parquet';

--
-- Déchargement des données de la table `plaintes`
--

INSERT INTO `plaintes` (`id`, `numero_plainte`, `date_plainte`, `date_reception`, `plaignant_nom`, `plaignant_prenom`, `plaignant_telephone`, `plaignant_adresse`, `plaignant_email`, `plaignant_qualite`, `mis_en_cause_nom`, `mis_en_cause_adresse`, `nature_plainte`, `description_faits`, `lieu_faits`, `date_faits`, `pieces_jointes`, `statut`, `pv_id`, `motif_classement`, `substitut_id`, `observations`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PLT-2026-0001', '2026-04-19', '2026-04-19', 'DIALLO', 'MOUSSA', '99999999', 'NIAMEY', '', 'personne_physique', 'ALI', 'BOBIEL', 'VOL ESCROQUERIE', '', '', '2026-04-15', 'uploads/plaintes/plt_6e6469c4d5aa52b6_1776588283.pdf', 'deposee', NULL, NULL, NULL, '', 11, '2026-04-19 08:44:43', '2026-04-19 08:44:43');

-- --------------------------------------------------------

--
-- Structure de la table `primo_intervenants`
--

CREATE TABLE `primo_intervenants` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `primo_intervenants`
--

INSERT INTO `primo_intervenants` (`id`, `nom`, `type`, `description`, `actif`) VALUES
(1, 'Unité Spéciale de la Police', 'Police', 'DGPN — Unité Spéciale', 1),
(2, 'Forces Armées Nigériennes', 'Armée', 'Forces Armées du Niger', 1),
(3, 'Opération Damissa', 'Inter-forces', 'Opération sécuritaire inter-forces', 1),
(4, 'Garde Nationale du Niger', 'Gendarmerie', 'Garde Nationale — missions sécuritaires', 1),
(5, 'Gendarmerie Nationale', 'Gendarmerie', 'Gendarmerie Nationale du Niger', 1),
(6, 'Direction de la Surveillance du Territoire', 'Renseignement', 'DST — services de renseignement', 1),
(7, 'Police Judiciaire', 'Police', 'Brigade de Police Judiciaire', 1);

-- --------------------------------------------------------

--
-- Structure de la table `pv`
--

CREATE TABLE `pv` (
  `id` int(11) NOT NULL,
  `numero_pv` varchar(100) NOT NULL,
  `numero_rg` varchar(60) NOT NULL,
  `unite_enquete_id` int(11) DEFAULT NULL,
  `date_pv` date NOT NULL,
  `date_reception` date NOT NULL,
  `type_affaire` enum('droit_commun_mineur','droit_commun_majeur','pole_antiterro_mineur','pole_antiterro_majeur','pole_economique','civile','penale','commerciale') NOT NULL DEFAULT 'droit_commun_majeur',
  `infraction_id` int(11) DEFAULT NULL,
  `qualification_substitut_id` int(11) DEFAULT NULL COMMENT 'Qualification retenue par le substitut',
  `qualification_details` text DEFAULT NULL COMMENT 'Précisions sur la qualification (complicité, circonstances aggravantes…)',
  `lois_applicables` text DEFAULT NULL COMMENT 'Références légales applicables, saisies uniquement par le substitut',
  `est_antiterroriste` tinyint(1) DEFAULT 0,
  `region_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `description_faits` text DEFAULT NULL,
  `statut` enum('nouveau','recu','en_traitement','classe','transfere','transfere_instruction','transfere_jugement_direct') NOT NULL DEFAULT 'recu',
  `motif_classement` text DEFAULT NULL,
  `date_classement` date DEFAULT NULL,
  `motif_declassement` text DEFAULT NULL,
  `date_declassement` date DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL,
  `date_affectation_substitut` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `numero_rp` varchar(60) DEFAULT NULL COMMENT 'Registre du Parquet (saisi manuellement)',
  `numero_ordre` varchar(60) DEFAULT NULL COMMENT 'Numéro d ordre du PV (saisi manuellement)',
  `mode_poursuite` enum('RI','CD','FD','CRPC','autre') DEFAULT NULL COMMENT 'Mode de poursuite décidé par le substitut'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pv`
--

INSERT INTO `pv` (`id`, `numero_pv`, `numero_rg`, `unite_enquete_id`, `date_pv`, `date_reception`, `type_affaire`, `infraction_id`, `qualification_substitut_id`, `qualification_details`, `lois_applicables`, `est_antiterroriste`, `region_id`, `departement_id`, `commune_id`, `description_faits`, `statut`, `motif_classement`, `date_classement`, `motif_declassement`, `date_declassement`, `substitut_id`, `date_affectation_substitut`, `created_by`, `created_at`, `updated_at`, `numero_rp`, `numero_ordre`, `mode_poursuite`) VALUES
(1, '234/2026', 'RG N°001/2026/TGI-NY', 4, '2026-04-17', '2026-04-17', 'penale', NULL, NULL, NULL, NULL, 1, 6, 37, 14, '', 'transfere_instruction', NULL, NULL, NULL, NULL, 5, '2026-04-17', 1, '2026-04-17 16:05:28', '2026-04-17 16:09:56', NULL, NULL, NULL),
(2, '239/2026', 'RG N°003/2026/TGI-NY', 5, '2026-04-17', '2026-04-17', 'penale', NULL, NULL, NULL, NULL, 1, 6, 46, 17, 'test', 'transfere_instruction', NULL, NULL, NULL, NULL, 6, '2026-04-17', 1, '2026-04-17 17:02:43', '2026-04-17 20:53:43', NULL, NULL, NULL),
(3, '247/2026', 'RG N°005/2026/TGI-NY', 6, '2026-04-17', '2026-04-17', 'penale', 30, NULL, NULL, NULL, 0, NULL, NULL, NULL, '', 'transfere_instruction', NULL, NULL, NULL, NULL, 7, '2026-04-17', 1, '2026-04-17 21:00:34', '2026-04-17 21:01:24', NULL, NULL, NULL),
(4, '234/2026', 'RG N°007/2026/TGI-NY', 6, '2026-04-19', '2026-04-19', 'pole_antiterro_majeur', 23, NULL, NULL, NULL, 1, 6, 54, 198, '8989', 'en_traitement', NULL, NULL, NULL, NULL, 5, '2026-04-22', 11, '2026-04-19 08:45:58', '2026-04-22 16:10:32', '99', '007', NULL),
(5, 'PV N°504', 'RG N°008/2026/TGI-NY', 8, '2025-03-26', '2024-04-22', 'droit_commun_majeur', 17, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'ESCROQUERIE FAUX ET USAGE DE FAUX', 'en_traitement', NULL, NULL, NULL, NULL, 6, '2026-04-22', 11, '2026-04-22 16:31:59', '2026-04-22 16:39:50', 'TMP01', 'SOUS LE N°055', NULL),
(6, '', 'RG N°009/2026/TGI-NY', NULL, '2026-04-22', '2026-04-22', 'penale', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '', 'en_traitement', NULL, NULL, NULL, NULL, 7, '2026-04-22', 11, '2026-04-22 16:38:16', '2026-04-22 16:56:28', NULL, NULL, NULL),
(7, 'PV N°505', 'RG N°010/2026/TGI-NY', 6, '2026-04-22', '2026-04-22', 'droit_commun_majeur', 32, NULL, NULL, NULL, 0, NULL, NULL, NULL, '', 'en_traitement', NULL, NULL, NULL, NULL, 5, '2026-04-22', 11, '2026-04-22 16:44:58', '2026-04-22 16:59:34', 'TMP02', 'SOUS LE N°056', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `pv_infractions`
--

CREATE TABLE `pv_infractions` (
  `id` int(11) NOT NULL,
  `pv_id` int(11) NOT NULL,
  `infraction_id` int(11) NOT NULL,
  `type` enum('unite','substitut') NOT NULL DEFAULT 'unite' COMMENT 'unite=saisie unité enquête, substitut=qualification substitut',
  `est_complicite` tinyint(1) DEFAULT 0 COMMENT '1 = complicité',
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Infractions multiples par PV (deux niveaux : unité + substitut)';

-- --------------------------------------------------------

--
-- Structure de la table `pv_primo_intervenants`
--

CREATE TABLE `pv_primo_intervenants` (
  `pv_id` int(11) NOT NULL,
  `primo_intervenant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pv_primo_intervenants`
--

INSERT INTO `pv_primo_intervenants` (`pv_id`, `primo_intervenant_id`) VALUES
(1, 2),
(4, 2);

-- --------------------------------------------------------

--
-- Structure de la table `rapports`
--

CREATE TABLE `rapports` (
  `id` int(11) NOT NULL,
  `type` enum('quotidien','hebdomadaire','mensuel','annuel','personnalise') NOT NULL DEFAULT 'quotidien',
  `titre` varchar(255) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `contenu_json` longtext DEFAULT NULL COMMENT 'Données du rapport en JSON',
  `fichier` varchar(255) DEFAULT NULL COMMENT 'Chemin du fichier PDF généré',
  `genere_par` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rapports de fin de journée / périodiques générés depuis le dashboard';

-- --------------------------------------------------------

--
-- Structure de la table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `regions`
--

INSERT INTO `regions` (`id`, `nom`, `code`) VALUES
(1, 'Agadez', '1'),
(2, 'Diffa', '2'),
(3, 'Dosso', '3'),
(4, 'Maradi', '4'),
(5, 'Tahoua', '5'),
(6, 'Tillabéri', '6'),
(7, 'Zinder', '7'),
(8, 'Niamey', '8');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `code`, `libelle`) VALUES
(1, 'admin', 'Administrateur Système'),
(2, 'president', 'Président du Tribunal'),
(3, 'vice_president', 'Vice-Président'),
(4, 'procureur', 'Procureur de la République'),
(5, 'substitut_procureur', 'Substitut du Procureur'),
(6, 'juge_instruction', 'Juge d\'Instruction'),
(7, 'juge_siege', 'Juge du Siège'),
(8, 'greffier', 'Greffier'),
(9, 'avocat', 'Avocat');

-- --------------------------------------------------------

--
-- Structure de la table `salles_audience`
--

CREATE TABLE `salles_audience` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `capacite` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salles_audience`
--

INSERT INTO `salles_audience` (`id`, `nom`, `capacite`, `description`, `actif`) VALUES
(1, 'Grande Salle d\'Assises', 150, 'Salle principale pour les affaires criminelles', 1),
(2, 'Salle Correctionnelle N°1', 80, 'Affaires correctionnelles', 1),
(3, 'Salle Correctionnelle N°2', 80, 'Affaires correctionnelles', 1),
(4, 'Salle Civile', 60, 'Affaires civiles et commerciales', 1),
(5, 'Chambre du Conseil', 20, 'Audiences à huis clos — instruction', 1);

-- --------------------------------------------------------

--
-- Structure de la table `scelles`
--

CREATE TABLE `scelles` (
  `id` int(11) NOT NULL,
  `numero_scelle` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `categorie` enum('arme','drogue','document','argent','electronique','vehicule','autre') NOT NULL,
  `categorie_autre_detail` varchar(200) DEFAULT NULL COMMENT 'Précision si catégorie = autre',
  `description` text NOT NULL,
  `date_depot` date NOT NULL,
  `lieu_conservation` varchar(200) DEFAULT 'Greffe du TGI-NY',
  `observations` text DEFAULT NULL,
  `statut` enum('depose','inventorie','restitue','detruit','confisque') NOT NULL DEFAULT 'depose',
  `date_restitution` date DEFAULT NULL,
  `beneficiaire_restitution` varchar(200) DEFAULT NULL,
  `date_destruction` date DEFAULT NULL,
  `motif_destruction` text DEFAULT NULL,
  `pv_destruction` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `scelles`
--

INSERT INTO `scelles` (`id`, `numero_scelle`, `dossier_id`, `categorie`, `categorie_autre_detail`, `description`, `date_depot`, `lieu_conservation`, `observations`, `statut`, `date_restitution`, `beneficiaire_restitution`, `date_destruction`, `motif_destruction`, `pv_destruction`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SCL-2026-0001', 2, 'drogue', NULL, 'uuuuuuu', '2026-04-18', '', '', 'depose', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-17 23:07:23', '2026-04-17 23:07:23'),
(3, 'SCL-2026-0002', 2, 'drogue', NULL, 'ggggg', '2026-04-19', '', '', 'detruit', NULL, NULL, '2026-04-19', 'dddd', NULL, 1, '2026-04-19 06:21:35', '2026-04-19 06:22:32');

-- --------------------------------------------------------

--
-- Structure de la table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Journal de sécurité : connexions, tentatives échouées, actions sensibles';

-- --------------------------------------------------------

--
-- Structure de la table `unites_enquete`
--

CREATE TABLE `unites_enquete` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `type` enum('commissariat','brigade_police','gendarmerie','unite_speciale','autre') NOT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `unites_enquete`
--

INSERT INTO `unites_enquete` (`id`, `nom`, `type`, `commune_id`, `telephone`, `actif`) VALUES
(1, 'Commissariat Central de Niamey', 'commissariat', NULL, '+227 20 73 20 00', 1),
(2, 'Commissariat du 1er Arrondissement', 'commissariat', NULL, '+227 20 73 21 00', 1),
(3, 'Commissariat du 2ème Arrondissement', 'commissariat', NULL, '+227 20 73 22 00', 1),
(4, 'Brigade de Gendarmerie de Niamey', 'gendarmerie', NULL, '+227 20 73 30 00', 1),
(5, 'Brigade Territoriale de Say', 'gendarmerie', NULL, '+227 20 73 31 00', 1),
(6, 'Brigade de Kollo', 'gendarmerie', NULL, '+227 20 73 32 00', 1),
(7, 'Police Judiciaire Niamey', 'brigade_police', NULL, '+227 20 73 25 00', 1),
(8, 'Unité Spéciale Anti-Terrorisme', 'unite_speciale', NULL, '+227 20 73 40 00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `fonction_parquet_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `role_id`, `fonction_parquet_id`, `nom`, `prenom`, `email`, `password`, `telephone`, `matricule`, `actif`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'SYSTÈME', 'Admin', 'admin@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SYS-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(2, 2, NULL, 'MAÏGA', 'Ousmane', 'president@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'PRES-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(3, 3, NULL, 'HASSANE', 'Aminatou', 'vice.president@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'VP-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(4, 4, NULL, 'MOUSSA', 'Ibrahim', 'procureur@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'PROC-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(5, 5, NULL, 'ADAMOU', 'Fatouma', 'substitut1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SUB-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(6, 5, NULL, 'CHAIBOU', 'Moustapha', 'substitut2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SUB-002', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(7, 5, NULL, 'MAHAMADOU', 'Salissou', 'substitut3@tgi-niamey.ne', '$2y$12$6RD5DqKtkTyAJnb8EMAHNONJdSzknzOGRpCKSxdBpsAo0gbBFyxU.', '', 'SUB-003', 1, '2026-04-17 16:04:24', '2026-04-22 16:57:04'),
(8, 6, NULL, 'SAIDOU', 'Aïssatou', 'juge.instr1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JI-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(9, 6, NULL, 'HAMIDOU', 'Mariama', 'juge.instr2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JI-002', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(10, 7, NULL, 'YACOUBA', 'Hassane', 'juge.siege@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JS-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(11, 8, NULL, 'ISSA', 'Rahila', 'greffier@tgi-niamey.ne', '$2y$12$clSJyB3Ayv7Uo8/Og2MKkucpN7ScLqTCzMsiI3D115uFg5.JkIpsS', '', 'GRF-001', 1, '2026-04-17 16:04:24', '2026-04-19 08:42:23'),
(12, 9, NULL, 'MAHAMANE', 'Alio', 'avocat@barreau-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'AVO-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `voies_recours`
--

CREATE TABLE `voies_recours` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `type_recours` enum('appel','cassation','opposition','revision') NOT NULL,
  `demandeur_nom` varchar(200) NOT NULL,
  `demandeur_qualite` enum('prevenu','partie_civile','ministere_public','avocat') DEFAULT NULL,
  `date_declaration` date NOT NULL,
  `juridiction_saisie` varchar(200) DEFAULT NULL,
  `motifs` text DEFAULT NULL,
  `decision_rendue` text DEFAULT NULL,
  `date_decision` date DEFAULT NULL,
  `statut` enum('declare','instruit','juge','irrecevable','desiste') NOT NULL DEFAULT 'declare',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `voies_recours`
--

INSERT INTO `voies_recours` (`id`, `dossier_id`, `jugement_id`, `type_recours`, `demandeur_nom`, `demandeur_qualite`, `date_declaration`, `juridiction_saisie`, `motifs`, `decision_rendue`, `date_decision`, `statut`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 'opposition', 'SANI MOUSSA', 'prevenu', '2026-04-18', 'APPEL NY', '', '', '2026-04-18', 'irrecevable', 1, '2026-04-18 05:46:00', '2026-04-18 05:46:25');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alertes`
--
ALTER TABLE `alertes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`),
  ADD KEY `pv_id` (`pv_id`),
  ADD KEY `destinataire_id` (`destinataire_id`),
  ADD KEY `idx_alertes_user` (`user_id`),
  ADD KEY `idx_alertes_est_lue` (`est_lue`);

--
-- Index pour la table `audiences`
--
ALTER TABLE `audiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salle_id` (`salle_id`),
  ADD KEY `president_id` (`president_id`),
  ADD KEY `greffier_id` (`greffier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_audiences_dossier` (`dossier_id`),
  ADD KEY `idx_audiences_date` (`date_audience`);

--
-- Index pour la table `avocats`
--
ALTER TABLE `avocats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avocat_matricule` (`matricule`);

--
-- Index pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avocat_dossier` (`avocat_id`,`dossier_id`),
  ADD KEY `fk_avdoss_dossier` (`dossier_id`);

--
-- Index pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `juge_id` (`juge_id`);

--
-- Index pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cjc_personne` (`personne_id`),
  ADD KEY `fk_cjc_dossier` (`dossier_id`),
  ADD KEY `fk_cjc_jugement` (`jugement_id`),
  ADD KEY `fk_cjc_created_by` (`created_by`);

--
-- Index pour la table `casier_judiciaire_personnes`
--
ALTER TABLE `casier_judiciaire_personnes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nin` (`nin`),
  ADD KEY `idx_nom` (`nom`);

--
-- Index pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_cr` (`numero_cr`),
  ADD KEY `fk_cr_dossier` (`dossier_id`),
  ADD KEY `fk_cr_created_by` (`created_by`);

--
-- Index pour la table `communes`
--
ALTER TABLE `communes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Index pour la table `communes_geo`
--
ALTER TABLE `communes_geo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nom_dept` (`nom`,`departement_nom`);

--
-- Index pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cj_dossier` (`dossier_id`),
  ADD KEY `fk_cj_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_cj_created_by` (`created_by`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`);

--
-- Index pour la table `detenus`
--
ALTER TABLE `detenus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_ecrou` (`numero_ecrou`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_detenus_dossier` (`dossier_id`),
  ADD KEY `idx_detenus_statut` (`statut`),
  ADD KEY `idx_detenus_maison` (`maison_arret_id`),
  ADD KEY `idx_detenus_jugement` (`jugement_id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pv_id` (`pv_id`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `jugement_id` (`jugement_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_documents_dossier` (`dossier_id`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_rg` (`numero_rg`),
  ADD UNIQUE KEY `numero_rp` (`numero_rp`),
  ADD UNIQUE KEY `numero_ri` (`numero_ri`),
  ADD UNIQUE KEY `uk_numero_ri` (`numero_ri`),
  ADD UNIQUE KEY `uk_numero_rp_dossier` (`numero_rp`),
  ADD KEY `declasse_par` (`declasse_par`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `departement_id` (`departement_id`),
  ADD KEY `commune_id` (`commune_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `juge_siege_id` (`juge_siege_id`),
  ADD KEY `idx_dossiers_statut` (`statut`),
  ADD KEY `idx_dossiers_cabinet` (`cabinet_id`),
  ADD KEY `idx_dossiers_substitut` (`substitut_id`),
  ADD KEY `idx_dossiers_pv` (`pv_id`),
  ADD KEY `idx_type_affaire` (`type_affaire`),
  ADD KEY `idx_dossiers_created_by` (`created_by`);

--
-- Index pour la table `dossier_avocats`
--
ALTER TABLE `dossier_avocats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_dos_avo` (`dossier_id`,`avocat_id`),
  ADD KEY `idx_da_avocat` (`avocat_id`);

--
-- Index pour la table `dossier_pvs`
--
ALTER TABLE `dossier_pvs`
  ADD PRIMARY KEY (`dossier_id`,`pv_id`),
  ADD KEY `idx_dossier_pvs_dossier` (`dossier_id`),
  ADD KEY `idx_dossier_pvs_pv` (`pv_id`);

--
-- Index pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_droit_user_menu` (`user_id`,`menu_id`),
  ADD UNIQUE KEY `uk_droit_user_fonc` (`user_id`,`fonctionnalite_id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `fonctionnalite_id` (`fonctionnalite_id`),
  ADD KEY `accorde_par` (`accorde_par`);

--
-- Index pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exp_dossier` (`dossier_id`),
  ADD KEY `fk_exp_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_exp_created_by` (`created_by`);

--
-- Index pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Index pour la table `fonctions_parquet`
--
ALTER TABLE `fonctions_parquet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `infractions`
--
ALTER TABLE `infractions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `jugements`
--
ALTER TABLE `jugements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_jugement` (`numero_jugement`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `greffier_id` (`greffier_id`),
  ADD KEY `redige_par` (`redige_par`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_jugements_dossier` (`dossier_id`);

--
-- Index pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `commune_id` (`commune_id`);

--
-- Index pour la table `mandats`
--
ALTER TABLE `mandats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `detenu_id` (`detenu_id`),
  ADD KEY `partie_id` (`partie_id`),
  ADD KEY `emetteur_id` (`emetteur_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_mandats_dossier` (`dossier_id`),
  ADD KEY `idx_mandats_statut` (`statut`);

--
-- Index pour la table `mec_infractions`
--
ALTER TABLE `mec_infractions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mec_infr_type` (`mec_id`,`infraction_id`,`type`),
  ADD KEY `idx_mec_infractions_mec` (`mec_id`),
  ADD KEY `idx_mec_infractions_infr` (`infraction_id`);

--
-- Index pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `mises_en_cause`
--
ALTER TABLE `mises_en_cause`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mec_pv` (`pv_id`),
  ADD KEY `idx_mec_statut` (`statut`),
  ADD KEY `idx_mec_decision` (`decision_substitut`),
  ADD KEY `fk_mec_user` (`created_by`),
  ADD KEY `idx_mec_created_by` (`created_by`),
  ADD KEY `idx_mec_substitut` (`substitut_id`);

--
-- Index pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_ordonnance` (`numero_ordonnance`),
  ADD KEY `fk_ord_dossier` (`dossier_id`),
  ADD KEY `fk_ord_juge` (`juge_id`),
  ADD KEY `fk_ord_created_by` (`created_by`);

--
-- Index pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle` (`cle`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Index pour la table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`);

--
-- Index pour la table `plaintes`
--
ALTER TABLE `plaintes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_plainte` (`numero_plainte`),
  ADD KEY `idx_plainte_statut` (`statut`),
  ADD KEY `idx_plainte_date` (`date_plainte`),
  ADD KEY `idx_plainte_pv` (`pv_id`),
  ADD KEY `fk_plainte_sub` (`substitut_id`),
  ADD KEY `fk_plainte_user` (`created_by`);

--
-- Index pour la table `primo_intervenants`
--
ALTER TABLE `primo_intervenants`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pv`
--
ALTER TABLE `pv`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_rg` (`numero_rg`),
  ADD UNIQUE KEY `uk_numero_rp` (`numero_rp`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `departement_id` (`departement_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `substitut_id` (`substitut_id`),
  ADD KEY `idx_pv_statut` (`statut`),
  ADD KEY `idx_pv_antiterro` (`est_antiterroriste`),
  ADD KEY `idx_pv_commune` (`commune_id`),
  ADD KEY `idx_pv_unite` (`unite_enquete_id`),
  ADD KEY `fk_pv_infraction` (`infraction_id`),
  ADD KEY `fk_pv_qualification_substitut` (`qualification_substitut_id`),
  ADD KEY `idx_pv_created_by` (`created_by`),
  ADD KEY `idx_pv_substitut` (`substitut_id`),
  ADD KEY `idx_pv_date_reception` (`date_reception`);

--
-- Index pour la table `pv_infractions`
--
ALTER TABLE `pv_infractions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pv_infr_type` (`pv_id`,`infraction_id`,`type`),
  ADD KEY `idx_pv_infractions_pv` (`pv_id`),
  ADD KEY `idx_pv_infractions_infr` (`infraction_id`);

--
-- Index pour la table `pv_primo_intervenants`
--
ALTER TABLE `pv_primo_intervenants`
  ADD PRIMARY KEY (`pv_id`,`primo_intervenant_id`),
  ADD KEY `primo_intervenant_id` (`primo_intervenant_id`);

--
-- Index pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rapports_type` (`type`),
  ADD KEY `idx_rapports_date` (`date_debut`),
  ADD KEY `idx_rapports_user` (`genere_par`);

--
-- Index pour la table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `salles_audience`
--
ALTER TABLE `salles_audience`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `scelles`
--
ALTER TABLE `scelles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_scelle` (`numero_scelle`),
  ADD KEY `fk_sc_dossier` (`dossier_id`),
  ADD KEY `fk_sc_created_by` (`created_by`);

--
-- Index pour la table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seclog_user` (`user_id`),
  ADD KEY `idx_seclog_action` (`action`),
  ADD KEY `idx_seclog_created` (`created_at`);

--
-- Index pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commune_id` (`commune_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fonction_parquet_id` (`fonction_parquet_id`);

--
-- Index pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vr_dossier` (`dossier_id`),
  ADD KEY `fk_vr_jugement` (`jugement_id`),
  ADD KEY `fk_vr_created_by` (`created_by`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alertes`
--
ALTER TABLE `alertes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audiences`
--
ALTER TABLE `audiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `avocats`
--
ALTER TABLE `avocats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `casier_judiciaire_personnes`
--
ALTER TABLE `casier_judiciaire_personnes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `communes`
--
ALTER TABLE `communes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT pour la table `communes_geo`
--
ALTER TABLE `communes_geo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT pour la table `detenus`
--
ALTER TABLE `detenus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `dossier_avocats`
--
ALTER TABLE `dossier_avocats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `fonctions_parquet`
--
ALTER TABLE `fonctions_parquet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `infractions`
--
ALTER TABLE `infractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `jugements`
--
ALTER TABLE `jugements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `mandats`
--
ALTER TABLE `mandats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `mec_infractions`
--
ALTER TABLE `mec_infractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `mises_en_cause`
--
ALTER TABLE `mises_en_cause`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `parties`
--
ALTER TABLE `parties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `plaintes`
--
ALTER TABLE `plaintes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `primo_intervenants`
--
ALTER TABLE `primo_intervenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `pv`
--
ALTER TABLE `pv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `pv_infractions`
--
ALTER TABLE `pv_infractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rapports`
--
ALTER TABLE `rapports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `salles_audience`
--
ALTER TABLE `salles_audience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `scelles`
--
ALTER TABLE `scelles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `alertes`
--
ALTER TABLE `alertes`
  ADD CONSTRAINT `alertes_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertes_ibfk_2` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertes_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alertes_ibfk_4` FOREIGN KEY (`destinataire_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `audiences`
--
ALTER TABLE `audiences`
  ADD CONSTRAINT `audiences_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audiences_ibfk_2` FOREIGN KEY (`salle_id`) REFERENCES `salles_audience` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_3` FOREIGN KEY (`president_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_4` FOREIGN KEY (`greffier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  ADD CONSTRAINT `fk_avdoss_avocat` FOREIGN KEY (`avocat_id`) REFERENCES `avocats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avdoss_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  ADD CONSTRAINT `cabinets_instruction_ibfk_1` FOREIGN KEY (`juge_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  ADD CONSTRAINT `fk_cjc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_jugement` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_personne` FOREIGN KEY (`personne_id`) REFERENCES `casier_judiciaire_personnes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  ADD CONSTRAINT `fk_cr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communes`
--
ALTER TABLE `communes`
  ADD CONSTRAINT `communes_ibfk_1` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  ADD CONSTRAINT `fk_cj_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cj_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cj_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detenus`
--
ALTER TABLE `detenus`
  ADD CONSTRAINT `detenus_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detenus_ibfk_2` FOREIGN KEY (`maison_arret_id`) REFERENCES `maisons_arret` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detenus_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_4` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_5` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_ibfk_1` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_2` FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_3` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets_instruction` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_4` FOREIGN KEY (`declasse_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_5` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_6` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_7` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_9` FOREIGN KEY (`juge_siege_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `dossier_pvs`
--
ALTER TABLE `dossier_pvs`
  ADD CONSTRAINT `fk_dossierpvs_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dossierpvs_pv` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  ADD CONSTRAINT `droits_utilisateurs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_3` FOREIGN KEY (`fonctionnalite_id`) REFERENCES `fonctionnalites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_4` FOREIGN KEY (`accorde_par`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  ADD CONSTRAINT `fk_exp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_exp_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exp_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  ADD CONSTRAINT `fonctionnalites_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `jugements`
--
ALTER TABLE `jugements`
  ADD CONSTRAINT `jugements_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jugements_ibfk_2` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_3` FOREIGN KEY (`greffier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_4` FOREIGN KEY (`redige_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  ADD CONSTRAINT `maisons_arret_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maisons_arret_ibfk_2` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mandats`
--
ALTER TABLE `mandats`
  ADD CONSTRAINT `mandats_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_2` FOREIGN KEY (`detenu_id`) REFERENCES `detenus` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_3` FOREIGN KEY (`partie_id`) REFERENCES `parties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_4` FOREIGN KEY (`emetteur_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mandats_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mec_infractions`
--
ALTER TABLE `mec_infractions`
  ADD CONSTRAINT `fk_mecinf_infr` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mecinf_mec` FOREIGN KEY (`mec_id`) REFERENCES `mises_en_cause` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  ADD CONSTRAINT `membres_audience_ibfk_1` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `membres_audience_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mises_en_cause`
--
ALTER TABLE `mises_en_cause`
  ADD CONSTRAINT `fk_mec_pv` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mec_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  ADD CONSTRAINT `mouvements_dossier_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mouvements_dossier_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD CONSTRAINT `fk_ord_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ord_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ord_juge` FOREIGN KEY (`juge_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  ADD CONSTRAINT `parametres_tribunal_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `parties`
--
ALTER TABLE `parties`
  ADD CONSTRAINT `parties_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plaintes`
--
ALTER TABLE `plaintes`
  ADD CONSTRAINT `fk_plainte_pv` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_plainte_sub` FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_plainte_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `pv`
--
ALTER TABLE `pv`
  ADD CONSTRAINT `fk_pv_infraction` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pv_qualification_substitut` FOREIGN KEY (`qualification_substitut_id`) REFERENCES `infractions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_1` FOREIGN KEY (`unite_enquete_id`) REFERENCES `unites_enquete` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_4` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_6` FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `pv_infractions`
--
ALTER TABLE `pv_infractions`
  ADD CONSTRAINT `fk_pvinf_infr` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pvinf_pv` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pv_primo_intervenants`
--
ALTER TABLE `pv_primo_intervenants`
  ADD CONSTRAINT `pv_primo_intervenants_ibfk_1` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pv_primo_intervenants_ibfk_2` FOREIGN KEY (`primo_intervenant_id`) REFERENCES `primo_intervenants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `scelles`
--
ALTER TABLE `scelles`
  ADD CONSTRAINT `fk_sc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sc_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`);

--
-- Contraintes pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  ADD CONSTRAINT `unites_enquete_ibfk_1` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`fonction_parquet_id`) REFERENCES `fonctions_parquet` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  ADD CONSTRAINT `fk_vr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vr_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vr_jugement` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
