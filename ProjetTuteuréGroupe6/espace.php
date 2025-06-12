<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Keyce - Plateforme de Gestion des Notes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/espace.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="images/logo_keyce.JPEG" alt="Logo Keyce">
            </div>
            <nav class="main-nav">
                <a href="#accueil">Accueil</a>
                <a href="#actualites">Actualités</a>
                <a href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="welcome-banner">
            <div class="banner-content">
                <h1>Bienvenue sur la Plateforme de Gestion des Notes</h1>
                <p>Accédez à votre espace personnel pour gérer vos notes académiques</p>
            </div>
        </section>

        <section class="choice-section">
            <h2>Choisissez votre espace</h2>
            <div class="cards-container">
                <div class="card admin">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Espace Administrateur</h3>
                    <p>Gérez l'ensemble de la plateforme et ses utilisateurs</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Gestion des utilisateurs</li>
                        <li><i class="fas fa-check"></i> Suivi des activités</li>
                        <li><i class="fas fa-check"></i> Rapports et statistiques</li>
                    </ul>
                    <a href="index.php?role=admin" class="btn-access">Accéder <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="card teacher">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Espace Enseignant</h3>
                    <p>Enregistrez et consultez les notes de vos étudiants</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Saisie des notes</li>
                        <li><i class="fas fa-check"></i> Consultation des classes</li>
                        <li><i class="fas fa-check"></i> Communication avec les étudiants</li>
                    </ul>
                    <a href="index.php?role=teacher" class="btn-access">Accéder <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="card student">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Espace Étudiant</h3>
                    <p>Consultez vos notes et votre progression académique</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Consultation des notes</li>
                        <li><i class="fas fa-check"></i> Suivi des moyennes</li>
                        <li><i class="fas fa-check"></i> Accès aux ressources</li>
                    </ul>
                    <a href="index.php?role=student" class="btn-access">Accéder <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <section class="news-section">
            <h2>Actualités</h2>
            <div class="news-container">
                <div class="news-card">
                    <div class="news-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Dates importantes</h3>
                    <ul>
                        <li>Inscriptions : 1er - 15 septembre</li>
                        <li>Début des cours : 20 septembre</li>
                        <li>Examens : 15 - 30 décembre</li>
                    </ul>
                </div>
                <div class="news-card">
                    <div class="news-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Annonces</h3>
                    <ul>
                        <li>Nouvelle fonctionnalité : Export PDF</li>
                        <li>Maintenance : 25 août 2024</li>
                        <li>Formation : 5 septembre 2024</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <h2>Besoin d'aide ?</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <p>support@keyce.fr</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <p>01 23 45 67 89</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <p>Lun-Ven: 9h-18h</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="images/logo_keyce.JPEG" alt="Logo Keyce">
            </div>
            <div class="footer-links">
                <a href="#">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Keyce. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>