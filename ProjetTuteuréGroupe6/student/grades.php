<?php
session_start();
require_once '../php/database.php';

// Verify student session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.php?role=student');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get student ID
    $stmt_student = $pdo->prepare("SELECT id FROM etudiants WHERE id_user = :user_id");
    $stmt_student->bindParam(':user_id', $user_id);
    $stmt_student->execute();
    $student_id = $stmt_student->fetchColumn();

    if (!$student_id) {
        $_SESSION['error'] = "Informations étudiant introuvables.";
        header('Location: ../php/logout.php');
        exit();
    }

    // Get all grades for the student with subject names and teacher info
    $stmt_grades = $pdo->prepare("
        SELECT m.nom_matiere, n.note, n.commentaire, n.date_creation, en.nom AS enseignant_nom, en.prenom AS enseignant_prenom
        FROM notes n
        JOIN matieres m ON n.id_matiere = m.id
        JOIN enseignants en ON n.id_enseignant = en.id
        WHERE n.id_etudiant = :student_id
        ORDER BY m.nom_matiere, n.date_creation DESC
    ");
    $stmt_grades->bindParam(':student_id', $student_id);
    $stmt_grades->execute();
    $grades = $stmt_grades->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des notes : " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mes Résultats - Keyce</title>
    <link rel="stylesheet" href="../css/student-dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile-section">
                <img src="../images/student-avatar.png" alt="Profile" class="profile-image" />
                <h3 class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Étudiant'); ?></h3>
                <p class="profile-role">Étudiant</p>
            </div>
            <div class="nav-section">
                <p class="nav-title">Apprentissage</p>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i> Tableau de bord
                </a>
                <a href="emploi-du-temps.php" class="nav-item">
                    <i class="fas fa-calendar"></i> Emploi du temps
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="messages.php" class="nav-item">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a href="plan-apprentissage.php" class="nav-item">
                    <i class="fas fa-book"></i> Plan d'apprentissage
                </a>
            </div>
            <div class="nav-section">
                <p class="nav-title">Aide & Support</p>
                <a href="aide.php" class="nav-item">
                    <i class="fas fa-question-circle"></i> Aide/Signalement
                </a>
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i> Nous contacter
                </a>
            </div>
        </aside>
        <main class="main-content">
            <h1>Mes Résultats</h1>
            <?php if (empty($grades)): ?>
                <p>Aucun résultat disponible.</p>
            <?php else: ?>
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                            <th>Date</th>
                            <th>Enseignant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['nom_matiere']); ?></td>
                                <td><?php echo htmlspecialchars($grade['note']); ?>/20</td>
                                <td><?php echo htmlspecialchars($grade['commentaire']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($grade['date_creation'])); ?></td>
                                <td><?php echo htmlspecialchars($grade['enseignant_prenom'] . ' ' . $grade['enseignant_nom']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form action="download_grades.php" method="POST" style="margin-top: 20px;">
                    <button type="submit" class="download-pdf-btn">Télécharger PDF</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
