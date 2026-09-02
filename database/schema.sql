-- ============================================================
-- Systeme de Gestion de Pharmacie - Schema de base de donnees
-- Projet Technologies Web 2A - ESPRIT
-- Moteur : MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS pharmacie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacie_db;

-- ------------------------------------------------------------
-- Table : utilisateurs
-- Entite unique portant les 3 roles (Responsable, Pharmacien, Client)
-- ------------------------------------------------------------
CREATE TABLE utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('responsable','pharmacien','client') NOT NULL DEFAULT 'client',
    telephone       VARCHAR(20)  DEFAULT NULL,
    adresse         VARCHAR(255) DEFAULT NULL,
    actif           TINYINT(1)   NOT NULL DEFAULT 1,
    date_creation   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : medicaments
-- ------------------------------------------------------------
CREATE TABLE medicaments (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    nom                     VARCHAR(150) NOT NULL,
    description             TEXT DEFAULT NULL,
    categorie               VARCHAR(100) NOT NULL,
    fabricant               VARCHAR(150) DEFAULT NULL,
    prix                    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock                   INT NOT NULL DEFAULT 0,
    stock_minimum           INT NOT NULL DEFAULT 10,
    date_expiration         DATE DEFAULT NULL,
    necessite_ordonnance    TINYINT(1) NOT NULL DEFAULT 0,
    date_ajout              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : ordonnances
-- ------------------------------------------------------------
CREATE TABLE ordonnances (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    client_id           INT NOT NULL,
    medecin_nom         VARCHAR(150) NOT NULL,
    date_prescription   DATE NOT NULL,
    type                ENUM('nouvelle','renouvellement') NOT NULL DEFAULT 'nouvelle',
    statut              ENUM('en_attente','validee','refusee') NOT NULL DEFAULT 'en_attente',
    commentaire         TEXT DEFAULT NULL,
    valide_par          INT DEFAULT NULL,
    date_validation      TIMESTAMP NULL DEFAULT NULL,
    date_creation       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ord_client   FOREIGN KEY (client_id)  REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_ord_valideur FOREIGN KEY (valide_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table de jointure : ordonnance_medicaments (N,N entre ordonnances et medicaments)
-- ------------------------------------------------------------
CREATE TABLE ordonnance_medicaments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ordonnance_id   INT NOT NULL,
    medicament_id   INT NOT NULL,
    quantite        INT NOT NULL DEFAULT 1,
    posologie       VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_om_ordonnance FOREIGN KEY (ordonnance_id) REFERENCES ordonnances(id) ON DELETE CASCADE,
    CONSTRAINT fk_om_medicament FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : interactions_medicamenteuses
-- ------------------------------------------------------------
CREATE TABLE interactions_medicamenteuses (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    medicament_1_id     INT NOT NULL,
    medicament_2_id     INT NOT NULL,
    description         TEXT NOT NULL,
    niveau_gravite      ENUM('faible','moderee','grave') NOT NULL DEFAULT 'moderee',
    enregistre_par      INT DEFAULT NULL,
    date_enregistrement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_int_med1 FOREIGN KEY (medicament_1_id) REFERENCES medicaments(id) ON DELETE CASCADE,
    CONSTRAINT fk_int_med2 FOREIGN KEY (medicament_2_id) REFERENCES medicaments(id) ON DELETE CASCADE,
    CONSTRAINT fk_int_user FOREIGN KEY (enregistre_par) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : transactions (expeditions / ventes)
-- ------------------------------------------------------------
CREATE TABLE transactions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ordonnance_id   INT DEFAULT NULL,
    client_id       INT NOT NULL,
    pharmacien_id   INT NOT NULL,
    montant_total   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut          ENUM('completee','annulee') NOT NULL DEFAULT 'completee',
    date_transaction TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tr_ordonnance FOREIGN KEY (ordonnance_id) REFERENCES ordonnances(id) ON DELETE SET NULL,
    CONSTRAINT fk_tr_client     FOREIGN KEY (client_id)     REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_tr_pharmacien FOREIGN KEY (pharmacien_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table : transaction_details (N,N entre transactions et medicaments)
-- ------------------------------------------------------------
CREATE TABLE transaction_details (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id  INT NOT NULL,
    medicament_id   INT NOT NULL,
    quantite        INT NOT NULL DEFAULT 1,
    prix_unitaire   DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_td_transaction FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    CONSTRAINT fk_td_medicament  FOREIGN KEY (medicament_id)  REFERENCES medicaments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Donnees de demonstration
-- Mot de passe en clair pour TOUS les comptes de demo : "password123"
-- (hache avec password_hash / PASSWORD_DEFAULT cote PHP)
-- ------------------------------------------------------------
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, adresse) VALUES
('Trabelsi', 'Amine',  'responsable@pharmacie.tn', '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'responsable', '20111222', 'Tunis, Tunisie'),
('Bouazizi', 'Salma',  'pharmacien@pharmacie.tn', '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'pharmacien', '21333444', 'Ariana, Tunisie'),
('Khemiri',  'Youssef','client@pharmacie.tn',     '$2y$10$gNuVhfXbNLBW.NfpvAcbauR8z..RyfSQHvtixW27P8df7oaIMx4XO', 'client', '22555666', 'Sousse, Tunisie');

INSERT INTO medicaments (nom, description, categorie, fabricant, prix, stock, stock_minimum, date_expiration, necessite_ordonnance) VALUES
('Doliprane 1000mg', 'Antalgique et antipyretique', 'Antalgique', 'Sanofi', 5.500, 120, 20, '2027-06-30', 0),
('Amoxicilline 500mg', 'Antibiotique a large spectre', 'Antibiotique', 'Adwya', 8.900, 8, 15, '2026-12-31', 1),
('Aspegic 500mg', 'Anti-inflammatoire et fluidifiant sanguin', 'Anti-inflammatoire', 'Opalia', 6.200, 45, 10, '2027-03-15', 0),
('Ventoline', 'Bronchodilatateur pour asthme', 'Respiratoire', 'GSK', 12.750, 5, 10, '2026-09-30', 1),
('Efferalgan Codeine', 'Antalgique opioide leger', 'Antalgique', 'UPSA', 7.300, 30, 12, '2027-01-20', 1),
-- Lot perime : sert a demontrer le blocage de la delivrance et le rapport des peremptions
('Sirop Toux Enfant', 'Sirop antitussif pediatrique', 'Respiratoire', 'Adwya', 6.400, 18, 5, '2026-08-15', 0);

INSERT INTO interactions_medicamenteuses (medicament_1_id, medicament_2_id, description, niveau_gravite, enregistre_par) VALUES
(2, 3, 'Risque accru de saignement en cas d association Amoxicilline et Aspegic chez les patients sensibles.', 'moderee', 2),
(3, 5, 'Association deconseillee : majoration du risque hemorragique et de somnolence.', 'grave', 2);
