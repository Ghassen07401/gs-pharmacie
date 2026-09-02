-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 02 sep. 2026 à 04:02
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
-- Base de données : `pharmacie_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `interactions_medicamenteuses`
--

CREATE TABLE `interactions_medicamenteuses` (
  `id` int(11) NOT NULL,
  `medicament_1_id` int(11) NOT NULL,
  `medicament_2_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `niveau_gravite` enum('faible','moderee','grave') NOT NULL DEFAULT 'moderee',
  `enregistre_par` int(11) DEFAULT NULL,
  `date_enregistrement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `interactions_medicamenteuses`
--

INSERT INTO `interactions_medicamenteuses` (`id`, `medicament_1_id`, `medicament_2_id`, `description`, `niveau_gravite`, `enregistre_par`, `date_enregistrement`) VALUES
(1, 2, 3, 'Risque accru de saignement en cas d association Amoxicilline et Aspegic chez les patients sensibles.', 'moderee', 2, '2026-09-01 21:10:17'),
(2, 3, 5, 'Association deconseillee : majoration du risque hemorragique et de somnolence.', 'grave', 2, '2026-09-01 21:10:17');

-- --------------------------------------------------------

--
-- Structure de la table `medicaments`
--

CREATE TABLE `medicaments` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `categorie` varchar(100) NOT NULL,
  `fabricant` varchar(150) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimum` int(11) NOT NULL DEFAULT 10,
  `date_expiration` date DEFAULT NULL,
  `necessite_ordonnance` tinyint(1) NOT NULL DEFAULT 0,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medicaments`
--

INSERT INTO `medicaments` (`id`, `nom`, `description`, `categorie`, `fabricant`, `prix`, `stock`, `stock_minimum`, `date_expiration`, `necessite_ordonnance`, `date_ajout`) VALUES
(1, 'Doliprane 1000mg', 'Antalgique et antipyretique', 'Antalgique', 'Sanofi', 5.50, 120, 20, '2027-06-30', 0, '2026-09-01 21:10:17'),
(2, 'Amoxicilline 500mg', 'Antibiotique a large spectre', 'Antibiotique', 'Adwya', 8.90, 8, 15, '2026-12-31', 1, '2026-09-01 21:10:17'),
(3, 'Aspegic 500mg', 'Anti-inflammatoire et fluidifiant sanguin', 'Anti-inflammatoire', 'Opalia', 6.20, 45, 10, '2027-03-15', 0, '2026-09-01 21:10:17'),
(4, 'Ventoline', 'Bronchodilatateur pour asthme', 'Respiratoire', 'GSK', 12.75, 5, 10, '2026-09-30', 1, '2026-09-01 21:10:17'),
(5, 'Efferalgan Codeine', 'Antalgique opioide leger', 'Antalgique', 'UPSA', 7.30, 30, 12, '2027-01-20', 1, '2026-09-01 21:10:17'),
(6, 'Sirop Toux Enfant', 'Sirop antitussif pediatrique', 'Respiratoire', 'Adwya', 6.40, 18, 5, '2026-08-15', 0, '2026-09-02 01:31:40'),
(7, 'ggg', '', 'Antalgique', '', 1.45, 0, 10, '2026-09-01', 0, '2026-09-02 01:39:30');

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances`
--

CREATE TABLE `ordonnances` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `medecin_nom` varchar(150) NOT NULL,
  `date_prescription` date NOT NULL,
  `type` enum('nouvelle','renouvellement') NOT NULL DEFAULT 'nouvelle',
  `statut` enum('en_attente','validee','refusee') NOT NULL DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  `valide_par` int(11) DEFAULT NULL,
  `date_validation` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ordonnance_medicaments`
--

CREATE TABLE `ordonnance_medicaments` (
  `id` int(11) NOT NULL,
  `ordonnance_id` int(11) NOT NULL,
  `medicament_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `posologie` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `ordonnance_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `pharmacien_id` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `statut` enum('completee','annulee') NOT NULL DEFAULT 'completee',
  `date_transaction` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `medicament_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('responsable','pharmacien','client') NOT NULL DEFAULT 'client',
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `telephone`, `adresse`, `actif`, `date_creation`) VALUES
(1, 'Trabelsi', 'Amine', 'responsable@pharmacie.tn', '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'responsable', '20111222', 'Tunis, Tunisie', 1, '2026-09-01 21:10:17'),
(2, 'Bouazizi', 'Salma', 'pharmacien@pharmacie.tn', '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'pharmacien', '21333444', 'Ariana, Tunisie', 1, '2026-09-01 21:10:17'),
(3, 'Khemiri', 'Youssef', 'client@pharmacie.tn', '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'client', '22555666', 'Sousse, Tunisie', 1, '2026-09-01 21:10:17'),
(4, 'Gasmi', 'Mouhaned', 'mohaned.gasmi@esprit.tn', '$2y$10$Y26RE0f1/q0fR2SHCFa9H.x8seWHwaQOGK0X5p9f05HP7CWhhY0T6', 'client', '21990597', 'Tunis, Tunisie', 1, '2026-09-02 01:52:01');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `interactions_medicamenteuses`
--
ALTER TABLE `interactions_medicamenteuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_int_med1` (`medicament_1_id`),
  ADD KEY `fk_int_med2` (`medicament_2_id`),
  ADD KEY `fk_int_user` (`enregistre_par`);

--
-- Index pour la table `medicaments`
--
ALTER TABLE `medicaments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ord_client` (`client_id`),
  ADD KEY `fk_ord_valideur` (`valide_par`);

--
-- Index pour la table `ordonnance_medicaments`
--
ALTER TABLE `ordonnance_medicaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_om_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_om_medicament` (`medicament_id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tr_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_tr_client` (`client_id`),
  ADD KEY `fk_tr_pharmacien` (`pharmacien_id`);

--
-- Index pour la table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_td_transaction` (`transaction_id`),
  ADD KEY `fk_td_medicament` (`medicament_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `interactions_medicamenteuses`
--
ALTER TABLE `interactions_medicamenteuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `medicaments`
--
ALTER TABLE `medicaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ordonnance_medicaments`
--
ALTER TABLE `ordonnance_medicaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `interactions_medicamenteuses`
--
ALTER TABLE `interactions_medicamenteuses`
  ADD CONSTRAINT `fk_int_med1` FOREIGN KEY (`medicament_1_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_int_med2` FOREIGN KEY (`medicament_2_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_int_user` FOREIGN KEY (`enregistre_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD CONSTRAINT `fk_ord_client` FOREIGN KEY (`client_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ord_valideur` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ordonnance_medicaments`
--
ALTER TABLE `ordonnance_medicaments`
  ADD CONSTRAINT `fk_om_medicament` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_om_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tr_client` FOREIGN KEY (`client_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tr_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tr_pharmacien` FOREIGN KEY (`pharmacien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `fk_td_medicament` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_td_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
