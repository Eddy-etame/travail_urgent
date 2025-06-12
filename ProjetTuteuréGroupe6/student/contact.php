<?php
session_start();
require_once '../php/database.php';

// Vérification de la session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.php?role=student');
    exit();
}

$user_id = $_SESSION['user_id'];
$student_info = null;

try {
    // Get student's information
    $stmt_student = $pdo->prepare("
        SELECT
            e.id AS student_id,
            e.nom,
            e.prenom,
            u.matricule,
            c.nom_classe
        FROM
            etudiants e
        JOIN
            users u ON e.id_user = u.id
        LEFT JOIN
            etudiant_classe ec ON e.id = ec.id_etudiant
        LEFT JOIN
            classes c ON ec.id_classe = c.id
        WHERE
            e.id_user = :user_id
    ");
    $stmt_student->bindParam(':user_id', $user_id);
    $stmt_student->execute();
    $student_info = $stmt_student->fetch();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nous contacter - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .contact-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .contact-header {
            margin-bottom: 2rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .contact-form {
            padding: 2rem;
            background: var(--light-blue);
            border-radius: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #eee;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        .submit-button:hover {
            background-color: #0056b3;
        }

        .contact-info {
            padding: 2rem;
            background: var(--light-blue);
            border-radius: 10px;
        }

        .contact-method {
            margin-bottom: 2rem;
        }

        .contact-method h3 {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-method i {
            color: var(--primary-color);
        }

        .contact-method p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: transform 0.3s;
        }

        .social-link:hover {
            transform: translateY(-3px);
        }

        .map-container {
            grid-column: 1 / -1;
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-section">
                <img src="../images/student-avatar.png" alt="Profile" class="profile-image">
                <h3 class="profile-name"><?php echo htmlspecialchars($student_info['prenom'] . ' ' . $student_info['nom']); ?></h3>
                <p class="profile-role">Étudiant</p>
            </div>

            <div class="nav-section">
                <p class="nav-title">Apprentissage</p>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    Tableau de bord
                </a>
                <a href="emploi-du-temps.php" class="nav-item">
                    <i class="fas fa-calendar"></i>
                    Emploi du temps
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fas fa-bell"></i>
                    Notifications
                </a>
                <a href="messages.php" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
                <a href="plan-apprentissage.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    Plan d'apprentissage
                </a>
            </div>

            <div class="nav-section">
                <p class="nav-title">Aide & Support</p>
                <a href="aide.php" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    Aide/Signalement
                </a>
                <a href="contact.php" class="nav-item active">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="contact-container">
                <div class="contact-header">
                    <h1>Nous contacter</h1>
                    <p>N'hésitez pas à nous contacter pour toute question ou demande d'information.</p>
                </div>

                <div class="contact-grid">
                    <div class="contact-form">
                        <h2>Envoyez-nous un message</h2>
                        <form action="submit_contact.php" method="POST">
                            <div class="form-group">
                                <label for="subject">Sujet</label>
                                <select id="subject" name="subject" required>
                                    <option value="">Sélectionnez un sujet</option>
                                    <option value="general">Question générale</option>
                                    <option value="technical">Support technique</option>
                                    <option value="administrative">Question administrative</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" required></textarea>
                            </div>

                            <button type="submit" class="submit-button">Envoyer le message</button>
                        </form>
                    </div>

                    <div class="contact-info">
                        <div class="contact-method">
                            <h3><i class="fas fa-map-marker-alt"></i> Adresse</h3>
                            <p>123 Rue de l'École</p>
                            <p>75000 Paris, France</p>
                        </div>

                        <div class="contact-method">
                            <h3><i class="fas fa-clock"></i> Horaires d'ouverture</h3>
                            <p>Lundi - Vendredi : 9h00 - 17h00</p>
                            <p>Fermé les weekends et jours fériés</p>
                        </div>

                        <div class="contact-method">
                            <h3><i class="fas fa-phone"></i> Téléphone</h3>
                            <p>+33 1 23 45 67 89</p>
                        </div>

                        <div class="contact-method">
                            <h3><i class="fas fa-envelope"></i> Email</h3>
                            <p>contact@ecole.fr</p>
                        </div>

                        <div class="contact-method">
                            <h3><i class="fas fa-share-alt"></i> Réseaux sociaux</h3>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="map-container">
                        <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=2.3319,48.8336,2.3519,48.8536&layer=mapnik"></iframe>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handling
            const contactForm = document.querySelector('form');
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Here you would typically handle the form submission
                // For now, we'll just show an alert
                alert('Message envoyé avec succès !');
                this.reset();
            });

            // Social links hover effect
            const socialLinks = document.querySelectorAll('.social-link');
            socialLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html> 