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
    <title>Plan d'apprentissage - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .learning-plan-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .learning-plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .semester-selector {
            display: flex;
            gap: 1rem;
        }

        .semester-button {
            padding: 0.5rem 1rem;
            border: 1px solid var(--primary-color);
            border-radius: 5px;
            background: none;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s;
        }

        .semester-button.active {
            background: var(--primary-color);
            color: white;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .course-card {
            background: var(--light-blue);
            border-radius: 10px;
            padding: 1.5rem;
            transition: transform 0.3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .course-title {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .course-credits {
            background: var(--primary-color);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .course-progress {
            margin: 1rem 0;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-color);
            border-radius: 4px;
            transition: width 0.3s;
        }

        .course-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .milestone-timeline {
            margin-top: 2rem;
            padding: 2rem;
            background: var(--light-blue);
            border-radius: 10px;
        }

        .timeline-header {
            margin-bottom: 1.5rem;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 2px;
            background: var(--primary-color);
        }

        .milestone {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 1.5rem;
        }

        .milestone::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
        }

        .milestone.completed::before {
            background: var(--success-color);
        }

        .milestone-date {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.3rem;
        }

        .milestone-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .milestone-description {
            font-size: 0.9rem;
            color: #666;
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
                <a href="plan-apprentissage.php" class="nav-item active">
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
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>


        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="learning-plan-container">
                <div class="learning-plan-header">
                    <h1>Plan d'apprentissage</h1>
                    <div class="semester-selector">
                        <button class="semester-button">Semestre 1</button>
                        <button class="semester-button active">Semestre 2</button>
                    </div>
                </div>

                <div class="course-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Mathématiques</div>
                            <div class="course-credits">6 ECTS</div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">Progression : 75%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="course-stats">
                            <span>12 chapitres</span>
                            <span>9 complétés</span>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Programmation</div>
                            <div class="course-credits">8 ECTS</div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">Progression : 60%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 60%"></div>
                            </div>
                        </div>
                        <div class="course-stats">
                            <span>15 chapitres</span>
                            <span>9 complétés</span>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Base de données</div>
                            <div class="course-credits">4 ECTS</div>
                        </div>
                        <div class="course-progress">
                            <div class="progress-label">Progression : 90%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="course-stats">
                            <span>8 chapitres</span>
                            <span>7 complétés</span>
                        </div>
                    </div>
                </div>

                <div class="milestone-timeline">
                    <div class="timeline-header">
                        <h2>Jalons importants</h2>
                    </div>
                    <div class="timeline">
                        <div class="milestone completed">
                            <div class="milestone-date">15 février 2024</div>
                            <div class="milestone-title">Examen de mi-semestre</div>
                            <div class="milestone-description">Évaluation des connaissances acquises en mathématiques et programmation.</div>
                        </div>

                        <div class="milestone">
                            <div class="milestone-date">1 mars 2024</div>
                            <div class="milestone-title">Projet de Base de données</div>
                            <div class="milestone-description">Conception et implémentation d'une base de données relationnelle.</div>
                        </div>

                        <div class="milestone">
                            <div class="milestone-date">15 avril 2024</div>
                            <div class="milestone-title">Examen final</div>
                            <div class="milestone-description">Évaluation finale couvrant l'ensemble des matières du semestre.</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Semester selection
            const semesterButtons = document.querySelectorAll('.semester-button');
            semesterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    semesterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Course card hover effect
            const courseCards = document.querySelectorAll('.course-card');
            courseCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html> 