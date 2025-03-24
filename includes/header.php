<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$utilisateurConnecte = isset($_SESSION['utilisateur']);
$estAdmin = $utilisateurConnecte && $_SESSION['utilisateur']['role'] === 'Administrateur';
$pageCourante = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme de Gestion de Projets</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Police Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Notre CSS personnalisé -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container" style="color: aliceblue;">
            <a class="navbar-brand" href="index.php" data-aos="fade-right" data-aos-duration="800">
    <img src="logo_collabsphere-r.png" alt="CollabSphere" style="height: 40px; color: aliceblue;">
</a>

                    CollabSphere
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Afficher la navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <?php if (isset($_SESSION['utilisateur'])) : ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item" data-aos="fade-down" data-aos-delay="100">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'tableau_de_bord.php' ? 'active' : '' ?>" href="tableau_de_bord.php">
                                <i class="bi bi-speedometer2 me-1"></i>Tableau de Bord
                            </a>
                        </li>
                        
                        <?php if ($_SESSION['utilisateur']['role'] === 'Administrateur') : ?>
                        <li class="nav-item" data-aos="fade-down" data-aos-delay="200">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'administration.php' ? 'active' : '' ?>" href="administration.php">
                                <i class="bi bi-gear me-1"></i>Administration
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown" data-aos="fade-down" data-aos-delay="300">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['utilisateur']['nom_utilisateur']) ?>
                                <?php if ($_SESSION['utilisateur']['role'] === 'Administrateur') : ?>
                                <span class="badge bg-danger">Admin</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="profil.php">
                                        <i class="bi bi-person me-2"></i>Mon Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="deconnexion.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <?php else : ?>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item" data-aos="fade-down" data-aos-delay="100">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'connexion.php' ? 'active' : '' ?>" href="connexion.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item" data-aos="fade-down" data-aos-delay="200">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'inscription.php' ? 'active' : '' ?>" href="inscription.php">
                                <i class="bi bi-person-plus me-1"></i>Inscription
                            </a>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main>
        <?php if (isset($_SESSION['message'])) : ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        </div>
        <?php 
        // Une fois affiché, supprimer le message de la session
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
        <?php endif; ?>
    </main>
</body>
</html>