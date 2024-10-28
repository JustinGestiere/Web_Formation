-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 28 oct. 2024 à 16:10
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
-- Base de données : `web_formation`
--

-- --------------------------------------------------------

--
-- Structure de la table `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `emploi_du_temps`
--

CREATE TABLE `emploi_du_temps` (
  `id` int(11) NOT NULL,
  `matiere_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `professeur_id` int(11) DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `matieres`
--

CREATE TABLE `matieres` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sign`
--

CREATE TABLE `sign` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `emploi_du_temps_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenoms` varchar(255) NOT NULL,
  `emails` varchar(255) NOT NULL,
  `ages` tinyint(3) NOT NULL,
  `passwords` varchar(255) NOT NULL,
  `roles` varchar(50) NOT NULL,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenoms`, `emails`, `ages`, `passwords`, `roles`, `classe_id`) VALUES
(1, 'GESTIERE', 'Justin', 'justin.gestiere@gmail.com', 20, '$2y$10$6ChLdm4z.Q6eMKMeHzdy1OlW74MEP98/7X/lzYJKw.U0jfHwi17hW', 'administrateur', 0),
(2, 'RIPOCHE', 'Matthis', 'matthis.ripoche@gmail.com', 23, '$2y$10$pQgQJtlpr1oHPymjp0y0Ruwg9NuBHGBro4DIeduIN9.bVKmUdQ4Vu', 'eleve', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matiere_id` (`matiere_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `professeur_id` (`professeur_id`);

--
-- Index pour la table `matieres`
--
ALTER TABLE `matieres`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sign`
--
ALTER TABLE `sign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `emploi_du_temps_id` (`emploi_du_temps_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emails` (`emails`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sign`
--
ALTER TABLE `sign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  ADD CONSTRAINT `emploi_du_temps_ibfk_1` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`),
  ADD CONSTRAINT `emploi_du_temps_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`),
  ADD CONSTRAINT `emploi_du_temps_ibfk_3` FOREIGN KEY (`professeur_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `sign`
--
ALTER TABLE `sign`
  ADD CONSTRAINT `sign_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sign_ibfk_2` FOREIGN KEY (`emploi_du_temps_id`) REFERENCES `emploi_du_temps` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
