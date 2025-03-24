<?php
require_once 'includes/auth_check.php';
require_once 'classes/Projet.php';
require_once 'classes/Utilisateur.php';
require_once 'classes/Tache.php';
require_once 'classes/Message.php';
require_once 'classes/Fichier.php';

// Vérifier si l'ID du projet est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID de projet non valide.';
    $_SESSION['message_type'] = 'danger';
    header('Location: tableau_de_bord.php');
    exit;
}

$idProjet = (int)$_GET['id'];
$idUtilisateur = $_SESSION['utilisateur']['id'];
$erreur = '';
$success = '';

// Initialiser les objets
$projetObj = new Projet();
$utilisateurObj = new Utilisateur();
$tacheObj = new Tache();
$messageObj = new Message();
$fichierObj = new Fichier();

// Vérifier si l'utilisateur est membre du projet
if (!$projetObj->estMembre($idProjet, $idUtilisateur)) {
    $_SESSION['message'] = 'Vous n\'êtes pas membre de ce projet.';
    $_SESSION['message_type'] = 'danger';
    header('Location: tableau_de_bord.php');
    exit;
}

// Obtenir les détails du projet
$projet = $projetObj->obtenirProjet($idProjet);
if (!$projet) {
    $_SESSION['message'] = 'Projet non trouvé.';
    $_SESSION['message_type'] = 'danger';
    header('Location: tableau_de_bord.php');
    exit;
}

// Vérifier si l'utilisateur est le propriétaire du projet
$estProprietaire = $projetObj->estProprietaire($idProjet, $idUtilisateur);

// Obtenir les membres du projet
$membres = $projetObj->obtenirMembres($idProjet);

// Obtenir les tâches du projet
$taches = $tacheObj->obtenirTachesProjet($idProjet);

// Obtenir les messages du projet
$messages = $messageObj->obtenirMessagesProjet($idProjet);

// Obtenir les fichiers du projet
$fichiers = $fichierObj->obtenirFichiersProjet($idProjet);

// Calculer le score du projet
$score = $projetObj->calculerScore($idProjet);
$scoreClass = '';
if ($score < 30) {
    $scoreClass = 'bg-danger';
} elseif ($score < 70) {
    $scoreClass = 'bg-warning';
} else {
    $scoreClass = 'bg-success';
}

