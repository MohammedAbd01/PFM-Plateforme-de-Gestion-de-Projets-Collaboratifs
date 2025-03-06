# 📌 Plateforme de Gestion de Projets Collaboratifs

## 📖 Description
Ce projet est une plateforme web permettant aux **professionnels et étudiants** de **gérer leurs projets en ligne**, d'**assigner des tâches** et de **collaborer efficacement**.

---

## 🚀 Fonctionnalités

✔️ **Gestion des utilisateurs** : Inscription, connexion, modification du profil  
✔️ **Gestion des projets** : Création, mise à jour, suppression, invitation de membres  
✔️ **Gestion des tâches** : Attribution, suivi du statut (_À faire, En cours, Terminé_)  
✔️ **Messagerie interne** : Communication entre membres des projets  
✔️ **Partage de documents** : Ajout et téléchargement de fichiers liés au projet  
✔️ **Tableau de bord** : Suivi des projets et statistiques de collaboration  

---

## 🛠️ Technologies utilisées

- **Frontend** : HTML, CSS, Bootstrap, JavaScript  
- **Backend** : PHP (avec PDO pour la gestion des requêtes SQL sécurisées)  
- **Base de données** : MySQL  

---

## 📌 Conception UML et Base de Données

### 1️⃣ Cas d'Utilisation (Use Case)
**Acteurs principaux** :  
- **Utilisateur** (_Étudiant / Professionnel_)  
- **Administrateur**  

**Principales fonctionnalités** :  
- Gestion des utilisateurs  
- Gestion des projets  
- Gestion des tâches  
- Messagerie interne  
- Partage de documents  
- Tableau de bord  

---

## 📌 Pages nécessaires

### 1️⃣ Pages d'authentification
- `register.php` – Inscription  
- `login.php` – Connexion  
- `forgot_password.php` – Mot de passe oublié  

### 2️⃣ Tableau de bord
- `dashboard.php` – Vue globale des projets et tâches  

### 3️⃣ Gestion des projets
- `projects.php` – Liste des projets  
- `create_project.php` – Création d’un projet  
- `project_details.php` – Détail d’un projet  

### 4️⃣ Gestion des tâches
- `task_form.php` – Ajouter/modifier une tâche  
- `tasks.php` – Liste des tâches d’un projet  

### 5️⃣ Communication
- `messages.php` – Messagerie interne  

### 6️⃣ Gestion des documents
- `documents.php` – Upload et affichage des fichiers  

### 7️⃣ Administration
- `admin_dashboard.php` – Gestion des utilisateurs et projets  

---

## 🎯 Installation et Configuration

### 1️⃣ Cloner le projet
```bash
git clone [https://github.com/votre-repo.git](https://github.com/MohammedAbd01/PFM-Plateforme-de-Gestion-de-Projets-Collaboratifs.git]
cd votre-repo
