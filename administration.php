<?php
require_once 'includes/admin_check.php';
require_once 'classes/Utilisateur.php';
require_once 'classes/Projet.php';

$utilisateurObj = new Utilisateur();
$projetObj = new Projet();

// Obtenir tous les utilisateurs
$utilisateurs = $utilisateurObj->obtenirTousLesUtilisateurs();

// Obtenir tous les projets
$projets = $projetObj->obtenirTousProjets();

// Gérer la suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_utilisateur'])) {
    $idUtilisateur = $_POST['id_utilisateur'] ?? 0;
    if ($idUtilisateur) {
        $resultat = $utilisateurObj->supprimerUtilisateur($idUtilisateur);
        if ($resultat['success']) {
            $_SESSION['message'] = 'Utilisateur supprimé avec succès!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Erreur lors de la suppression de l\'utilisateur.';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: administration.php');
        exit;
    }
}

// Gérer la suppression d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_projet'])) {
    $idProjet = $_POST['id_projet'] ?? 0;
    if ($idProjet) {
        $resultat = $projetObj->supprimerProjet($idProjet);
        if ($resultat['success']) {
            $_SESSION['message'] = 'Projet supprimé avec succès!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Erreur lors de la suppression du projet.';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: administration.php');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Panneau d'Administration</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Gestion des Utilisateurs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $utilisateur) : ?>
                                <tr>
                                    <td><?= $utilisateur['id'] ?></td>
                                    <td><?= htmlspecialchars($utilisateur['nom_utilisateur']) ?></td>
                                    <td><?= htmlspecialchars($utilisateur['email']) ?></td>
                                    <td>
                                        <span class="badge <?= $utilisateur['role'] === 'Administrateur' ? 'bg-danger' : 'bg-info' ?>">
                                            <?= htmlspecialchars($utilisateur['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($utilisateur['id'] != $_SESSION['utilisateur']['id']) : ?>
                                        <form method="post" action="administration.php" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?');">
                                            <input type="hidden" name="id_utilisateur" value="<?= $utilisateur['id'] ?>">
                                            <button type="submit" name="supprimer_utilisateur" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php else : ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-person-check"></i> Vous
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-kanban me-2"></i>Gestion des Projets</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom du projet</th>
                                    <th>Propriétaire</th>
                                    <th>Membres</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projets as $projet) : ?>
                                <tr>
                                    <td><?= $projet['id'] ?></td>
                                    <td><?= htmlspecialchars($projet['nom_projet']) ?></td>
                                    <td><?= htmlspecialchars($projet['proprietaire_nom']) ?></td>
                                    <td><?= $projet['nombre_membres'] ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="projet.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form method="post" action="administration.php" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet?');">
                                                <input type="hidden" name="id_projet" value="<?= $projet['id'] ?>">
                                                <button type="submit" name="supprimer_projet" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistiques</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Utilisateurs</h6>
                                    <h2 class="mb-0"><?= count($utilisateurs) ?></h2>
                                </div>
                                <i class="bi bi-people fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Projets</h6>
                                    <h2 class="mb-0"><?= count($projets) ?></h2>
                                </div>
                                <i class="bi bi-kanban fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Administrateurs</h6>
                                    <h2 class="mb-0">
                                        <?php
                                        $nbAdmins = 0;
                                        foreach ($utilisateurs as $utilisateur) {
                                            if ($utilisateur['role'] === 'Administrateur') {
                                                $nbAdmins++;
                                            }
                                        }
                                        echo $nbAdmins;
                                        ?>
                                    </h2>
                                </div>
                                <i class="bi bi-shield-lock fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Moyenne Membres/Projet</h6>
                                    <h2 class="mb-0">
                                        <?php
                                        $totalMembres = 0;
                                        foreach ($projets as $projet) {
                                            $totalMembres += $projet['nombre_membres'];
                                        }
                                        echo count($projets) > 0 ? round($totalMembres / count($projets), 1) : 0;
                                        ?>
                                    </h2>
                                </div>
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 