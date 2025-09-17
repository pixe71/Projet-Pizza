-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 17 sep. 2025 à 20:52
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
-- Base de données : `pizza_truck`
--

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `creneau_id` int(11) DEFAULT NULL,
  `statut` enum('en_attente','confirmee','prete','terminee','annulee') DEFAULT 'en_attente',
  `total` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `user_id`, `creneau_id`, `statut`, `total`, `created_at`) VALUES
(1, 2, 1, 'terminee', 54.00, '2025-09-12 17:56:22'),
(2, 2, 1, 'terminee', 43.00, '2025-09-12 18:12:12'),
(3, 2, 2, 'terminee', 55.00, '2025-09-12 18:13:43'),
(4, 2, 1, 'terminee', 12.00, '2025-09-12 18:16:15'),
(5, 2, 3, 'terminee', 85.50, '2025-09-12 18:53:38'),
(6, 2, 4, 'terminee', 12.00, '2025-09-12 19:28:40'),
(7, 2, 4, 'en_attente', 12.00, '2025-09-12 19:58:56'),
(8, 2, 4, 'en_attente', 12.00, '2025-09-12 23:48:56'),
(9, 2, 1, 'terminee', 15.50, '2025-09-13 15:47:41'),
(10, 2, 1, 'annulee', 12.00, '2025-09-13 15:49:30'),
(11, 2, 2, 'en_attente', 12.00, '2025-09-13 15:51:12'),
(12, 2, 2, 'terminee', 12.00, '2025-09-13 15:51:35'),
(13, 2, 2, 'en_attente', 24.00, '2025-09-13 18:31:37');

-- --------------------------------------------------------

--
-- Structure de la table `commande_details`
--

CREATE TABLE `commande_details` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) DEFAULT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_details`
--

INSERT INTO `commande_details` (`id`, `commande_id`, `pizza_id`, `quantite`, `prix_unitaire`) VALUES
(1, 1, 1, 1, 12.00),
(2, 1, 2, 3, 14.00),
(3, 2, 1, 1, 12.00),
(4, 2, 4, 2, 15.50),
(5, 3, 1, 2, 12.00),
(6, 3, 4, 2, 15.50),
(7, 4, 1, 1, 12.00),
(8, 5, 1, 1, 12.00),
(9, 5, 2, 2, 14.00),
(10, 5, 3, 2, 15.00),
(11, 5, 4, 1, 15.50),
(12, 6, 1, 1, 12.00),
(13, 7, 1, 1, 12.00),
(14, 8, 1, 1, 12.00),
(15, 9, 4, 1, 15.50),
(16, 10, 1, 1, 12.00),
(17, 11, 1, 1, 12.00),
(18, 12, 1, 1, 12.00),
(19, 13, 1, 2, 12.00);

-- --------------------------------------------------------

--
-- Structure de la table `creneaux`
--

CREATE TABLE `creneaux` (
  `id` int(11) NOT NULL,
  `date_creneau` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `max_pizzas` int(11) DEFAULT 20,
  `pizzas_commandees` int(11) DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `creneaux`
--

INSERT INTO `creneaux` (`id`, `date_creneau`, `heure_debut`, `heure_fin`, `max_pizzas`, `pizzas_commandees`, `actif`) VALUES
(1, '2025-09-13', '19:00:00', '19:15:00', 10, 10, 1),
(2, '2025-09-14', '20:00:00', '20:15:00', 10, 8, 1),
(3, '2025-09-12', '20:50:00', '21:00:00', 10, 6, 1),
(4, '2025-09-12', '21:27:00', '21:30:00', 5, 3, 1);

-- --------------------------------------------------------

--
-- Structure de la table `pizzas`
--

CREATE TABLE `pizzas` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(5,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pizzas`
--

INSERT INTO `pizzas` (`id`, `nom`, `description`, `prix`, `image`, `disponible`) VALUES
(1, 'Margherita', 'Tomate, mozzarella, basilic', 12.00, NULL, 1),
(2, 'Regina', 'Tomate, mozzarella, jambon, champignons', 14.00, NULL, 1),
(3, '4 Fromages', 'Mozzarella, gorgonzola, parmesan, chèvre', 15.00, NULL, 1),
(4, 'Chorizo', 'Tomate, mozzarella, chorizo, poivrons', 15.50, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `telephone`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'Admin', 'admin@pizza.com', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-09-12 17:48:42'),
(2, 'Tournie', 'Luc', 'contact@luc-tournie.fr', '0622503811', '$2y$10$do/ZY2rJS0oTKNayf0tFrOAdGWavVES1gxcIbllYNPR0zN3LpGJsu', 'client', '2025-09-12 17:52:54');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `creneau_id` (`creneau_id`);

--
-- Index pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `pizza_id` (`pizza_id`);

--
-- Index pour la table `creneaux`
--
ALTER TABLE `creneaux`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pizzas`
--
ALTER TABLE `pizzas`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `commande_details`
--
ALTER TABLE `commande_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `creneaux`
--
ALTER TABLE `creneaux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `pizzas`
--
ALTER TABLE `pizzas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `commandes_ibfk_2` FOREIGN KEY (`creneau_id`) REFERENCES `creneaux` (`id`);

--
-- Contraintes pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD CONSTRAINT `commande_details_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commande_details_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
