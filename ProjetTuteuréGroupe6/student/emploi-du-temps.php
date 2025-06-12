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
    <title>Emploi du temps - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .schedule-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .week-navigation {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .week-navigation button {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .week-navigation button:hover {
            background-color: #0056b3;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: 100px repeat(5, 1fr);
            gap: 1px;
            background-color: #eee;
            border-radius: 10px;
            overflow: hidden;
        }

        .time-slot, .day-header, .schedule-cell {
            padding: 1rem;
            background: var(--white);
            text-align: center;
        }

        .day-header {
            font-weight: bold;
            background-color: var(--primary-color);
            color: var(--white);
        }

        .time-slot {
            font-weight: bold;
            background-color: var(--light-blue);
        }

        .schedule-cell {
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s;
        }

        .schedule-cell:hover {
            background-color: var(--light-blue);
        }

        .course-item {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 0.5rem;
            border-radius: 5px;
            margin: 0.2rem;
            width: 90%;
            font-size: 0.9rem;
        }

        .empty-slot {
            color: #666;
            font-style: italic;
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
                <a href="emploi-du-temps.php" class="nav-item active">
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
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>


        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="schedule-container">
                <div class="schedule-header">
                    <h1>Emploi du temps</h1>
                    <div class="week-navigation">
                        <button id="prevWeek"><i class="fas fa-chevron-left"></i> Semaine précédente</button>
                        <span id="currentWeek">Semaine du <?php echo date('d/m/Y'); ?></span>
                        <button id="nextWeek">Semaine suivante <i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="schedule-grid">
                    <div class="time-slot"></div>
                    <div class="day-header">Lundi</div>
                    <div class="day-header">Mardi</div>
                    <div class="day-header">Mercredi</div>
                    <div class="day-header">Jeudi</div>
                    <div class="day-header">Vendredi</div>

                    <?php
                    $timeSlots = [
                        '08:00 - 10:00',
                        '10:15 - 12:15',
                        '13:30 - 15:30',
                        '15:45 - 17:45'
                    ];

                    foreach ($timeSlots as $time) {
                        echo '<div class="time-slot">' . $time . '</div>';
                        for ($i = 0; $i < 5; $i++) {
                            echo '<div class="schedule-cell">';
                            if (rand(0, 1)) { // Simulated course data
                                echo '<div class="course-item">';
                                echo 'Cours';
                                echo '</div>';
                            } else {
                                echo '<span class="empty-slot">Libre</span>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prevWeekBtn = document.getElementById('prevWeek');
            const nextWeekBtn = document.getElementById('nextWeek');
            const currentWeekSpan = document.getElementById('currentWeek');

            let currentDate = new Date();

            function updateWeekDisplay() {
                const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
                currentWeekSpan.textContent = 'Semaine du ' + currentDate.toLocaleDateString('fr-FR', options);
            }

            prevWeekBtn.addEventListener('click', function() {
                currentDate.setDate(currentDate.getDate() - 7);
                updateWeekDisplay();
            });

            nextWeekBtn.addEventListener('click', function() {
                currentDate.setDate(currentDate.getDate() + 7);
                updateWeekDisplay();
            });
        });
    </script>
</body>
</html> 