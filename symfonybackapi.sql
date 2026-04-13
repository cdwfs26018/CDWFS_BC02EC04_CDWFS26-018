-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 13 avr. 2026 à 16:42
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
-- Base de données : `symfonybackapi`
--

-- --------------------------------------------------------

--
-- Structure de la table `adresse`
--

CREATE TABLE `adresse` (
  `id` binary(16) NOT NULL,
  `rue` varchar(255) NOT NULL,
  `ville` varchar(255) NOT NULL,
  `code_postal` varchar(5) NOT NULL,
  `pays` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `adresse`
--

INSERT INTO `adresse` (`id`, `rue`, `ville`, `code_postal`, `pays`) VALUES
(0x099cb4d1b11145ecad8dfc539de078a5, '10 rue de Paris', 'Chartres', '28000', 'FRANCE'),
(0x5a73cfc78cf24f82adc3d11ecc87cf38, '10 rue de Paris', 'Chartres', '28000', 'FRANCE');

-- --------------------------------------------------------

--
-- Structure de la table `chauffeur`
--

CREATE TABLE `chauffeur` (
  `id` binary(16) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chauffeur`
--

INSERT INTO `chauffeur` (`id`, `nom`, `prenom`, `email`, `telephone`) VALUES
(0x2e7d5ab844d64ef8bd4996670ba979fc, 'Dupont', 'Jean', 'user@example.com', '0612345678'),
(0x350cef30078e4f45946935f624c529d9, 'Martin', 'Jean', 'chauffeur1@mail.com', '0600000000');

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id` binary(16) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id`, `nom`, `email`, `telephone`) VALUES
(0xc3b14e73cf294ab9b7f4e44e4d9a8a8d, 'Dupont', 'client1@mail.com', '0600000000');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260413114830', '2026-04-13 11:48:42', 20),
('DoctrineMigrations\\Version20260413122442', '2026-04-13 12:24:50', 840),
('DoctrineMigrations\\Version20260413122710', '2026-04-13 12:27:19', 12),
('DoctrineMigrations\\Version20260413135136', '2026-04-13 13:51:41', 106);

-- --------------------------------------------------------

--
-- Structure de la table `livraison`
--

CREATE TABLE `livraison` (
  `id` binary(16) NOT NULL,
  `heure_prevue` datetime NOT NULL,
  `statut` varchar(255) NOT NULL,
  `tournee_id` binary(16) NOT NULL,
  `client_id` binary(16) NOT NULL,
  `adresse_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livraison`
--

INSERT INTO `livraison` (`id`, `heure_prevue`, `statut`, `tournee_id`, `client_id`, `adresse_id`) VALUES
(0xbd771c7d240d4c159f4082442eacf9a6, '2026-04-15 14:30:00', 'EN_ATTENTE', 0xe75d9f6cc1454836808dc3ae2e15dadb, 0xc3b14e73cf294ab9b7f4e44e4d9a8a8d, 0x5a73cfc78cf24f82adc3d11ecc87cf38);

-- --------------------------------------------------------

--
-- Structure de la table `livraison_marchandise`
--

CREATE TABLE `livraison_marchandise` (
  `id` binary(16) NOT NULL,
  `livraison_id` binary(16) NOT NULL,
  `marchandise_id` binary(16) NOT NULL,
  `quantite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `marchandise`
--

CREATE TABLE `marchandise` (
  `id` binary(16) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `poids` double NOT NULL,
  `volume` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tournee`
--

CREATE TABLE `tournee` (
  `id` binary(16) NOT NULL,
  `date` date NOT NULL,
  `chauffeur_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tournee`
--

INSERT INTO `tournee` (`id`, `date`, `chauffeur_id`) VALUES
(0x54b207b8a6264af6b061f44b15f5123b, '2026-04-13', 0x350cef30078e4f45946935f624c529d9),
(0xe75d9f6cc1454836808dc3ae2e15dadb, '2026-04-13', 0x350cef30078e4f45946935f624c529d9);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` binary(16) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `chauffeur_id` binary(16) DEFAULT NULL,
  `client_id` binary(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `chauffeur_id`, `client_id`) VALUES
(0x5469d511cc5d43aa8583af21800a3ddf, 'user@example.com', '[\"ROLE_CHAUFFEUR\"]', '$2y$13$aIUGTzeuuYx7aU3WRlLIduKf1HzXAITmQ1RxzSkmktOmlikwLr1PG', 0x2e7d5ab844d64ef8bd4996670ba979fc, NULL),
(0x5da0e9f945404e7fa59b4d0d124fdaa5, 'admin@mail.com', '[\"ROLE_ADMIN\"]', '$2y$13$Eo5jzXTOsM1JPgR3TUaqM.LHUvg4FfwP49E5U4CZqWBed3tD6RT9i', NULL, NULL),
(0x6978f3a42e1c4ec0ad1ea4003e2b62da, 'client1@mail.com', '[\"ROLE_CLIENT\"]', '$2y$13$OUh3NVesXSM5tszJVE3l9.PLpJpuOiD8PPocMI9Ly1zr50nYwNlhq', NULL, 0xc3b14e73cf294ab9b7f4e44e4d9a8a8d),
(0x96a77748b9da4dde92fabf0356716812, 'chauffeur1@mail.com', '[\"ROLE_CHAUFFEUR\"]', '$2y$13$7c7M5JihlitVohIcH3oQFeQr1tZI7Z7ROcGphEKNScl21iUzPLQpy', 0x350cef30078e4f45946935f624c529d9, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adresse`
--
ALTER TABLE `adresse`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chauffeur`
--
ALTER TABLE `chauffeur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_5CA777B8E7927C74` (`email`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_C7440455E7927C74` (`email`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `livraison`
--
ALTER TABLE `livraison`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A60C9F1FF661D013` (`tournee_id`),
  ADD KEY `IDX_A60C9F1F19EB6921` (`client_id`),
  ADD KEY `IDX_A60C9F1F4DE7DC5C` (`adresse_id`);

--
-- Index pour la table `livraison_marchandise`
--
ALTER TABLE `livraison_marchandise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3EF4AFD78E54FB25` (`livraison_id`),
  ADD KEY `IDX_3EF4AFD7F7FBEBE` (`marchandise_id`);

--
-- Index pour la table `marchandise`
--
ALTER TABLE `marchandise`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tournee`
--
ALTER TABLE `tournee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EBF67D7E85C0B3BE` (`chauffeur_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD UNIQUE KEY `UNIQ_8D93D64985C0B3BE` (`chauffeur_id`),
  ADD UNIQUE KEY `UNIQ_8D93D64919EB6921` (`client_id`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `livraison`
--
ALTER TABLE `livraison`
  ADD CONSTRAINT `FK_A60C9F1F19EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  ADD CONSTRAINT `FK_A60C9F1F4DE7DC5C` FOREIGN KEY (`adresse_id`) REFERENCES `adresse` (`id`),
  ADD CONSTRAINT `FK_A60C9F1FF661D013` FOREIGN KEY (`tournee_id`) REFERENCES `tournee` (`id`);

--
-- Contraintes pour la table `livraison_marchandise`
--
ALTER TABLE `livraison_marchandise`
  ADD CONSTRAINT `FK_3EF4AFD78E54FB25` FOREIGN KEY (`livraison_id`) REFERENCES `livraison` (`id`),
  ADD CONSTRAINT `FK_3EF4AFD7F7FBEBE` FOREIGN KEY (`marchandise_id`) REFERENCES `marchandise` (`id`);

--
-- Contraintes pour la table `tournee`
--
ALTER TABLE `tournee`
  ADD CONSTRAINT `FK_EBF67D7E85C0B3BE` FOREIGN KEY (`chauffeur_id`) REFERENCES `chauffeur` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D64919EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  ADD CONSTRAINT `FK_8D93D64985C0B3BE` FOREIGN KEY (`chauffeur_id`) REFERENCES `chauffeur` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
