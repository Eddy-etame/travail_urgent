<?php
// php/add_note.php
session_start();
require_once '../php/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $grade = isset($_POST['grade']) ? floatval($_POST['grade']) : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $csrf_token = $_POST['csrf_token'];

    // Verify CSRF token (simplified; use a more robust method in production)
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Requête invalide']);
        exit();
    }

    if ($student_id <= 0 || $subject_id <= 0 || $grade === null || $grade < 0 || $grade > 20) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit();
    }

    try {
        // Get teacher ID
        $stmt = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $teacher_id = $stmt->fetchColumn();

        if (!$teacher_id) {
            echo json_encode(['success' => false, 'message' => 'Enseignant non trouvé']);
            exit();
        }

        $pdo->beginTransaction();

        // Check if note exists
        $stmt = $pdo->prepare("SELECT id FROM notes WHERE id_etudiant = :student_id AND id_matiere = :subject_id AND id_enseignant = :teacher_id");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        $existing_note = $stmt->fetchColumn();

        if ($existing_note) {
            // Update existing note
            $stmt = $pdo->prepare("UPDATE notes SET note = :note, commentaire = :commentaire, created_at = NOW() 
                                   WHERE id_etudiant = :student_id AND id_matiere = :subject_id AND id_enseignant = :teacher_id");
        } else {
            // Insert new note
            $stmt = $pdo->prepare("INSERT INTO notes (id_etudiant, id_matiere, id_enseignant, note, commentaire, created_at) 
                                   VALUES (:student_id, :subject_id, :teacher_id, :note, :commentaire, NOW())");
        }

        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':note', $grade);
        $stmt->bindParam(':commentaire', $comment);
        $stmt->execute();

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

// Fetch students and subjects for the dropdowns
$students = $pdo->query("SELECT e.id, e.nom, e.prenom FROM etudiants e")->fetchAll();
$subjects = $pdo->query("SELECT id, nom_matiere FROM matieres")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Note</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/teacher-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f6fa;
            margin: 0;
        }
        .main-content {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo_keyce.JPEG" alt="Logo Keyce" class="logo" />
                <h2>Espace Enseignant</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i> Tableau de bord</a>
                <a href="add_note.php" class="nav-item active"><i class="fas fa-graduation-cap"></i> Gestion des notes</a>
                <a href="notifications.php" class="nav-item"><i class="fas fa-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> Profil</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="header">
                <h1>Ajouter une Note</h1>
            </div>
            <div class="info-card">
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
                <form class="add-note-form" id="addNoteForm" method="POST" action="../php/save_notes.php">
                    <div class="form-group">
                        <label for="student">Étudiant</label>
                        <select name="student" id="student" required>
                            <option value="">Sélectionner un étudiant</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['prenom'] . ' ' . $student['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Matière</label>
                        <select name="subject" id="subject" required>
                            <option value="">Sélectionner une matière</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['nom_matiere']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        <input type="number" name="note" id="note" min="0" max="20" step="0.01" required />
                    </div>
                    <div class="form-group">
                        <label for="comment">Commentaire</label>
                        <textarea name="comment" id="comment" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../js/teacher.js"></script>
</body>
</html>