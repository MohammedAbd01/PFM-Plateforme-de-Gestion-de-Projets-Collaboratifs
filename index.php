<?php
session_start();

// Rediriger vers le tableau de bord si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: tableau_de_bord.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="hero-section" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-6 text-lg-start text-center mb-4 mb-lg-0">
                <h1 class="hero-title">Gestion de Projets Simplifiée</h1>
                <p class="hero-subtitle">Organisez, collaborez et réussissez vos projets avec notre plateforme moderne et intuitive. CollabSphere vous offre tous les outils nécessaires pour atteindre vos objectifs.</p>
                <div class="d-grid gap-3 d-sm-flex justify-content-lg-start justify-content-center">
                    <a href="inscription.php" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-person-plus me-2"></i>Commencer
                    </a>
                    <a href="connexion.php" class="btn btn-outline-secondary btn-lg px-5">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 pt-5">
        <div class="col-12 text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Pourquoi choisir <span style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">CollabSphere</span> ?</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Notre plateforme propose des fonctionnalités puissantes pour rendre la gestion de projets plus efficace et agréable.</p>
        </div>
    </div>
    
    </div>
    
    <div class="row my-5 py-5 align-items-center">
        <div class="col-lg-6 order-lg-1" data-aos="fade-right">
            <h2 class="display-6 fw-bold mb-4">Des outils puissants pour des projets réussis</h2>
            <div class="d-flex mb-3">
                <div class="me-3">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
                <div>
                    <h5>Gestion des tâches avancée</h5>
                    <p class="text-muted">Créez, attribuez et suivez les tâches avec des dates d'échéance, des priorités et des étiquettes personnalisées.</p>
                </div>
            </div>
            <div class="d-flex mb-3">
                <div class="me-3">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
                <div>
                    <h5>Partage de fichiers intégré</h5>
                    <p class="text-muted">Téléchargez, organisez et partagez vos documents importants directement dans la plateforme.</p>
                </div>
            </div>
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
                <div>
                    <h5>Communication en temps réel</h5>
                    <p class="text-muted">Discutez avec votre équipe via la messagerie intégrée et recevez des notifications instantanées.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?> 