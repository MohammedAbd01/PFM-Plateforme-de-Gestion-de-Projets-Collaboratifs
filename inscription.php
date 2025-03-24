<?php
session_start();
require_once 'classes/Utilisateur.php';

// Rediriger vers le tableau de bord si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: tableau_de_bord.php');
    exit;
}

$erreur = '';
$nomUtilisateur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomUtilisateur = $_POST['nom_utilisateur'] ?? '';
    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmationMotDePasse = $_POST['confirmation_mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'Utilisateur';
    
    // Valider les données
    if (empty($nomUtilisateur) || empty($email) || empty($motDePasse) || empty($confirmationMotDePasse)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif ($motDePasse !== $confirmationMotDePasse) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($motDePasse) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez entrer une adresse email valide.';
    } else {
        // Créer un utilisateur
        $utilisateur = new Utilisateur();
        $resultat = $utilisateur->inscrire($nomUtilisateur, $email, $motDePasse, $role);
        
        if ($resultat['success']) {
            $_SESSION['message'] = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
            $_SESSION['message_type'] = 'success';
            header('Location: connexion.php');
            exit;
        } else {
            $erreur = $resultat['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-0 shadow-lg" style="border-radius: var(--border-radius); overflow: hidden;">
                <div class="col-md-6 bg-white order-md-1" data-aos="fade-right">
                    <div class="p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Créer un compte</h3>
                            <p class="text-muted">Rejoignez notre plateforme de gestion de projets</p>
                        </div>
                        
                        <?php if ($erreur) : ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erreur ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" action="inscription.php" class="mt-4">
                            <div class="mb-3">
                                <label for="nom_utilisateur" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="nom_utilisateur" name="nom_utilisateur" value="<?= htmlspecialchars($nomUtilisateur) ?>" placeholder="Choisissez un nom d'utilisateur" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Entrez votre adresse email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="mot_de_passe" name="mot_de_passe" placeholder="Choisissez un mot de passe" required>
                                </div>
                                <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmation_mot_de_passe" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" placeholder="Confirmez votre mot de passe" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </span>
                                    <select class="form-select border-start-0" id="role" name="role">
                                        <option value="Utilisateur" selected>Utilisateur</option>
                                        <option value="Administrateur">Administrateur</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-3">
                                    <i class="bi bi-person-plus-fill me-2"></i>S'inscrire
                                </button>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0">Déjà inscrit? <a href="connexion.php" class="text-decoration-none fw-bold">Se connecter</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6 d-none d-md-block order-md-0" data-aos="fade-left">
                    <div class="h-100 d-flex align-items-center justify-content-center p-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <div class="text-center text-white p-4">
                            <i class="bi bi-person-plus" style="font-size: 4rem;"></i>
                            <h2 class="mt-4 mb-3">Rejoignez-nous</h2>
                            <p class="mb-4">Créez un compte pour accéder à toutes les fonctionnalités de notre plateforme de gestion de projets.</p>
                            <div class="row g-4 mt-3">
                                <div class="col-12">
                                    <div class="p-3" style="background-color: rgba(255, 255, 255, 0.1); border-radius: var(--border-radius);">
                                        <i class="bi bi-check-circle-fill me-2"></i>Tableaux de bord personnalisés
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3" style="background-color: rgba(255, 255, 255, 0.1); border-radius: var(--border-radius);">
                                        <i class="bi bi-check-circle-fill me-2"></i>Gestion de tâches avancée
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3" style="background-color: rgba(255, 255, 255, 0.1); border-radius: var(--border-radius);">
                                        <i class="bi bi-check-circle-fill me-2"></i>Collaboration en temps réel
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 