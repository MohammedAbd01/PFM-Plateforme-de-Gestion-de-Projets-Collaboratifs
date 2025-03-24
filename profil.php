<?php
require_once 'includes/auth_check.php';
require_once 'classes/Utilisateur.php';

$idUtilisateur = $_SESSION['utilisateur']['id'];
$erreur = '';
$success = '';

// Obtenir les informations de l'utilisateur
$utilisateurObj = new Utilisateur();
$utilisateur = $utilisateurObj->obtenirUtilisateur($idUtilisateur);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmationMotDePasse = $_POST['confirmation_mot_de_passe'] ?? '';
    
    // Valider les données
    if (empty($email)) {
        $erreur = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez entrer une adresse email valide.';
    } elseif ($motDePasse !== $confirmationMotDePasse) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (!empty($motDePasse) && strlen($motDePasse) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Mettre à jour le profil
        $resultat = $utilisateurObj->modifierProfil($idUtilisateur, $email, $motDePasse ?: null);
        
        if ($resultat['success']) {
            $success = $resultat['message'];
            $utilisateur = $utilisateurObj->obtenirUtilisateur($idUtilisateur);
        } else {
            $erreur = $resultat['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="mt-4 mb-4">Mon Profil</h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Informations du Profil</h5>
                </div>
                <div class="card-body">
                    <?php if ($erreur) : ?>
                    <div class="alert alert-danger"><?= $erreur ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success) : ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="profil.php">
                        <div class="mb-3">
                            <label for="nom_utilisateur" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="nom_utilisateur" value="<?= htmlspecialchars($utilisateur['nom_utilisateur']) ?>" disabled>
                            <div class="form-text">Le nom d'utilisateur ne peut pas être modifié.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <input type="text" class="form-control" id="role" value="<?= htmlspecialchars($utilisateur['role']) ?>" disabled>
                            <div class="form-text">Le rôle ne peut pas être modifié.</div>
                        </div>
                        <hr>
                        <h5>Changer le mot de passe</h5>
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe">
                            <div class="form-text">Laissez vide pour conserver votre mot de passe actuel.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmation_mot_de_passe" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="tableau_de_bord.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du Compte</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle" style="font-size: 5rem; color: #0d6efd;"></i>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-person me-2"></i>Nom d'utilisateur:</span>
                            <span class="fw-bold"><?= htmlspecialchars($utilisateur['nom_utilisateur']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-envelope me-2"></i>Email:</span>
                            <span class="fw-bold"><?= htmlspecialchars($utilisateur['email']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-shield me-2"></i>Rôle:</span>
                            <span class="fw-bold">
                                <span class="badge <?= $utilisateur['role'] === 'Administrateur' ? 'bg-danger' : 'bg-info' ?>">
                                    <?= htmlspecialchars($utilisateur['role']) ?>
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Sécurité</h5>
                </div>
                <div class="card-body">
                    <p>
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Assurez-vous d'utiliser un mot de passe fort pour protéger votre compte.
                    </p>
                    <p>
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Changez régulièrement votre mot de passe pour améliorer la sécurité.
                    </p>
                    <p>
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Ne partagez jamais vos identifiants de connexion avec d'autres personnes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 