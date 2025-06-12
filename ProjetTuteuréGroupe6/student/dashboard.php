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
$student_notes = [];
$overall_average = 0;
$total_courses = 0;
$completed_courses = 0;

try {
    // Get student's information and class
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

    if ($student_info) {
        $student_id = $student_info['student_id'];

        // Get all courses (matières)
        $stmt_courses = $pdo->prepare("SELECT * FROM matieres");
        $stmt_courses->execute();
        $courses = $stmt_courses->fetchAll();
        $total_courses = count($courses);

        // Initialize course progress array
        $course_progress = [];
        foreach ($courses as $course) {
            $course_progress[$course['id']] = [
                'name' => $course['nom_matiere'],
                'progress' => 0,
                'has_grades' => false
            ];
        }

        // Get all notes for this student with latest date for each subject
        $stmt_notes = $pdo->prepare("
            SELECT
                m.id as matiere_id,
                m.nom_matiere,
                n.note,
                n.commentaire,
                en.nom AS enseignant_nom,
                en.prenom AS enseignant_prenom,
                n.date_creation,
                (SELECT AVG(note) 
                 FROM notes 
                 WHERE id_etudiant = :student_id 
                 AND id_matiere = m.id) as average_note
            FROM notes n
            JOIN matieres m ON n.id_matiere = m.id
            JOIN enseignants en ON n.id_enseignant = en.id
            WHERE n.id_etudiant = :student_id
            ORDER BY m.nom_matiere, n.date_creation DESC
        ");
        $stmt_notes->bindParam(':student_id', $student_id);
        $stmt_notes->execute();
        $student_notes = $stmt_notes->fetchAll();

        // Calculate progress for each course based on actual grades
        $total_progress = 0;
        $courses_with_grades = 0;
        
        foreach ($student_notes as $note) {
            $matiere_id = $note['matiere_id'];
            if (isset($course_progress[$matiere_id])) {
                // Calculate progress as percentage (note out of 20)
                $progress = ($note['average_note'] / 20) * 100;
                $course_progress[$matiere_id]['progress'] = round($progress);
                $course_progress[$matiere_id]['has_grades'] = true;
                
                $total_progress += $progress;
                $courses_with_grades++;

                if ($progress >= 50) {  // Consider a course completed if grade is 50% or higher
                    $completed_courses++;
                }
            }
        }

        // Calculate overall progress
        $overall_progress = $courses_with_grades > 0 ? round($total_progress / $courses_with_grades) : 0;

    } else {
        $_SESSION['error'] = "Vos informations d'étudiant n'ont pas été trouvées.";
        header('Location: ../php/logout.php');
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des données de l'étudiant : " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
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
                <a href="dashboard.php" class="nav-item active">
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
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>

        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Card -->
            <div class="welcome-card">
            <div class="welcome-text">
                <h1>Bonjour <?php echo htmlspecialchars($student_info['prenom']); ?>,</h1>
                <p>Vous avez complété <?php echo $overall_progress; ?>% de vos cours</p>
                <p>Continuez comme ça et améliorez vos notes pour obtenir une bourse</p>
                <a href="grades.php" class="view-result" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; border-radius: 5px; text-decoration: none;">Voir les résultats</a>
            </div>
                <img src="../images/student-illustration.png" alt="Student" class="welcome-image">
            </div>

            <div class="content-grid">
                <!-- Courses Section -->
                <div class="courses-section">
                    <div class="section-header">
                        <h2>Mes Cours</h2>
                        <div class="search-bar">
                            <input type="text" placeholder="Rechercher un cours">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <!-- Course List -->
                    <?php foreach ($course_progress as $id => $course): ?>
                    <div class="course-item">
                        <div class="course-icon" style="background: <?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>">
                            <?php echo strtoupper(substr($course['name'], 0, 1)); ?>
                        </div>
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                            <p><?php echo $student_info['nom_classe']; ?></p>
                        </div>
                        <div class="course-progress">
                            <span><?php echo $course['has_grades'] ? $course['progress'] . '%' : 'No grades'; ?></span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%; background-color: <?php 
                                    echo $course['progress'] < 50 ? 'var(--error-color)' : ($course['progress'] < 75 ? '#ffc107' : 'var(--secondary-color)');
                                ?>"></div>
                            </div>
                </div>
                </div>
                    <?php endforeach; ?>

                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="#" class="view-more">View More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Colonne de droite -->
                <div class="right-column">
                    <!-- Résultats récents -->
                    <div class="results-section">
                        <div class="section-header">
                            <h2>Résultats récents</h2>
                            <a href="#" class="view-more">Voir plus <i class="fas fa-arrow-right"></i></a>
                </div>

                        <?php 
                        // Get unique latest results per subject
                        $shown_subjects = [];
                        $recent_results = [];
                        foreach ($student_notes as $note) {
                            if (!isset($shown_subjects[$note['matiere_id']])) {
                                $recent_results[] = $note;
                                $shown_subjects[$note['matiere_id']] = true;
                                if (count($recent_results) >= 5) break;
                            }
                        }
                        
                        foreach ($recent_results as $result): 
                        ?>
                        <div class="result-item">
                            <div class="result-info">
                                <h4><?php echo htmlspecialchars($result['nom_matiere']); ?></h4>
                                <p>Latest Grade - <?php echo date('d/m/Y', strtotime($result['date_creation'])); ?></p>
                            </div>
                            <div class="result-score" style="color: <?php 
                                echo $result['note'] < 10 ? 'var(--error-color)' : ($result['note'] < 15 ? '#ffc107' : 'var(--secondary-color)');
                            ?>">
                                <?php echo $result['note']; ?>/20
                            </div>
                        </div>
                                <?php endforeach; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="action-info">
                                <h4>Congé</h4>
                                <p>Vous souhaitez prendre un congé ?</p>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </div>

                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="action-info">
                                <h4>Plainte</h4>
                                <p>Vous souhaitez déposer une plainte contre quelqu'un ?</p>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/student-dashboard.js"></script>
</body>
</html>