<?php
// Ce fichier est inclus dans projet.php et gère l'affichage et la gestion des tâches

// Récupérer les tâches du projet
$resultat_taches = $tache_obj->getTachesProjet($id_projet);
$taches = $resultat_taches['succes'] ? $resultat_taches['taches'] : [];

// Traiter l'ajout d'une nouvelle tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_tache') {
    $nom_tache = trim($_POST['nom_tache']);
    $description = trim($_POST['description']);
    $date_echeance = !empty($_POST['date_echeance']) ? $_POST['date_echeance'] : null;
    $id_assignee = !empty($_POST['id_assignee']) ? intval($_POST['id_assignee']) : null;
    
    // Valider les données
    if (empty($nom_tache)) {
        $message = 'Le nom de la tâche est obligatoire.';
        $type_message = 'danger';
    } else {
        // Créer la tâche
        $resultat = $tache_obj->creerTache($id_projet, $nom_tache, $description, $id_assignee, 'À faire', $date_echeance);
        
        if ($resultat['succes']) {
            // Au lieu d'utiliser header(), on crée un script JavaScript pour la redirection
            echo "<script>window.location.href = 'projet.php?id=" . $id_projet . "&message=Tâche créée avec succès.&type=success';</script>";
            // Pas besoin de exit car le JavaScript va recharger la page
        } else {
            $message = $resultat['message'];
            $type_message = 'danger';
        }
    }
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check"></i> Tâches du projet</h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterTache">
            <i class="bi bi-plus"></i> Ajouter une tâche
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($taches)): ?>
            <div class="alert alert-info">
                Aucune tâche pour ce projet. Cliquez sur "Ajouter une tâche" pour commencer.
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Colonne À faire -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">À faire</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="task-list" id="todo-tasks">
                                <?php foreach ($taches as $tache): ?>
                                    <?php if ($tache['statut'] === 'À faire'): ?>
                                        <?php 
                                        // Vérifier si la tâche est en retard
                                        $est_en_retard = $tache_obj->estEnRetard($tache['date_echeance']); 
                                        ?>
                                        <div class="card mb-2 task-card <?php echo $est_en_retard ? 'task-overdue' : ''; ?>" id="task-<?php echo $tache['id']; ?>" data-task-id="<?php echo $tache['id']; ?>">
                                            <div class="card-body p-2">
                                                <h6 class="card-title"><?php echo htmlspecialchars($tache['nom_tache']); ?></h6>
                                                <?php if (!empty($tache['description'])): ?>
                                                    <p class="card-text small"><?php echo htmlspecialchars($tache['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['date_echeance'])): ?>
                                                    <p class="card-text small mb-1">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('d/m/Y', strtotime($tache['date_echeance'])); ?>
                                                        <?php if ($est_en_retard): ?>
                                                            <span class="badge bg-danger">En retard</span>
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['assignee'])): ?>
                                                    <p class="card-text small mb-0">
                                                        <i class="bi bi-person"></i> 
                                                        <?php echo htmlspecialchars($tache['assignee']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="mt-2 d-flex justify-content-between">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Statut
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'À faire'); return false;">À faire</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'En cours'); return false;">En cours</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'Terminé'); return false;">Terminé</a></li>
                                                        </ul>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $tache['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne En cours -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">En cours</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="task-list" id="in-progress-tasks">
                                <?php foreach ($taches as $tache): ?>
                                    <?php if ($tache['statut'] === 'En cours'): ?>
                                        <?php 
                                        // Vérifier si la tâche est en retard
                                        $est_en_retard = $tache_obj->estEnRetard($tache['date_echeance']); 
                                        ?>
                                        <div class="card mb-2 task-card <?php echo $est_en_retard ? 'task-overdue' : ''; ?>" id="task-<?php echo $tache['id']; ?>" data-task-id="<?php echo $tache['id']; ?>">
                                            <div class="card-body p-2">
                                                <h6 class="card-title"><?php echo htmlspecialchars($tache['nom_tache']); ?></h6>
                                                <?php if (!empty($tache['description'])): ?>
                                                    <p class="card-text small"><?php echo htmlspecialchars($tache['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['date_echeance'])): ?>
                                                    <p class="card-text small mb-1">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('d/m/Y', strtotime($tache['date_echeance'])); ?>
                                                        <?php if ($est_en_retard): ?>
                                                            <span class="badge bg-danger">En retard</span>
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['assignee'])): ?>
                                                    <p class="card-text small mb-0">
                                                        <i class="bi bi-person"></i> 
                                                        <?php echo htmlspecialchars($tache['assignee']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="mt-2 d-flex justify-content-between">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Statut
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'À faire'); return false;">À faire</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'En cours'); return false;">En cours</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'Terminé'); return false;">Terminé</a></li>
                                                        </ul>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $tache['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne Terminé -->
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Terminé</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="task-list" id="completed-tasks">
                                <?php foreach ($taches as $tache): ?>
                                    <?php if ($tache['statut'] === 'Terminé'): ?>
                                        <div class="card mb-2 task-card" id="task-<?php echo $tache['id']; ?>" data-task-id="<?php echo $tache['id']; ?>">
                                            <div class="card-body p-2">
                                                <h6 class="card-title"><?php echo htmlspecialchars($tache['nom_tache']); ?></h6>
                                                <?php if (!empty($tache['description'])): ?>
                                                    <p class="card-text small"><?php echo htmlspecialchars($tache['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['date_echeance'])): ?>
                                                    <p class="card-text small mb-1">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('d/m/Y', strtotime($tache['date_echeance'])); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($tache['assignee'])): ?>
                                                    <p class="card-text small mb-0">
                                                        <i class="bi bi-person"></i> 
                                                        <?php echo htmlspecialchars($tache['assignee']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="mt-2 d-flex justify-content-between">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Statut
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'À faire'); return false;">À faire</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'En cours'); return false;">En cours</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="updateTaskStatus(<?php echo $tache['id']; ?>, 'Terminé'); return false;">Terminé</a></li>
                                                        </ul>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $tache['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour ajouter une tâche -->
<div class="modal fade" id="modalAjouterTache" tabindex="-1" aria-labelledby="modalAjouterTacheLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAjouterTacheLabel">Ajouter une tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="projet.php?id=<?php echo $id_projet; ?>">
                    <input type="hidden" name="action" value="ajouter_tache">
                    
                    <div class="mb-3">
                        <label for="nom_tache" class="form-label">Nom de la tâche <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom_tache" name="nom_tache" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_echeance" class="form-label">Date d'échéance</label>
                        <input type="date" class="form-control" id="date_echeance" name="date_echeance">
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_assignee" class="form-label">Assignée à</label>
                        <select class="form-select" id="id_assignee" name="id_assignee">
                            <option value="">Non assignée</option>
                            <?php foreach ($membres as $membre): ?>
                                <option value="<?php echo $membre['id']; ?>"><?php echo htmlspecialchars($membre['nom_utilisateur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Ajouter la tâche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
