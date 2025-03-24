<?php
// Ce fichier est inclus dans projet.php et gère l'affichage et l'envoi des messages

// Récupérer les messages du projet
$resultat_messages = $message_obj->getMessagesProjet($id_projet);
$messages = $resultat_messages['succes'] ? $resultat_messages['messages'] : [];

// Traiter l'envoi d'un nouveau message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'envoyer_message') {
    $contenu = trim($_POST['contenu']);
    
    // Valider les données
    if (empty($contenu)) {
        $message_erreur = 'Le contenu du message est obligatoire.';
    } else {
        // Envoyer le message - utiliser la bonne méthode envoyerMessage au lieu de creerMessage
        // envoyerMessage prend un id_destinataire que nous allons laisser NULL pour un message de projet
        $resultat = $message_obj->envoyerMessage($id_projet, $_SESSION['utilisateur_id'], null, $contenu);
        
        if ($resultat['succes']) {
            // Actualiser les messages - recharger les messages après l'envoi
            $resultat_messages = $message_obj->getMessagesProjet($id_projet);
            $messages = $resultat_messages['succes'] ? $resultat_messages['messages'] : [];
            // Message de succès
            $message_succes = 'Message envoyé avec succès!';
        } else {
            $message_erreur = $resultat['message'];
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-chat"></i> Messages du projet</h5>
    </div>
    <div class="card-body">
        <?php if (isset($message_succes)): ?>
            <div class="alert alert-success mb-3">
                <?php echo $message_succes; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($message_erreur)): ?>
            <div class="alert alert-danger mb-3">
                <?php echo $message_erreur; ?>
            </div>
        <?php endif; ?>

        <!-- Liste des messages -->
        <div class="messages-container mb-4">
            <?php if (empty($messages)): ?>
                <div class="alert alert-info">
                    Aucun message dans ce projet. Soyez le premier à envoyer un message !
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                    // Déterminer si le message est de l'utilisateur courant
                    $est_message_utilisateur = $msg['id_expediteur'] === $_SESSION['utilisateur_id'];
                    $classe_message = $est_message_utilisateur ? 'message-utilisateur' : 'message-autre';
                    ?>
                    
                    <div class="message <?php echo $classe_message; ?>">
                        <div class="message-header">
                            <strong><?php echo htmlspecialchars($msg['nom_expediteur'] ?? 'Utilisateur'); ?></strong>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($msg['date_envoi'])); ?>
                            </small>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'] ?? '')); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Formulaire d'envoi de message -->
        <form method="post" id="messageForm" action="projet.php?id=<?php echo $id_projet; ?>" class="message-form">
            <input type="hidden" name="action" value="envoyer_message">
            
            <div class="mb-3">
                <label for="contenu" class="form-label">Nouveau message</label>
                <textarea class="form-control" id="contenu" name="contenu" rows="3" required></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Envoyer
                </button>
            </div>
        </form>
        
        <script>
        // Ajouter un gestionnaire pour le formulaire de message
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            // Ne pas intercepter l'action du formulaire - la laisser se poursuivre normalement
            
            // Enregistrer que nous sommes sur l'onglet messages
            sessionStorage.setItem('activeTab', 'messages');
        });
        
        // Vérifier si nous devrions activer l'onglet messages
        document.addEventListener('DOMContentLoaded', function() {
            if (sessionStorage.getItem('activeTab') === 'messages' || window.location.hash === '#messages') {
                // Activer l'onglet messages
                var messagesTab = document.getElementById('messages-tab');
                if (messagesTab) {
                    messagesTab.click();
                }
                // Effacer le stockage après utilisation
                sessionStorage.removeItem('activeTab');
            }
        });
        </script>
    </div>
</div>
