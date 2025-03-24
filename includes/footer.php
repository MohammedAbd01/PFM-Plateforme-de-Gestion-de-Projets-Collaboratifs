    </main>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="100">
                <h5 class="mb-3">
    <img src="logo_collabsphere-r.png" alt="Logo CollabSphere" style="height: 40px; margin-right: 10px; color: aliceblue;">
    CollabSphere
</h5>

                    <p class="text-muted">Votre plateforme complète pour la gestion efficace de projets collaboratifs.</p>
                    <div class="social-icons mt-3">
                        <a href="https://linkin.bio/collabsphere/" class="text-white me-3" target="_blank"><i class="bi bi-linkedin"></i></a>
                        <a href="https://github.com/MohammedAbd01/PFM-Plateforme-de-Gestion-de-Projets-Collaboratifs" class="text-white" target="_blank"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                    <h5 class="mb-3">Liens Rapides</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Accueil</a></li>
                        <?php if (isset($_SESSION['utilisateur'])) : ?>
                        <li class="mb-2"><a href="tableau_de_bord.php"  style="color: white;><i class="bi bi-chevron-right me-2"></i>Tableau de bord</a></li>
                        <li class="mb-2"><a href="profil.php" style="color: white;><i class="bi bi-chevron-right me-2"></i>Mon profil</a></li>
                        <?php else : ?>
                        <li class="mb-2"><a href="connexion.php"  style="color: white;><i class="bi bi-chevron-right me-2"></i>Connexion</a></li>
                        <li class="mb-2"><a href="inscription.php" style="color: white;><i class="bi bi-chevron-right me-2"></i>Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <h5 class="mb-3">Contactez-nous</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>BP : 577, Route de Casa, Settat</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>contact_fsts@uhp.ac.ma</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>05 23 40 07 36</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
        </div>
    </footer>

    <!-- Scripts Bootstrap, jQuery, AOS et personnalisés -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialisation des animations AOS
        AOS.init({
            duration: 800,
            easing: 'ease',
            once: true
        });
        
        // Activer les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Effet d'entrée sur les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.classList.add('fade-in');
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html> 