// Traiter les formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Invitation d'un membre
    if (isset($_POST['inviter_membre']) && $estProprietaire) {
        $nomUtilisateur = $_POST['nom_utilisateur'] ?? '';
        
        if (empty($nomUtilisateur)) {
            $erreur = 'Le nom d\'utilisateur est obligatoire.';
        } else {
            // Rechercher l'utilisateur
            $utilisateurs = $utilisateurObj->rechercher($nomUtilisateur);
            
            if (empty($utilisateurs)) {
                $erreur = 'Utilisateur non trouvé.';
            } else {
                // Inviter le premier utilisateur trouvé
                $utilisateurAInviter = $utilisateurs[0];
                $resultat = $projetObj->inviterMembre($idProjet, $utilisateurAInviter['id']);
                
                if ($resultat['success']) {
                    $success = $resultat['message'];
                } else {
                    $erreur = $resultat['message'];
                }
            }
        }
    }
    
    // Création d'une tâche
    if (isset($_POST['creer_tache'])) {
        $nomTache = $_POST['nom_tache'] ?? '';
        $description = $_POST['description'] ?? '';
        $assigneA = $_POST['assigne_a'] ?? '';
        $statut = $_POST['statut'] ?? 'À faire';
        $dateEcheance = $_POST['date_echeance'] ?? '';
        
        if (empty($nomTache) || empty($assigneA) || empty($dateEcheance)) {
            $erreur = 'Le nom de la tâche, l\'utilisateur assigné et la date d\'échéance sont obligatoires.';
        } else {
            $resultat = $tacheObj->creer($idProjet, $nomTache, $description, $assigneA, $statut, $dateEcheance);
            
            if ($resultat['success']) {
                $success = 'Tâche créée avec succès!';
                // Rafraîchir les tâches
                $taches = $tacheObj->obtenirTachesProjet($idProjet);
                // Mettre à jour le score
                $score = $projetObj->calculerScore($idProjet);
            } else {
                $erreur = $resultat['message'];
            }
        }
    }
    
    // Envoi d'un message
    if (isset($_POST['envoyer_message'])) {
        $message = $_POST['message'] ?? '';
        $destinataire = $_POST['destinataire'] ?? '';
        
        if (empty($message)) {
            $erreur = 'Le message ne peut pas être vide.';
        } else {
            $resultat = $messageObj->envoyer($idProjet, $idUtilisateur, $destinataire ?: null, $message);
            
            if ($resultat['success']) {
                $success = 'Message envoyé avec succès!';
                // Rafraîchir les messages
                $messages = $messageObj->obtenirMessagesProjet($idProjet);
            } else {
                $erreur = $resultat['message'];
            }
        }
    }
    
    // Téléversement d'un fichier
    if (isset($_POST['telecharger_fichier']) && isset($_FILES['fichier'])) {
        if ($_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            if ($fichierObj->estExtensionAutorisee($_FILES['fichier'])) {
                $resultat = $fichierObj->telecharger($idProjet, $_FILES['fichier']);
                
                if ($resultat['success']) {
                    $success = 'Fichier téléversé avec succès!';
                    // Rafraîchir les fichiers
                    $fichiers = $fichierObj->obtenirFichiersProjet($idProjet);
                } else {
                    $erreur = $resultat['message'];
                }
            } else {
                $erreur = 'Format de fichier non autorisé.';
            }
        } else {
            $erreur = 'Erreur lors du téléversement du fichier.';
        }
    }
    
    // Suppression d'un fichier
    if (isset($_POST['supprimer_fichier']) && isset($_POST['id_fichier'])) {
        $idFichier = (int)$_POST['id_fichier'];
        
        // Vérifier que l'utilisateur est le propriétaire du projet
        if ($estProprietaire) {
            $resultat = $fichierObj->supprimerFichier($idFichier);
            
            if ($resultat['success']) {
                $success = 'Fichier supprimé avec succès!';
                // Rafraîchir les fichiers
                $fichiers = $fichierObj->obtenirFichiersProjet($idProjet);
            } else {
                $erreur = $resultat['message'];
            }
        } else {
            $erreur = 'Vous n\'avez pas les droits pour supprimer ce fichier.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><?= htmlspecialchars($projet['nom_projet']) ?></h1>
        <a href="tableau_de_bord.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
        </a>
    </div>
    
    <?php if ($erreur) : ?>
    <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>
    
    <?php if ($success) : ?>
    <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Informations du projet et membres -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du Projet</h5>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                    <p><strong>Propriétaire:</strong> <?= htmlspecialchars($projet['proprietaire_nom']) ?></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Progression du projet:</label>
                        <div class="progress">
                            <div id="progress-<?= $idProjet ?>" class="progress-bar <?= $scoreClass ?>" role="progressbar" style="width: <?= $score ?>%;" aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= $score ?>%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="text-muted">Projet créé par <?= htmlspecialchars($projet['proprietaire_nom']) ?></span>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Membres du Projet</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($membres as $membre) : ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($membre['nom_utilisateur']) ?>
                            
                            <?php if ($membre['id'] == $projet['id_proprietaire']) : ?>
                            <span class="badge bg-primary">Propriétaire</span>
                            <?php elseif ($membre['statut'] == 'en_attente') : ?>
                            <span class="badge bg-warning text-dark">En attente</span>
                            <?php else : ?>
                            <span class="badge bg-success">Membre</span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if ($estProprietaire) : ?>
                    <hr>
                    <h6 class="mb-3">Inviter un membre</h6>
                    <form method="post" action="projet.php?id=<?= $idProjet ?>">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Nom d'utilisateur" name="nom_utilisateur" required>
                            <button class="btn btn-primary" type="submit" name="inviter_membre">Inviter</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark me-2"></i>Fichiers</h5>
                </div>
                <div class="card-body">
                    <?php if (count($fichiers) > 0) : ?>
                    <ul class="list-group">
                        <?php foreach ($fichiers as $fichier) : ?>
                        <li class="list-group-item file-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    <?= htmlspecialchars($fichier['nom_fichier']) ?>
                                </span>
                                <div>
                                    <a href="telecharger_fichier.php?id=<?= $fichier['id'] ?>" class="btn btn-sm btn-primary" title="Télécharger">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if ($estProprietaire) : ?>
                                    <form method="post" action="projet.php?id=<?= $idProjet ?>" class="d-inline">
                                        <input type="hidden" name="id_fichier" value="<?= $fichier['id'] ?>">
                                        <button type="submit" name="supprimer_fichier" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-muted small">
                                Téléversé le <?= (new DateTime($fichier['date_televersement']))->format('d/m/Y H:i') ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else : ?>
                    <p class="text-muted">Aucun fichier téléversé.</p>
                    <?php endif; ?>
                    
                    <hr>
                    <h6 class="mb-3">Téléverser un fichier</h6>
                    <form method="post" action="projet.php?id=<?= $idProjet ?>" enctype="multipart/form-data" id="form-fichier">
                        <div class="mb-3">
                            <input type="file" class="form-control" id="fichier" name="fichier" required>
                            <div class="form-text">
                                Formats autorisés: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="telecharger_fichier">
                            <i class="bi bi-upload me-1"></i>Téléverser
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tâches -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Tâches</h5>
                </div>
                <div class="card-body">
                    <?php if (count($taches) > 0) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Assigné à</th>
                                    <th>Statut</th>
                                    <th>Échéance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($taches as $tache) : ?>
                                <?php
                                $estEnRetard = false;
                                $aujourdhui = new DateTime();
                                $dateEcheance = new DateTime($tache['date_echeance']);
                                
                                if ($dateEcheance < $aujourdhui && $tache['statut'] !== 'Terminé') {
                                    $estEnRetard = true;
                                }
                                
                                $peutModifier = $tache['assigne_a'] == $idUtilisateur || $estProprietaire;
                                ?>
                                <tr data-date="<?= $tache['date_echeance'] ?>" data-statut="<?= $tache['statut'] ?>" <?= $estEnRetard ? 'class="task-overdue"' : '' ?>>
                                    <td>
                                        <strong><?= htmlspecialchars($tache['nom_tache']) ?></strong>
                                        <?php if (!empty($tache['description'])) : ?>
                                        <p class="mb-0 small text-muted"><?= htmlspecialchars($tache['description']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($tache['assigne_nom'] ?? 'Non assigné') ?></td>
                                    <td>
                                        <?php if ($peutModifier) : ?>
                                        <form method="post" action="services/update_status.php" class="status-form">
                                            <input type="hidden" name="id_tache" value="<?= $tache['id'] ?>">
                                            <input type="hidden" name="id_projet" value="<?= $idProjet ?>">
                                            <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                                            <select class="form-select form-select-sm" name="statut" onchange="this.form.submit()">
                                                <option value="À faire" <?= $tache['statut'] === 'À faire' ? 'selected' : '' ?>>À faire</option>
                                                <option value="En cours" <?= $tache['statut'] === 'En cours' ? 'selected' : '' ?>>En cours</option>
                                                <option value="Terminé" <?= $tache['statut'] === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
                                            </select>
                                        </form>
                                        <?php else : ?>
                                        <span class="badge <?= $tache['statut'] === 'À faire' ? 'bg-danger' : ($tache['statut'] === 'En cours' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                            <?= htmlspecialchars($tache['statut']) ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $dateEcheance->format('d/m/Y') ?>
                                        <?php if ($estEnRetard) : ?>
                                        <br><span class="text-danger small"><i class="bi bi-exclamation-triangle-fill"></i> En retard</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else : ?>
                    <p class="text-muted">Aucune tâche créée pour ce projet.</p>
                    <?php endif; ?>
                    
                    <hr>
                    <h6 class="mb-3">Ajouter une tâche</h6>
                    <form method="post" action="projet.php?id=<?= $idProjet ?>" id="form-tache">
                        <div class="mb-3">
                            <label for="nom_tache" class="form-label">Nom de la tâche <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom_tache" name="nom_tache" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="assigne_a" class="form-label">Assigné à <span class="text-danger">*</span></label>
                            <select class="form-select" id="assigne_a" name="assigne_a" required>
                                <option value="">Sélectionner un membre</option>
                                <?php foreach ($membres as $membre) : ?>
                                <?php if ($membre['statut'] === 'accepte') : ?>
                                <option value="<?= $membre['id'] ?>"><?= htmlspecialchars($membre['nom_utilisateur']) ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="À faire" selected>À faire</option>
                                    <option value="En cours">En cours</option>
                                    <option value="Terminé">Terminé</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="date_echeance" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_echeance" name="date_echeance" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="creer_tache">
                            <i class="bi bi-plus-circle me-1"></i>Ajouter la tâche
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Messages</h5>
                </div>
                <div class="card-body">
                    <div class="messages-container mb-4" style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($messages) > 0) : ?>
                        <?php foreach ($messages as $message) : ?>
                        <?php
                        $estMonMessage = $message['id_expediteur'] == $idUtilisateur;
                        $classeMessage = $estMonMessage ? 'message-sent' : 'message-received';
                        
                        // Si c'est un message à tous les membres
                        if ($message['id_destinataire'] === null) {
                            $classeMessage = 'message-broadcast';
                        }
                        ?>
                        <div class="message <?= $classeMessage ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong>
                                    <?= htmlspecialchars($message['expediteur_nom']) ?>
                                    <?php if ($message['id_destinataire'] !== null && isset($message['destinataire_nom'])) : ?>
                                    <i class="bi bi-arrow-right mx-1"></i> <?= htmlspecialchars($message['destinataire_nom']) ?>
                                    <?php elseif ($message['id_destinataire'] === null) : ?>
                                    <i class="bi bi-arrow-right mx-1"></i> Tous les membres
                                    <?php endif; ?>
                                </strong>
                                <small class="text-muted">
                                    <?= (new DateTime($message['date_envoi']))->format('d/m/Y H:i') ?>
                                </small>
                            </div>
                            <hr class="my-1">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <p class="text-muted">Aucun message dans ce projet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Envoyer un message</h6>
                    <form method="post" action="projet.php?id=<?= $idProjet ?>">
                        <div class="mb-3">
                            <label for="destinataire" class="form-label">Destinataire</label>
                            <select class="form-select" id="destinataire" name="destinataire">
                                <option value="">Tous les membres</option>
                                <?php foreach ($membres as $membre) : ?>
                                <?php if ($membre['id'] != $idUtilisateur && $membre['statut'] === 'accepte') : ?>
                                <option value="<?= $membre['id'] ?>"><?= htmlspecialchars($membre['nom_utilisateur']) ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="envoyer_message">
                            <i class="bi bi-send me-1"></i>Envoyer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
<?php include 'includes/footer.php'; ?> 