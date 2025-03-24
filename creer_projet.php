<?php
require_once 'includes/auth_check.php';
require_once 'classes/Projet.php';

$idUtilisateur = $_SESSION['utilisateur']['id'];
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomProjet = $_POST['nom_projet'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Valider les données
    if (empty($nomProjet)) {
        $erreur = 'Le nom du projet est obligatoire.';
    } else {
        // Créer le projet
        $projetObj = new Projet();
        $resultat = $projetObj->creer($nomProjet, $description, $idUtilisateur);
        
        if ($resultat['success']) {
            $_SESSION['message'] = 'Projet créé avec succès!';
            $_SESSION['message_type'] = 'success';
            header('Location: projet.php?id=' . $resultat['id_projet']);
            exit;
        } else {
            $erreur = $resultat['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1 class="mt-4 mb-4">Créer un Nouveau Projet</h1>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-plus-square me-2"></i>Informations du Projet</h5>
        </div>
        <div class="card-body">
            <?php if ($erreur) : ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
            <?php endif; ?>
            
            <form method="post" action="creer_projet.php">
                <div class="mb-3">
                    <label for="nom_projet" class="form-label">Nom du projet <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom_projet" name="nom_projet" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    <div class="form-text">Décrivez l'objectif et le contexte de ce projet.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="tableau_de_bord.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Créer le projet
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Conseils pour un Bon Projet</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Donnez un nom clair et concis à votre projet.
                </li>
                <li class="list-group-item">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Incluez un objectif principal et des objectifs secondaires dans la description.
                </li>
                <li class="list-group-item">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Après la création, ajoutez des membres à votre équipe pour collaborer.
                </li>
                <li class="list-group-item">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Divisez le travail en tâches spécifiques avec des échéances claires.
                </li>
                <li class="list-group-item">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Utilisez la messagerie et le partage de fichiers pour faciliter la communication.
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 