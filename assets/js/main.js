document.addEventListener('DOMContentLoaded', function() {
    // Mettre en évidence les tâches en retard
    highlightOverdueTasks();
    
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Disparition automatique des alertes après 5 secondes
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Gérer le formulaire d'ajout de tâche
    var formTache = document.getElementById('form-tache');
    if (formTache) {
        formTache.addEventListener('submit', function(e) {
            var dateEcheance = document.getElementById('date_echeance').value;
            var today = new Date().toISOString().split('T')[0];
            
            if (dateEcheance < today) {
                e.preventDefault();
                alert('La date d\'échéance ne peut pas être antérieure à aujourd\'hui.');
            }
        });
    }
    
    // Gérer le formulaire de téléversement de fichier
    var formFichier = document.getElementById('form-fichier');
    if (formFichier) {
        formFichier.addEventListener('submit', function(e) {
            var fichier = document.getElementById('fichier').value;
            if (fichier) {
                var extension = fichier.split('.').pop().toLowerCase();
                var extensionsAutorisees = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
                
                if (!extensionsAutorisees.includes(extension)) {
                    e.preventDefault();
                    alert('Format de fichier non autorisé. Les formats autorisés sont : PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF.');
                }
            }
        });
    }

    // Mettre à jour le score du projet lors de la modification du statut d'une tâche
    var selectStatuts = document.querySelectorAll('.select-statut');
    if (selectStatuts.length > 0) {
        selectStatuts.forEach(function(select) {
            select.addEventListener('change', function() {
                var idTache = this.dataset.idTache;
                var idProjet = this.dataset.idProjet;
                var statut = this.value;
                
                console.log("Changement de statut détecté - Tâche:", idTache, "Projet:", idProjet, "Statut:", statut);
                
                // Envoyer la mise à jour via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/Projet platform Final/services/mettre_a_jour_statut.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        console.log("Réponse du serveur:", this.responseText);
                        try {
                            var response = JSON.parse(this.responseText);
                            if (response.success) {
                                console.log("Mise à jour réussie, score:", response.score);
                                // Mettre à jour la barre de progression si elle existe
                                var progressBar = document.getElementById('progress-' + idProjet);
                                if (progressBar && response.score !== undefined) {
                                    console.log("Mise à jour de la barre de progression à", response.score + "%");
                                    progressBar.style.width = response.score + '%';
                                    progressBar.setAttribute('aria-valuenow', response.score);
                                    progressBar.textContent = response.score + '%';
                                    
                                    // Mettre à jour les classes de la barre de progression
                                    progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
                                    if (response.score < 30) {
                                        progressBar.classList.add('bg-danger');
                                    } else if (response.score < 70) {
                                        progressBar.classList.add('bg-warning');
                                    } else {
                                        progressBar.classList.add('bg-success');
                                    }
                                } else {
                                    console.error("Barre de progression non trouvée ou score non défini");
                                }
                            } else {
                                console.error("Erreur lors de la mise à jour:", response.message || "Erreur inconnue");
                                alert("Erreur lors de la mise à jour du statut: " + (response.message || "Erreur inconnue"));
                            }
                        } catch (e) {
                            console.error("Erreur de parsing JSON:", e, "Réponse:", this.responseText);
                            alert("Erreur de communication avec le serveur");
                        }
                    } else {
                        console.error("Erreur HTTP:", this.status);
                        alert("Erreur de communication avec le serveur (HTTP " + this.status + ")");
                    }
                };
                xhr.onerror = function() {
                    console.error("Erreur réseau lors de la requête AJAX");
                    alert("Erreur réseau lors de la communication avec le serveur");
                };
                console.log("Envoi de la requête avec les données: id_tache=" + idTache + "&statut=" + statut);
                xhr.send('id_tache=' + idTache + '&statut=' + statut);
            });
        });
    }
    
    // Boutons pour rafraîchir la progression des projets (tableau de bord)
    var btnsRafraichirProgression = document.querySelectorAll('.btn-rafraichir-progression');
    if (btnsRafraichirProgression.length > 0) {
        btnsRafraichirProgression.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idProjet = this.dataset.idProjet;
                if (idProjet) {
                    rafraichirProgressionProjet(idProjet);
                }
            });
        });
    }
});

// Fonction pour mettre en évidence les tâches en retard
function highlightOverdueTasks() {
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    
    var taskRows = document.querySelectorAll('tr[data-date]');
    taskRows.forEach(function(row) {
        var dateStr = row.dataset.date;
        var dateParts = dateStr.split('-');
        var taskDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        
        if (taskDate < today && row.dataset.statut !== 'Terminé') {
            row.classList.add('task-overdue');
        }
    });
}

// Fonction pour rafraîchir la progression d'un projet
// Note: Cette fonction est toujours utilisée par le tableau de bord
function rafraichirProgressionProjet(idProjet) {
    console.log("Rafraîchissement de la progression pour le projet", idProjet);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/Projet platform Final/services/recalculer_score.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        console.log("Réponse reçue:", this.responseText);
        if (this.status === 200) {
            try {
                var response = JSON.parse(this.responseText);
                if (response.success) {
                    // Trouver et mettre à jour la barre de progression
                    var progressBar = document.getElementById('progress-' + idProjet);
                    if (progressBar) {
                        console.log("Mise à jour de la barre à", response.score + "%");
                        progressBar.style.width = response.score + '%';
                        progressBar.setAttribute('aria-valuenow', response.score);
                        progressBar.textContent = response.score + '%';
                        
                        // Mettre à jour les classes
                        progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
                        progressBar.classList.add(response.scoreClass);
                        
                        // Afficher une notification
                        alert('Progression mise à jour avec succès: ' + response.score + '%');
                    } else {
                        console.error("Barre de progression non trouvée");
                        alert("Erreur: Barre de progression non trouvée");
                    }
                } else {
                    console.error("Erreur lors de la mise à jour:", response.message || "Erreur inconnue");
                    alert('Erreur lors de la mise à jour: ' + (response.message || 'Une erreur est survenue'));
                }
            } catch (e) {
                console.error("Erreur de parsing JSON:", e, "Réponse:", this.responseText);
                alert("Erreur de communication avec le serveur");
            }
        } else {
            console.error("Erreur HTTP:", this.status);
            alert("Erreur de communication avec le serveur (HTTP " + this.status + ")");
        }
    };
    xhr.onerror = function() {
        console.error("Erreur réseau lors de la requête AJAX");
        alert("Erreur réseau lors de la communication avec le serveur");
    };
    console.log("Envoi de la requête avec les données: id_projet=" + idProjet);
    xhr.send('id_projet=' + idProjet);
} 