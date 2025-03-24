<?php
// Ce fichier est inclus dans projet.php et gère l'affichage et le téléversement des fichiers

// Débogage des fichiers existants
error_log("DEBUG - Nombre de fichiers dans projet $id_projet: " . count($fichiers));

// IMPORTANT: NE PAS FAIRE DE REDIRECTIONS DANS CE FICHIER
// Les redirections avec header() doivent être faites avant que tout contenu HTML 
// ne soit envoyé au navigateur, c'est-à-dire en début de projet.php

// Traitement du téléversement déjà fait dans projet.php
// Traitement de la suppression déjà fait dans projet.php

// Récupérer les fichiers du projet (au cas où ils ont été mis à jour depuis le chargement de la page)
$resultat_fichiers = $fichier_obj->getFichiersProjet($id_projet);
$fichiers = $resultat_fichiers['succes'] ? $resultat_fichiers['fichiers'] : [];

// Afficher les détails des fichiers pour débogage
error_log("DEBUG - Résultat fichiers du projet $id_projet: " . json_encode($resultat_fichiers));
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark"></i> Fichiers du projet</h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTelechargerFichier">
            <i class="bi bi-upload"></i> Téléverser un fichier
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($message) && isset($type_message)): ?>
            <div class="alert alert-<?php echo $type_message; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($fichiers)): ?>
            <div class="alert alert-info">
                Aucun fichier dans ce projet. Cliquez sur "Téléverser un fichier" pour ajouter des documents.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Téléversé par</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichiers as $fichier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fichier['nom_fichier']); ?></td>
                                <td><?php echo htmlspecialchars($fichier['description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($fichier['nom_utilisateur']); ?></td>
                                <td><?php echo isset($fichier['date_televersement']) ? date('d/m/Y H:i', strtotime($fichier['date_televersement'])) : ''; ?></td>
                                <td>
                                    <?php
                                    // Déterminer l'icône en fonction du type de fichier
                                    $extension = isset($fichier['type_fichier']) ? $fichier['type_fichier'] : pathinfo($fichier['chemin_fichier'], PATHINFO_EXTENSION);
                                    $icone = 'bi-file-earmark';
                                    
                                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $icone = 'bi-file-earmark-image';
                                    } elseif (in_array($extension, ['pdf'])) {
                                        $icone = 'bi-file-earmark-pdf';
                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                        $icone = 'bi-file-earmark-word';
                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                        $icone = 'bi-file-earmark-excel';
                                    } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                        $icone = 'bi-file-earmark-slides';
                                    } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
                                        $icone = 'bi-file-earmark-zip';
                                    } elseif (in_array($extension, ['txt'])) {
                                        $icone = 'bi-file-earmark-text';
                                    }
                                    ?>
                                    <i class="bi <?php echo $icone; ?>"></i> <?php echo strtoupper($extension); ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="telecharger_fichier.php?id=<?php echo $fichier['id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="bi bi-download"></i> Télécharger
                                        </a>
                                        <?php if ($est_proprietaire || $est_admin || (isset($fichier['id_utilisateur']) && $fichier['id_utilisateur'] == $_SESSION['utilisateur_id'])): ?>
                                            <!-- Bouton de suppression - Utilise un formulaire caché -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmerSuppressionFichier(<?php echo $fichier['id']; ?>, <?php echo $id_projet; ?>)">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour téléverser un fichier -->
<div class="modal fade" id="modalTelechargerFichier" tabindex="-1" aria-labelledby="modalTelechargerFichierLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTelechargerFichierLabel">Téléverser un fichier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="uploadStatus"></div>
                <form action="projet.php?id=<?php echo $id_projet; ?>&tab=fichiers" method="post" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="telecharger_fichier">
                    
                    <div class="mb-3">
                        <label for="nom_fichier" class="form-label">Nom du fichier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom_fichier" name="nom_fichier" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fichier" class="form-label">Fichier <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="fichier" name="fichier" required>
                        <div class="form-text">
                            Taille maximale: 10 Mo. Types de fichiers acceptés: PDF, DOC, XLS, PPT, TXT, images, ZIP.
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Téléverser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Déboguer les fichiers existants
    console.log('Nombre de fichiers:', <?php echo count($fichiers); ?>);
    <?php if (!empty($fichiers)) { ?>
    console.log('Premier fichier:', <?php echo json_encode($fichiers[0]); ?>);
    <?php } ?>
    
    // Au cas où il y aurait des erreurs de téléversement, naviguer automatiquement vers l'onglet fichiers
    <?php if (isset($message) && isset($type_message)) { ?>
    $(document).ready(function() {
        $('#fichiers-tab').tab('show');
    });
    <?php } ?>
});

// Fonction pour confirmer la suppression d'un fichier
function confirmerSuppressionFichier(idFichier, idProjet) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
        // Créer et soumettre le formulaire
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'projet.php?id=' + idProjet + '&tab=fichiers';
        
        var inputSupprimer = document.createElement('input');
        inputSupprimer.type = 'hidden';
        inputSupprimer.name = 'supprimer_fichier';
        inputSupprimer.value = '1';
        form.appendChild(inputSupprimer);
        
        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_fichier';
        inputId.value = idFichier;
        form.appendChild(inputId);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
