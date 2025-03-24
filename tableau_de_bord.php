<?php
require_once 'includes/auth_check.php';
require_once 'classes/Projet.php';
require_once 'classes/Tache.php';

$idUtilisateur = $_SESSION['utilisateur']['id'];

// Obtenir les projets de l'utilisateur
$projetObj = new Projet();
$projets = $projetObj->obtenirProjetsUtilisateur($idUtilisateur);

// Obtenir les invitations en attente
$invitations = $projetObj->obtenirInvitationsEnAttente($idUtilisateur);

// Obtenir les tâches assignées à l'utilisateur
$tacheObj = new Tache();
$taches = $tacheObj->obtenirTachesUtilisateur($idUtilisateur);

// Gérer l'acceptation des invitations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accepter_invitation'])) {
    $idProjet = $_POST['id_projet'] ?? 0;
    if ($idProjet) {
        $resultat = $projetObj->accepterInvitation($idProjet, $idUtilisateur);
        if ($resultat['success']) {
            $_SESSION['message'] = 'Invitation acceptée avec succès!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Erreur lors de l\'acceptation de l\'invitation.';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: tableau_de_bord.php');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Tableau de Bord</h1>
    
    <?php if (count($invitations) > 0) : ?>
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Invitations en attente</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($invitations as $invitation) : ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($invitation['nom_projet']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">Propriétaire: <?= htmlspecialchars($invitation['proprietaire_nom']) ?></small>
                            </p>
                            <form method="post" action="tableau_de_bord.php">
                                <input type="hidden" name="id_projet" value="<?= $invitation['id'] ?>">
                                <button type="submit" name="accepter_invitation" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Accepter l'invitation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-kanban me-2"></i>Mes Projets</h5>
                </div>
                <div class="card-body">
                    <?php if (count($projets) > 0) : ?>
                    <div class="row">
                        <?php foreach ($projets as $projet) : ?>
                        <div class="col-md-6 mb-4">
                            <div class="card project-card">
                                <div class="card-header">
                                    <?= htmlspecialchars($projet['nom_projet']) ?>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <?= nl2br(htmlspecialchars($projet['description'])) ?>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>Propriétaire: <?= htmlspecialchars($projet['proprietaire_nom']) ?>
                                        </small><br>
                                        <small class="text-muted">
                                            <i class="bi bi-list-check me-1"></i>Tâches: <?= $projet['nombre_taches'] ?? 0 ?>
                                        </small>
                                    </p>
                                    
                                    <?php
                                    // Calculer le score du projet
                                    $score = $projetObj->calculerScore($projet['id']);
                                    $scoreClass = '';
                                    if ($score < 30) {
                                        $scoreClass = 'bg-danger';
                                    } elseif ($score < 70) {
                                        $scoreClass = 'bg-warning';
                                    } else {
                                        $scoreClass = 'bg-success';
                                    }
                                    ?>
                                    
                                    <div class="progress mb-3">
                                        <div id="progress-<?= $projet['id'] ?>" class="progress-bar <?= $scoreClass ?>" role="progressbar" style="width: <?= $score ?>%;" aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?= $score ?>%
                                        </div>
                                    </div>
                                    <div class="d-flex mb-3">
                                        <a href="projet.php?id=<?= $projet['id'] ?>" class="btn btn-primary flex-grow-1">
                                            <i class="bi bi-eye me-1"></i>Voir le projet
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else : ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Vous n'avez pas encore de projets.
                        <a href="creer_projet.php" class="alert-link">Créez votre premier projet</a>.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="creer_projet.php" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Créer un nouveau projet
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-check2-square me-2"></i>Mes Tâches</h5>
                </div>
                <div class="card-body">
                    <?php if (count($taches) > 0) : ?>
                    <div class="list-group">
                        <?php foreach ($taches as $tache) : ?>
                            <?php
                            $estEnRetard = false;
                            $aujourdhui = new DateTime();
                            $dateEcheance = new DateTime($tache['date_echeance']);
                            
                            if ($dateEcheance < $aujourdhui && $tache['statut'] !== 'Terminé') {
                                $estEnRetard = true;
                            }
                            
                            $badgeClass = 'bg-secondary';
                            if ($tache['statut'] === 'À faire') {
                                $badgeClass = 'bg-danger';
                            } elseif ($tache['statut'] === 'En cours') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($tache['statut'] === 'Terminé') {
                                $badgeClass = 'bg-success';
                            }
                            ?>
                            
                            <a href="projet.php?id=<?= $tache['id_projet'] ?>" class="list-group-item list-group-item-action <?= $estEnRetard ? 'list-group-item-danger' : '' ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= htmlspecialchars($tache['nom_tache']) ?></h5>
                                    <small>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($tache['statut']) ?></span>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <i class="bi bi-kanban me-1"></i>Projet: <?= htmlspecialchars($tache['nom_projet']) ?>
                                </p>
                                <small>
                                    <i class="bi bi-calendar me-1"></i>Échéance: <?= (new DateTime($tache['date_echeance']))->format('d/m/Y') ?>
                                    <?php if ($estEnRetard) : ?><span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> En retard</span><?php endif; ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else : ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Aucune tâche ne vous est assignée pour le moment.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<?php include 'includes/footer.php'; ?> 