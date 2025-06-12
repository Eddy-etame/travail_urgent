<?php
// director/dashboard.php
session_start();
require_once '../php/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

try {
    // Fetch data for dashboard
    $stmt = $pdo->query("SELECT COUNT(*) FROM etudiants");
    $total_students = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM enseignants");
    $total_teachers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
    $total_classes = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM matieres");
    $total_subjects = $stmt->fetchColumn();

    // Fetch students
    $stmt = $pdo->query("SELECT e.id, e.nom, e.prenom, u.matricule, c.nom_classe 
                         FROM etudiants e 
                         JOIN users u ON e.id_user = u.id 
                         LEFT JOIN etudiant_classe ec ON e.id = ec.id_etudiant 
                         LEFT JOIN classes c ON ec.id_classe = c.id");
    $students = $stmt->fetchAll();

    // Fetch teachers
    $stmt = $pdo->query("SELECT e.id, e.nom, e.prenom, u.matricule 
                         FROM enseignants e 
                         JOIN users u ON e.id_user = u.id");
    $teachers = $stmt->fetchAll();

    // Fetch classes
    $stmt = $pdo->query("SELECT id, nom_classe FROM classes");
    $classes = $stmt->fetchAll();

    // Fetch subjects
    $stmt = $pdo->query("SELECT m.id, m.nom_matiere, c.nom_classe 
                         FROM matieres m 
                         LEFT JOIN classes c ON m.id_classe = c.id");
    $subjects = $stmt->fetchAll();

    // Fetch notifications (remove 'audience' column)
    $stmt = $pdo->query("SELECT id, message, created_at, is_read FROM notifications ORDER BY created_at DESC");
    $notifications = $stmt->fetchAll() ?: []; // Ensure $notifications is an array

    // Fetch average note per class for the graph
    $classNotes = [];
    try {
        $stmt = $pdo->query("SELECT c.id, c.nom_classe, AVG(n.note) as avg_note, COUNT(n.id) as note_count
            FROM classes c
            LEFT JOIN matieres m ON c.id = m.id_classe
            LEFT JOIN notes n ON m.id = n.id_matiere
            GROUP BY c.id, c.nom_classe
            ORDER BY c.nom_classe");
        $classNotes = $stmt->fetchAll();
    } catch (PDOException $e) {
        $classNotes = [];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des données : " . $e->getMessage();
    $notifications = []; // Fallback to empty array on error
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Directeur</title>
    <link rel="stylesheet" href="../css/director.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/director.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo_keyce.JPEG" alt="Logo Keyce" class="logo">
                <h2>Espace Directeur</h2>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-item active" id="dashboardNav" href="#"><i class="fas fa-home"></i> Tableau de bord</a>
                <a class="nav-item" id="notificationsNav" href="#"><i class="fas fa-bell"></i> Notifications</a>
                <a class="nav-item" id="studentsNav" href="#"><i class="fas fa-users"></i> Étudiants</a>
                <a class="nav-item" id="teachersNav" href="#"><i class="fas fa-chalkboard-teacher"></i> Enseignants</a>
                <a class="nav-item" id="classesNav" href="#"><i class="fas fa-school"></i> Classes</a>
                <a class="nav-item" id="subjectsNav" href="#"><i class="fas fa-book"></i> Matières</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php" class="btn btn-danger" style="width:100%;margin-top:20px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </aside>
        <main class="main-content">
            <section id="dashboard-section" class="content-section">
                <h1>Tableau de bord de l'administrateur</h1>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="message message-success">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message message-error">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <section class="dashboard-section">
                    <h2>Bienvenue, Directeur</h2>
                    <div class="dashboard-stats-cards" style="display: flex; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
                        <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem 2.5rem; min-width: 180px; flex: 1; text-align: center;">
                            <div style="font-size: 2.2rem; color: #007bff; margin-bottom: 0.5rem;"><i class="fas fa-users"></i></div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #222;"><?php echo $total_students; ?></div>
                            <div style="font-size: 1.1rem; color: #555;">Étudiants</div>
                        </div>
                        <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem 2.5rem; min-width: 180px; flex: 1; text-align: center;">
                            <div style="font-size: 2.2rem; color: #28a745; margin-bottom: 0.5rem;"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #222;"><?php echo $total_teachers; ?></div>
                            <div style="font-size: 1.1rem; color: #555;">Enseignants</div>
                        </div>
                        <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem 2.5rem; min-width: 180px; flex: 1; text-align: center;">
                            <div style="font-size: 2.2rem; color: #ffc107; margin-bottom: 0.5rem;"><i class="fas fa-school"></i></div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #222;"><?php echo $total_classes; ?></div>
                            <div style="font-size: 1.1rem; color: #555;">Classes</div>
                        </div>
                        <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem 2.5rem; min-width: 180px; flex: 1; text-align: center;">
                            <div style="font-size: 2.2rem; color: #e83e8c; margin-bottom: 0.5rem;"><i class="fas fa-book"></i></div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #222;"><?php echo $total_subjects; ?></div>
                            <div style="font-size: 1.1rem; color: #555;">Matières</div>
                        </div>
                    </div>
                    <div class="performance-chart-container">
                        <h2>Performance globale des classes</h2>
                        <canvas id="performanceChart" width="400" height="200"></canvas>
                    </div>
                    <!-- New: Subjects per Class Table -->
                    <div class="subjects-per-class-container" style="margin-top:2rem;">
                        <h2>Matières par Classe</h2>
                        <table style="width:100%;border-collapse:collapse;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.07);">
                            <thead>
                                <tr style="background:#f5f5f5;">
                                    <th style="padding:8px 12px;">Classe</th>
                                    <th style="padding:8px 12px;">Matières</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td style="padding:8px 12px;font-weight:bold;">
                                            <?php echo htmlspecialchars($class['nom_classe']); ?>
                                        </td>
                                        <td style="padding:8px 12px;">
                                            <?php 
                                            $class_subjects = array_filter($subjects, function($subject) use ($class) {
                                                return $subject['nom_classe'] === $class['nom_classe'];
                                            });
                                            if (count($class_subjects) > 0) {
                                                echo implode(', ', array_map(function($s) { return htmlspecialchars($s['nom_matiere']); }, $class_subjects));
                                            } else {
                                                echo '<span style="color:#888;">Aucune matière</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- End Subjects per Class Table -->
                </section>
            </section>
            <section id="notifications-section" class="content-section" style="display:none;">
                <h2>Gestion des Notifications</h2>
                <ul class="notifications-list">
                    <?php if (count($notifications) === 0): ?>
                        <li>Aucune notification trouvée.</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                                data-id="<?php echo $notification['id']; ?>">
                                <?php echo htmlspecialchars($notification['message']); ?><br>
                                <small>
                                    <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?><br>
                                </small>
                                <?php if (!$notification['is_read']): ?>
                                    <button class="mark-as-read">Marquer comme lu</button>
                                <?php endif; ?>
                                <button class="delete-notification">Supprimer</button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <a href="add_notifications.php" class="btn-primary" style="margin-top:20px;">Ajouter une notification</a>
            </section>
            <section id="students-section" class="content-section" style="display:none;">
                <h2>Gestion des Étudiants</h2>
                <div class="stats-breakdown">
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1rem 2rem; min-width: 180px; display:inline-block; margin-bottom:1rem;">
                        <div style="font-size: 2rem; color: #007bff;"><i class="fas fa-users"></i></div>
                        <div style="font-size: 2rem; font-weight: bold; color: #222;"><?php echo $total_students; ?></div>
                        <div style="font-size: 1rem; color: #555;">Total Étudiants</div>
                    </div>
                    <table style="width:100%;margin-top:1rem;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.07);border-radius:8px;">
                        <thead>
                            <tr style="background:#f5f5f5;"><th style="padding:8px 12px;">Classe</th><th style="padding:8px 12px;">Nombre d'étudiants</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $class_student_counts = [];
                            foreach ($classes as $class) {
                                $count = 0;
                                foreach ($students as $student) {
                                    if ($student['nom_classe'] === $class['nom_classe']) $count++;
                                }
                                $class_student_counts[$class['nom_classe']] = $count;
                            }
                            foreach ($class_student_counts as $class_name => $count): ?>
                                <tr>
                                    <td style="padding:8px 12px;"> <?php echo htmlspecialchars($class_name); ?> </td>
                                    <td style="padding:8px 12px;"> <?php echo $count; ?> </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;display:flex;gap:1rem;">
                    <a href="inscription_etudiant.php" class="btn-primary">Ajouter un Étudiant</a>
                    <form action="../php/edit_student.php" method="get" style="display:inline;">
                        <button type="submit" class="btn-primary">Gérer</button>
                    </form>
                </div>
                <!-- New: Students Management Table -->
                <div class="students-management-container" style="margin-top:2rem;">
                    <h2>Gestion des Étudiants</h2>
                    <table class="management-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Classe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($student['matricule']); ?></td>
                                    <td><?php echo htmlspecialchars($student['nom_classe'] ?: 'Aucune'); ?></td>
                                    <td>
                                        <button class="btn-primary unified-btn edit-btn"
                                            data-id="<?php echo $student['id']; ?>"
                                            data-nom="<?php echo htmlspecialchars($student['nom']); ?>"
                                            data-prenom="<?php echo htmlspecialchars($student['prenom']); ?>"
                                            data-matricule="<?php echo htmlspecialchars($student['matricule']); ?>"
                                            data-classe="<?php echo htmlspecialchars($student['nom_classe']); ?>"
                                            data-type="student">Modifier</button>
                                        <form action="../php/delete_student.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn-danger unified-btn delete-btn">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- End Students Management Table -->
            </section>
            <section id="teachers-section" class="content-section" style="display:none;">
                <h2>Gestion des Enseignants</h2>
                <div class="stats-breakdown">
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1rem 2rem; min-width: 180px; display:inline-block; margin-bottom:1rem;">
                        <div style="font-size: 2rem; color: #28a745;"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div style="font-size: 2rem; font-weight: bold; color: #222;"><?php echo $total_teachers; ?></div>
                        <div style="font-size: 1rem; color: #555;">Total Enseignants</div>
                    </div>
                    <table class="management-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Matières</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($teacher['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['matricule']); ?></td>
                                    <td>
                                        <?php
                                        // Fetch subjects for this teacher
                                        $stmt = $pdo->prepare("SELECT m.nom_matiere FROM matieres m JOIN enseignant_matiere em ON m.id = em.id_matiere WHERE em.id_enseignant = ?");
                                        $stmt->execute([$teacher['id']]);
                                        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                        echo $subjects ? htmlspecialchars(implode(', ', $subjects)) : '<span style=\'color:#888;\'>Aucune</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn-primary unified-btn edit-btn" 
                                            data-id="<?php echo $teacher['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($teacher['nom']); ?>" 
                                            data-prenom="<?php echo htmlspecialchars($teacher['prenom']); ?>" 
                                            data-matricule="<?php echo htmlspecialchars($teacher['matricule']); ?>" 
                                            data-type="teacher">Modifier</button>
                                        <form action="../php/delete_teacher.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $teacher['id']; ?>">
                                            <button type="submit" class="btn-danger unified-btn delete-btn">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;display:flex;gap:1rem;">
                    <a href="inscription_enseignant.php" class="btn-primary">Ajouter un Enseignant</a>
                </div>
            </section>
            <section id="classes-section" class="content-section" style="display:none;">
                <h2>Gestion des Classes</h2>
                <div class="stats-breakdown">
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1rem 2rem; min-width: 180px; display:inline-block; margin-bottom:1rem;">
                        <div style="font-size: 2rem; color: #ffc107;"><i class="fas fa-school"></i></div>
                        <div style="font-size: 2rem; font-weight: bold; color: #222;"><?php echo $total_classes; ?></div>
                        <div style="font-size: 1rem; color: #555;">Total Classes</div>
                    </div>
                    <table class="management-table">
                        <thead>
                            <tr>
                                <th>Nom de la Classe</th>
                                <th>Nombre d'étudiants</th>
                                <th>Matières</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['nom_classe']); ?></td>
                                    <td>
                                        <?php
                                        $count = 0;
                                        foreach ($students as $student) {
                                            if ($student['nom_classe'] === $class['nom_classe']) $count++;
                                        }
                                        echo $count;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $class_subjects = array_filter($subjects, function($subject) use ($class) {
                                            return $subject['nom_classe'] === $class['nom_classe'];
                                        });
                                        echo $class_subjects ? htmlspecialchars(implode(', ', array_map(function($s) { return $s['nom_matiere']; }, $class_subjects))) : '<span style=\'color:#888;\'>Aucune</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn-primary unified-btn edit-btn" 
                                            data-id="<?php echo $class['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($class['nom_classe']); ?>" 
                                            data-type="class">Modifier</button>
                                        <form action="../php/delete_class.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $class['id']; ?>">
                                            <button type="submit" class="btn-danger unified-btn delete-btn">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;display:flex;gap:1rem;">
                    <a href="../php/add_class.php" class="btn-primary">Ajouter une Classe</a>
                </div>
            </section>
            <section id="subjects-section" class="content-section" style="display:none;">
                <h2>Gestion des Matières</h2>
                <div class="stats-breakdown">
                    <div class="stat-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1rem 2rem; min-width: 180px; display:inline-block; margin-bottom:1rem;">
                        <div style="font-size: 2rem; color: #e83e8c;"><i class="fas fa-book"></i></div>
                        <div style="font-size: 2rem; font-weight: bold; color: #222;"><?php echo $total_subjects; ?></div>
                        <div style="font-size: 1rem; color: #555;">Total Matières</div>
                    </div>
                    <table class="management-table">
                        <thead>
                            <tr>
                                <th>Nom de la Matière</th>
                                <th>Classe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['nom_matiere']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['nom_classe'] ?: 'Aucune'); ?></td>
                                    <td>
                                        <button class="btn-primary unified-btn edit-btn" 
                                            data-id="<?php echo $subject['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($subject['nom_matiere']); ?>" 
                                            data-type="subject">Modifier</button>
                                        <form action="../php/delete_subject.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                            <button type="submit" class="btn-danger unified-btn delete-btn">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;display:flex;gap:1rem;">
                    <a href="../php/add_subject.php" class="btn-primary">Ajouter une Matière</a>
                </div>
            </section>

        </main>
    </div>

    <!-- Modals: Add/Edit Student, Teacher, Class, Subject -->
    <!-- All modals use unified-modal and unified-form classes for consistent style -->
    <div id="addStudentModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Ajouter un Étudiant</h2>
            <form class="unified-form" action="traitement_etudiant.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <div class="form-group unified-form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="class">Classe</label>
                    <select id="class" name="id_classe">
                        <option value="">Aucune</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['nom_classe']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div id="addTeacherModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Ajouter un Enseignant</h2>
            <form class="unified-form" action="../php/traitement_enseignant.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <div class="form-group unified-form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="subjects">Matières</label>
                    <select id="subjects" name="subjects[]" multiple>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['nom_matiere']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div id="addClassModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Ajouter une Classe</h2>
            <form class="unified-form" action="../php/add_class.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <div class="form-group unified-form-group">
                    <label for="nom_classe">Nom de la Classe</label>
                    <input type="text" id="nom_classe" name="nom_classe" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="subjects">Matières à attribuer à la classe</label>
                    <select id="subjects" name="subjects[]" multiple>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['nom_matiere']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div id="addSubjectModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Ajouter une Matière</h2>
            <form class="unified-form" action="../php/add_subject.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <div class="form-group unified-form-group">
                    <label for="nom_matiere">Nom de la Matière</label>
                    <input type="text" id="nom_matiere" name="nom_matiere" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="id_classe">Classe</label>
                    <select id="id_classe" name="id_classe">
                        <option value="">Aucune</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['nom_classe']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Modifier Étudiant</h2>
            <form id="editStudentForm" class="unified-form" action="../php/edit_item.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <input type="hidden" name="id" id="editStudentId">
                <input type="hidden" name="type" value="student">
                <div class="form-group unified-form-group">
                    <label for="editStudentNom">Nom</label>
                    <input type="text" id="editStudentNom" name="nom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="editStudentPrenom">Prénom</label>
                    <input type="text" id="editStudentPrenom" name="prenom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="editStudentMatricule">Matricule</label>
                    <input type="text" id="editStudentMatricule" name="matricule" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="editStudentClass">Classe</label>
                    <select id="editStudentClass" name="classe">
                        <option value="N/A">Aucune</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class['nom_classe']); ?>">
                                <?php echo htmlspecialchars($class['nom_classe']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Teacher Modal -->
    <div id="editTeacherModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Modifier Enseignant</h2>
            <form id="editTeacherForm" class="unified-form" action="../php/edit_item.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <input type="hidden" name="id" id="editTeacherId">
                <input type="hidden" name="type" value="teacher">
                <div class="form-group unified-form-group">
                    <label for="editTeacherNom">Nom</label>
                    <input type="text" id="editTeacherNom" name="nom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="editTeacherPrenom">Prénom</label>
                    <input type="text" id="editTeacherPrenom" name="prenom" required>
                </div>
                <div class="form-group unified-form-group">
                    <label for="editTeacherMatricule">Matricule</label>
                    <input type="text" id="editTeacherMatricule" name="matricule" required>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div id="editClassModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Modifier Classe</h2>
            <form id="editClassForm" class="unified-form" action="../php/edit_item.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <input type="hidden" name="id" id="editClassId">
                <input type="hidden" name="type" value="class">
                <div class="form-group unified-form-group">
                    <label for="editClassName">Nom de la Classe</label>
                    <input type="text" id="editClassName" name="name" required>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div id="editSubjectModal" class="modal unified-modal">
        <div class="modal-content unified-modal-content">
            <span class="close-button">×</span>
            <h2>Modifier Matière</h2>
            <form id="editSubjectForm" class="unified-form" action="../php/edit_item.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                <input type="hidden" name="id" id="editSubjectId">
                <input type="hidden" name="type" value="subject">
                <div class="form-group unified-form-group">
                    <label for="editSubjectNom">Nom de la Matière</label>
                    <input type="text" id="editSubjectNom" name="name" required>
                </div>
                <div class="form-actions unified-form-actions">
                    <button type="button" class="btn-secondary unified-btn close-modal">Annuler</button>
                    <button type="submit" class="btn-primary unified-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data for Chart.js
        const classLabels = <?php echo json_encode(array_column($classNotes, 'nom_classe')); ?>;
        const classAverages = <?php echo json_encode(array_map(function($row) {
            return $row['note_count'] > 0 ? round((float)$row['avg_note'], 2) : null;
        }, $classNotes)); ?>;

        // Sidebar navigation logic for showing/hiding sections
        const navButtons = document.querySelectorAll('.sidebar-nav .nav-item');
        const contentSections = document.querySelectorAll('.content-section');
        navButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                navButtons.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                const id = this.id.replace('Nav', '-section');
                contentSections.forEach(section => {
                    section.style.display = (section.id === id) ? '' : 'none';
                });
            });
        });
        // Show dashboard by default
        contentSections.forEach(section => {
            section.style.display = (section.id === 'dashboard-section') ? '' : 'none';
        });
    </script>
</body>
</html>