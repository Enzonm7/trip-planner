-- phpMyAdmin SQL Dump
-- version 5.2.2deb1+jammy3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 29 mai 2026 à 14:30
-- Version du serveur : 8.0.45-0ubuntu0.22.04.1
-- Version de PHP : 8.1.2-1ubuntu2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Box`
--

-- --------------------------------------------------------

--
-- Structure de la table `places`
--

CREATE TABLE `places` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `places`
--

INSERT INTO `places` (`id`, `name`, `latitude`, `longitude`) VALUES
(1, 'Tokyo', 35.6768601, 139.7638947),
(2, 'Osaka', 34.6937569, 135.5014539),
(3, 'Kyoto', 35.0115754, 135.7681441),
(4, 'Hiroshima', 34.3917241, 132.4517589),
(5, 'Nara', 34.6845445, 135.8048359),
(6, 'Kamakura', 35.3192808, 139.5469627),
(7, 'Nikko', 36.7197576, 139.6981390),
(8, 'Paris', 48.8566101, 2.3514992),
(9, 'Lyon', 45.7578137, 4.8320114),
(10, 'Lille', 50.6365654, 3.0635282),
(11, 'Paris', 48.8588897, 2.3200410);

-- --------------------------------------------------------

--
-- Structure de la table `trips`
--

CREATE TABLE `trips` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `visibility` enum('public','private','restricted') DEFAULT 'private',
  `share_token` varchar(64) DEFAULT NULL,
  `total_distance` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `nb_hotels` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trips`
--

INSERT INTO `trips` (`id`, `user_id`, `name`, `visibility`, `share_token`, `total_distance`, `created_at`, `updated_at`, `nb_hotels`) VALUES
(1, 1, 'Japan Tour', 'public', 'tok_public_001', 1850.5, '2026-05-29 09:40:18', '2026-05-29 12:28:22', 5),
(2, 1, 'My Private Trip', 'private', 'tok_private_002', 2100.75, '2026-05-29 09:40:18', '2026-05-29 09:40:18', 1),
(3, 1, 'Shared with user 6', 'restricted', 'tok_restricted_003', 1950, '2026-05-29 09:40:18', '2026-05-29 09:40:18', 1),
(5, 6, 'User6 Private', 'private', 'tok_private_005', 750, '2026-05-29 09:40:18', '2026-05-29 09:40:18', 1);

-- --------------------------------------------------------

--
-- Structure de la table `trip_access`
--

CREATE TABLE `trip_access` (
  `trip_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trip_access`
--

INSERT INTO `trip_access` (`trip_id`, `user_id`) VALUES
(1, 6),
(3, 6);

-- --------------------------------------------------------

--
-- Structure de la table `trip_places`
--

CREATE TABLE `trip_places` (
  `id` int NOT NULL,
  `trip_id` int NOT NULL,
  `place_id` int NOT NULL,
  `position_order` int DEFAULT NULL,
  `is_hotel` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trip_places`
--

INSERT INTO `trip_places` (`id`, `trip_id`, `place_id`, `position_order`, `is_hotel`) VALUES
(8, 2, 1, 1, 0),
(9, 2, 3, 2, 0),
(10, 2, 2, 3, 0),
(11, 2, 4, 4, 0),
(12, 3, 1, 1, 0),
(13, 3, 2, 2, 0),
(14, 3, 3, 3, 0),
(17, 5, 8, 1, 0),
(18, 5, 9, 2, 0),
(41, 1, 10, 1, 0),
(42, 1, 7, 2, 0),
(43, 1, 1, 3, 0),
(44, 1, 3, 4, 0),
(45, 1, 2, 5, 0),
(46, 1, 5, 6, 0),
(47, 1, 11, 7, 0),
(48, 1, 10, 8, 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'test', 'test@test', '$2y$10$GzotCT3.wSsx6E/lTscIre9Ts/0xt2ySaJOi9BcUpHkb6tBcnKCHS', '2026-05-28 12:11:27'),
(6, 'test1', 'test1@test', '$2y$10$QDlQ4u1J29ARSwZqOtaQGeMYMA8hrBjhvDWSZx45VNKllWxiIYwWq', '2026-05-28 13:36:23');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `share_token` (`share_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `trip_access`
--
ALTER TABLE `trip_access`
  ADD PRIMARY KEY (`trip_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `trip_places`
--
ALTER TABLE `trip_places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `place_id` (`place_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `places`
--
ALTER TABLE `places`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `trip_places`
--
ALTER TABLE `trip_places`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `trip_access`
--
ALTER TABLE `trip_access`
  ADD CONSTRAINT `trip_access_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_access_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `trip_places`
--
ALTER TABLE `trip_places`
  ADD CONSTRAINT `trip_places_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_places_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
