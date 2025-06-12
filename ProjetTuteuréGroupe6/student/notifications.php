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
    <title>Notifications - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .notifications-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .notifications-filters {
            display: flex;
            gap: 1rem;
        }

        .filter-button {
            background: var(--light-blue);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filter-button.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 10px;
            background: var(--light-blue);
            transition: transform 0.3s;
        }

        .notification-item:hover {
            transform: translateX(5px);
        }

        .notification-icon {
            background: var(--white);
            padding: 0.8rem;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .notification-message {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            font-size: 0.8rem;
            color: #999;
        }

        .notification-actions {
            display: flex;
            gap: 1rem;
        }

        .notification-action {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.9rem;
        }

        .notification-action:hover {
            text-decoration: underline;
        }

        .unread {
            background: #e3f2fd;
        }

        .mark-all-read {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .mark-all-read:hover {
            background-color: #0056b3;
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
                <a href="notifications.php" class="nav-item active">
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
            <div class="notifications-container">
                <div class="notifications-header">
                    <h1>Notifications</h1>
                    <div class="notifications-filters">
                        <button class="mark-all-read">
                            <i class="fas fa-check-double"></i>
                            Tout marquer comme lu
                        </button>
                        <button class="filter-button active">Toutes</button>
                        <button class="filter-button">Non lues</button>
                        <button class="filter-button">Cours</button>
                        <button class="filter-button">Notes</button>
                    </div>
                </div>

                <div class="notification-list">
                    <!-- Example notifications -->
                    <div class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Nouvelle note disponible</div>
                            <div class="notification-message">
                                Votre note pour le cours de Mathématiques est maintenant disponible.
                            </div>
                            <div class="notification-time">Il y a 2 heures</div>
                        </div>
                        <div class="notification-actions">
                            <button class="notification-action">Voir</button>
                            <button class="notification-action">Marquer comme lu</button>
                        </div>
                    </div>

                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Nouveau cours ajouté</div>
                            <div class="notification-message">
                                Le cours "Introduction à Python" a été ajouté à votre emploi du temps.
                            </div>
                            <div class="notification-time">Hier</div>
                        </div>
                        <div class="notification-actions">
                            <button class="notification-action">Voir le cours</button>
                        </div>
                    </div>

                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Rappel d'examen</div>
                            <div class="notification-message">
                                Rappel : Examen de Bases de données demain à 14h00.
                            </div>
                            <div class="notification-time">Il y a 2 jours</div>
                        </div>
                        <div class="notification-actions">
                            <button class="notification-action">Voir les détails</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter buttons functionality
            const filterButtons = document.querySelectorAll('.filter-button');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Mark all as read functionality
            const markAllReadBtn = document.querySelector('.mark-all-read');
            markAllReadBtn.addEventListener('click', function() {
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                });
            });

            // Individual notification actions
            const notificationActions = document.querySelectorAll('.notification-action');
            notificationActions.forEach(action => {
                action.addEventListener('click', function() {
                    const notificationItem = this.closest('.notification-item');
                    if (this.textContent === 'Marquer comme lu') {
                        notificationItem.classList.remove('unread');
                    }
                });
            });
        });
    </script>
</body>
</html> 