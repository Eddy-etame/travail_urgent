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
    <title>Aide & Signalement - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .help-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .help-header {
            margin-bottom: 2rem;
        }

        .help-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .tab-button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            background: var(--light-blue);
            cursor: pointer;
            transition: all 0.3s;
        }

        .tab-button.active {
            background: var(--primary-color);
            color: white;
        }

        .faq-section {
            margin-bottom: 2rem;
        }

        .faq-item {
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .faq-question {
            padding: 1rem;
            background: var(--light-blue);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-answer {
            padding: 1rem;
            display: none;
            background: white;
        }

        .faq-answer.active {
            display: block;
        }

        .report-form {
            display: none;
        }

        .report-form.active {
            display: block;
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
</head>
            padding: 0.8rem;
            border: 1px solid #eee;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 150px;
<body>
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
        }

        .submit-button:hover {
            background-color: #0056b3;
        }

        .contact-info {
            background: var(--light-blue);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .contact-info h3 {
            margin-bottom: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .contact-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }
    </style>
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
                <a href="aide.php" class="nav-item active">
                    <i class="fas fa-question-circle"></i>
                    Aide/Signalement
                </a>
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="help-container">
                <div class="help-header">
                    <h1>Aide & Signalement</h1>
                </div>

                <div class="help-tabs">
                    <button class="tab-button active" data-tab="faq">FAQ</button>
                    <button class="tab-button" data-tab="report">Signaler un problème</button>
                </div>

                <div class="faq-section active" id="faq">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Comment puis-je accéder à mes notes ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Vous pouvez accéder à vos notes depuis le tableau de bord en cliquant sur la section "Résultats récents" ou en consultant votre relevé de notes complet dans la section correspondante.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Comment contacter un professeur ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Vous pouvez contacter vos professeurs via la section "Messages" de votre espace étudiant. Sélectionnez le professeur dans la liste des contacts et envoyez votre message.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Comment signaler une absence ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Pour signaler une absence, utilisez le formulaire de signalement dans l'onglet "Signaler un problème" et sélectionnez la catégorie "Absence". Joignez un justificatif si nécessaire.
                        </div>
                    </div>
                </div>

                <div class="report-form" id="report">
                    <form action="submit_report.php" method="POST">
                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <select id="category" name="category" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <option value="technical">Problème technique</option>
                                <option value="absence">Signalement d'absence</option>
                                <option value="grades">Problème de notes</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="attachment">Pièce jointe (optionnel)</label>
                            <input type="file" id="attachment" name="attachment">
                        </div>

                        <button type="submit" class="submit-button">Envoyer le signalement</button>
                    </form>
                </div>

                <div class="contact-info">
                    <h3>Contacts utiles</h3>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>support@ecole.fr</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>01 23 45 67 89</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Disponible du lundi au vendredi, 9h-17h</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const faqSection = document.getElementById('faq');
            const reportSection = document.getElementById('report');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const tab = this.dataset.tab;
                    if (tab === 'faq') {
                        faqSection.classList.add('active');
                        reportSection.classList.remove('active');
                    } else {
                        faqSection.classList.remove('active');
                        reportSection.classList.add('active');
                    }
                });
            });

            // FAQ accordion
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const icon = this.querySelector('i');
                    
                    answer.classList.toggle('active');
                    icon.classList.toggle('fa-chevron-up');
                    icon.classList.toggle('fa-chevron-down');
                });
            });
        });
    </script>
</body>
</html> 