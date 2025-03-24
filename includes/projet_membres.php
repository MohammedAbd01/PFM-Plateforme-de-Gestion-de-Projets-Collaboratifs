<?php
// Ce fichier est inclus dans projet.php et gère l'affichage et la gestion des membres

// Traiter l'ajout d'un nouveau membre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inviter_membre') {
    // Vérifier si l'utilisateur est propriétaire du projet
    if (!$est_proprietaire && !$est_admin) {
        $message = 'Seul le propriétaire du projet peut inviter des membres.';
        $type_message = 'danger';
    } else {
        $email_invite = trim($_POST['email_invite']);
        
        // Valider les données
        if (empty($email_invite)) {
            $message = 'L\'email est obligatoire.';
            $type_message = 'danger';
        } else {
            // Inviter le membre
            $resultat = $projet_obj->inviterMembre($id_projet, $email_invite);
            
            if ($resultat['succes']) {
                // Utiliser JavaScript pour la redirection au lieu de header()
                echo "<script>window.location.href = 'projet.php?id=" . $id_projet . "&message=Invitation envoyu00e9e avec succu00e8s.&type=success#membres';</script>";
                // Pas besoin de exit car le JavaScript va recharger la page
            } else {
                $message = $resultat['message'];
                $type_message = 'danger';
            }
        }
    }
}

// Traiter la suppression d'un membre
if (isset($_GET['supprimer_membre']) && isset($_GET['id'])) {
    // Vérifier si l'utilisateur est propriétaire du projet
    if (!$est_proprietaire && !$est_admin) {
        header('Location: projet.php?id=' . $id_projet . '&message=Seul le propriétaire du projet peut supprimer des membres.&type=danger#membres');
        exit;
    }
    
    $id_membre = intval($_GET['id']);
    
    // Ne pas supprimer le propriétaire
    if ($id_membre === $projet['id_proprietaire']) {
        header('Location: projet.php?id=' . $id_projet . '&message=Vous ne pouvez pas supprimer le propriétaire du projet.&type=danger#membres');
        exit;
    }
    
    // Supprimer le membre
    $resultat = $projet_obj->supprimerMembre($id_projet, $id_membre);
    
    if ($resultat['succes']) {
        header('Location: projet.php?id=' . $id_projet . '&message=Membre supprimé avec succès.&type=success#membres');
    } else {
        header('Location: projet.php?id=' . $id_projet . '&message=' . $resultat['message'] . '&type=danger#membres');
    }
    exit;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Membres du projet</h5>
        <?php if ($est_proprietaire || $est_admin): ?>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalInviterMembre">
                <i class="bi bi-person-plus"></i> Inviter un membre
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($membres)): ?>
            <div class="alert alert-info">
                Aucun membre dans ce projet. 
                <?php if ($est_proprietaire || $est_admin): ?>
                    Invitez des membres pour collaborer.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date d'ajout</th>
                            <?php if ($est_proprietaire || $est_admin): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membres as $membre): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($membre['nom_utilisateur']); ?>
                                    <?php if ($membre['id'] === $projet['id_proprietaire']): ?>
                                        <span class="badge bg-primary">Propriétaire</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($membre['email']); ?></td>
                                <td><?php echo isset($membre['role']) ? htmlspecialchars($membre['role']) : 'Membre'; ?></td>
                                <td><?php echo isset($membre['date_ajout']) && !empty($membre['date_ajout']) ? date('d/m/Y', strtotime($membre['date_ajout'])) : date('d/m/Y'); ?></td>
                                <?php if ($est_proprietaire || $est_admin): ?>
                                    <td>
                                        <?php if ($membre['id'] !== $projet['id_proprietaire']): ?>
                                            <a href="#" onclick="return confirmerSuppression('Êtes-vous sûr de vouloir supprimer ce membre ?', 'projet.php?id=<?php echo $id_projet; ?>&supprimer_membre=1&id=<?php echo $membre['id']; ?>')" class="btn btn-danger btn-sm">
                                                <i class="bi bi-person-x"></i> Supprimer
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Propriétaire</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour inviter un membre -->
<?php if ($est_proprietaire || $est_admin): ?>
    <div class="modal fade" id="modalInviterMembre" tabindex="-1" aria-labelledby="modalInviterMembreLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInviterMembreLabel">Inviter un membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="projet.php?id=<?php echo $id_projet; ?>#membres">
                        <input type="hidden" name="action" value="inviter_membre">
                        
                        <div class="mb-3">
                            <label for="email_invite" class="form-label">Email de l'utilisateur <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email_invite" name="email_invite" required>
                            <div class="form-text">
                                L'utilisateur doit être inscrit sur la plateforme avec cette adresse email.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Envoyer l'invitation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
