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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomUtilisateur = $_POST['nom_utilisateur'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    
    // Valider les données
    if (empty($nomUtilisateur) || empty($motDePasse)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        // Connecter l'utilisateur
        $utilisateur = new Utilisateur();
        $resultat = $utilisateur->connecter($nomUtilisateur, $motDePasse);
        
        if ($resultat['success']) {
            // Rediriger en fonction du rôle
            $destination = $resultat['role'] === 'Administrateur' ? 'administration.php' : 'tableau_de_bord.php';
            header("Location: $destination");
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
                <div class="col-md-6 d-none d-md-block" data-aos="fade-right">
                    <div class="h-100 d-flex align-items-center justify-content-center p-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <div class="text-center text-white p-4">
                        <img src="logo_collabsphere-r.png" alt="CollabSphere Logo" style="height: 80px;">
                            <h2 class="mt-4 mb-3">Bienvenue sur CollabSphere</h2>
                            <p class="mb-4">Connectez-vous pour accéder à votre tableau de bord et gérer vos projets.</p>
                            <div class="d-flex justify-content-center">
                                <div class="px-3">
                                    <i class="bi bi-graph-up" style="font-size: 2.5rem;"></i>
                                    <p class="mt-2">Suivi</p>
                                </div>
                                <div class="px-3">
                                    <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                                    <p class="mt-2">Collaboration</p>
                                </div>
                                <div class="px-3">
                                    <i class="bi bi-check2-circle" style="font-size: 2.5rem;"></i>
                                    <p class="mt-2">Productivité</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 bg-white" data-aos="fade-left">
                    <div class="p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Connexion</h3>
                            <p class="text-muted">Entrez vos identifiants pour vous connecter</p>
                        </div>
                        
                        <?php if ($erreur) : ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $erreur ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" action="connexion.php" class="mt-4">
                            <div class="mb-4">
                                <label for="nom_utilisateur" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="nom_utilisateur" name="nom_utilisateur" value="<?= htmlspecialchars($nomUtilisateur) ?>" placeholder="Entrez votre nom d'utilisateur" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="mot_de_passe" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                </button>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0">Pas encore inscrit? <a href="inscription.php" class="text-decoration-none fw-bold">Créer un compte</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 