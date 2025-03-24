<?php
require_once 'includes/protection.php';
require_once 'classes/Projet.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Vérifier si l'ID du projet est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: tableau_de_bord.php');
    exit;
}

$id_projet = intval($_GET['id']);

// Initialiser l'objet projet
$projet_obj = new Projet();

// Vérifier si l'utilisateur est propriétaire du projet
if (!$projet_obj->estProprietaire($id_projet, $_SESSION['utilisateur_id']) && $_SESSION['utilisateur_role'] !== 'Administrateur') {
    header('Location: tableau_de_bord.php?message=Vous n\'avez pas le droit de modifier ce projet.&type=danger');
    exit;
}

// Récupérer les informations du projet
$resultat_projet = $projet_obj->getProjetParId($id_projet);
if (!$resultat_projet['succes']) {
    header('Location: tableau_de_bord.php?message=Projet introuvable.&type=danger');
    exit;
}
$projet = $resultat_projet['projet'];

// Initialiser les variables
$message = '';
$type_message = '';

// Traiter le formulaire de mise à jour du projet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_projet = trim($_POST['nom_projet']);
    $description = trim($_POST['description']);
    
    // Valider les données
    if (empty($nom_projet)) {
        $message = 'Le nom du projet est obligatoire.';
        $type_message = 'danger';
    } else {
        // Mettre à jour le projet
        $resultat = $projet_obj->mettreAJourProjet($id_projet, $nom_projet, $description);
        
        if ($resultat['succes']) {
            header('Location: projet.php?id=' . $id_projet . '&message=Projet mis à jour avec succès.&type=success');
            exit;
        } else {
            $message = $resultat['message'];
            $type_message = 'danger';
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Éditer le projet</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $type_message; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="editer_projet.php?id=<?php echo $id_projet; ?>">
                        <div class="mb-3">
                            <label for="nom_projet" class="form-label">Nom du projet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom_projet" name="nom_projet" value="<?php echo htmlspecialchars($projet['nom_projet']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($projet['description']); ?></textarea>
                            <div class="form-text">Décrivez brièvement votre projet, ses objectifs et ce que vous souhaitez accomplir.</div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Mettre à jour le projet</button>
                            <a href="projet.php?id=<?php echo $id_projet; ?>" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Option de suppression du projet -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Zone dangereuse</h5>
                </div>
                <div class="card-body">
                    <h5>Supprimer le projet</h5>
                    <p>Cette action est irréversible. Toutes les tâches, messages et fichiers associés à ce projet seront définitivement supprimés.</p>
                    <a href="#" onclick="return confirmerSuppression('Êtes-vous absolument sûr de vouloir supprimer ce projet et toutes ses données associées? Cette action est irréversible.', 'supprimer_projet.php?id=<?php echo $id_projet; ?>')" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Supprimer ce projet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer
include 'includes/footer.php';
?>
