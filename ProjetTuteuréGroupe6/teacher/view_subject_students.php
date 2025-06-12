<?php
session_start();
require_once '../php/database.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo '<div class="message message-error">Accès non autorisé.</div>';
    exit();
}

$teacher_user_id = $_SESSION['user_id'];
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($subject_id <= 0) {
    echo '<div class="message message-error">ID de matière invalide.</div>';
    exit();
}

try {
    // Get teacher ID from enseignants table
    $stmt_teacher = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :user_id");
    $stmt_teacher->execute([':user_id' => $teacher_user_id]);
    $teacher = $stmt_teacher->fetch();

    if (!$teacher) {
        echo '<div class="message message-error">Enseignant introuvable.</div>';
        exit();
    }
    $teacher_id = $teacher['id'];

    // Verify that the teacher is assigned to the subject
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM enseignant_matiere WHERE id_enseignant = :teacher_id AND id_matiere = :subject_id");
    $stmt_check->execute([':teacher_id' => $teacher_id, ':subject_id' => $subject_id]);
    if ($stmt_check->fetchColumn() == 0) {
        echo '<div class="message message-error">Accès refusé à cette matière.</div>';
        exit();
    }

    // Get students registered in the subject via etudiant_matiere
    $stmt_students = $pdo->prepare("
        SELECT e.id, e.nom, e.prenom, u.matricule
        FROM etudiants e
        JOIN users u ON e.id_user = u.id
        JOIN etudiant_matiere em ON e.id = em.id_etudiant
        WHERE em.id_matiere = :subject_id
        ORDER BY e.nom, e.prenom
    ");
    $stmt_students->execute([':subject_id' => $subject_id]);
    $students = $stmt_students->fetchAll();

} catch (PDOException $e) {
    echo '<div class="message message-error">Erreur base de données : ' . htmlspecialchars($e->getMessage()) . '</div>';
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étudiants de la Matière</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="header">
                <h1>Étudiants de la Matière</h1>
            </div>
            <section class="info-cards">
                <div class="info-card">
                    <?php if (isset($students) && count($students) > 0): ?>
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Matricule</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($student['matricule']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="message">Aucun étudiant inscrit pour cette matière.</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
