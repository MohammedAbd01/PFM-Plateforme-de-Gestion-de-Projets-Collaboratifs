-- Script de création de la base de données et des tables pour la plateforme de projets
-- Réinitialiser la base de données si elle existe déjà
DROP DATABASE IF EXISTS plateforme_projets;
CREATE DATABASE plateforme_projets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plateforme_projets;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('Utilisateur', 'Administrateur') NOT NULL DEFAULT 'Utilisateur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des projets
CREATE TABLE projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_projet VARCHAR(100) NOT NULL,
    description TEXT,
    id_proprietaire INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proprietaire) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des membres du projet
CREATE TABLE membres_projet (
    id_projet INT NOT NULL,
    id_utilisateur INT NOT NULL,
    statut ENUM('en_attente', 'accepte') NOT NULL DEFAULT 'en_attente',
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_projet, id_utilisateur),
    FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des tâches
CREATE TABLE taches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT NOT NULL,
    nom_tache VARCHAR(100) NOT NULL,
    description TEXT,
    assigne_a INT,
    statut ENUM('À faire', 'En cours', 'Terminé') NOT NULL DEFAULT 'À faire',
    date_echeance DATE NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (assigne_a) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT NOT NULL,
    id_expediteur INT NOT NULL,
    id_destinataire INT NULL, -- NULL signifie un message pour tous les membres
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (id_expediteur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_destinataire) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des fichiers
CREATE TABLE fichiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_projet INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    date_televersement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insérer un utilisateur administrateur par défaut
-- Mot de passe: admin123 (à changer en production)
INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) 
VALUES ('admin', 'admin@example.com', '$2y$10$fCOiMky9jHKq.pkE5vDDGeLIl1DnO.K6YkCKnB5L9rOYQBbBmJzjW', 'Administrateur');

-- Insérer quelques utilisateurs de test
INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) 
VALUES ('jean', 'jean@example.com', '$2y$10$fCOiMky9jHKq.pkE5vDDGeLIl1DnO.K6YkCKnB5L9rOYQBbBmJzjW', 'Utilisateur');

INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) 
VALUES ('marie', 'marie@example.com', '$2y$10$fCOiMky9jHKq.pkE5vDDGeLIl1DnO.K6YkCKnB5L9rOYQBbBmJzjW', 'Utilisateur'); 