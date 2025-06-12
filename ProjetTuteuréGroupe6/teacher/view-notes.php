<?php
session_start();
require_once '../php/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../index.php?role=teacher');
    exit();
}

$user_id = $_SESSION['user_id'];
$teacher_id = null;
$notes = [];

try {
    $stmt_teacher_id = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :user_id");
    $stmt_teacher_id->bindParam(':user_id', $user_id);
    $stmt_teacher_id->execute();
    $teacher_id = $stmt_teacher_id->fetchColumn();

    if ($teacher_id) {
        $stmt_notes = $pdo->prepare("SELECT n.id, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, m.nom_matiere, n.note, n.commentaire, n.date_creation FROM notes n JOIN etudiants e ON n.id_etudiant = e.id JOIN matieres m ON n.id_matiere = m.id WHERE m.id IN (SELECT id_matiere FROM enseignant_matiere WHERE id_enseignant = :teacher_id) ORDER BY n.date_creation DESC");
        $stmt_notes->bindParam(':teacher_id', $teacher_id);
        $stmt_notes->execute();
        $notes = $stmt_notes->fetchAll();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des notes : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir les Notes</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/teacher-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="add-note.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Gestion des notes</a>
                <a href="view-notes.php" class="nav-item active"><i class="fas fa-eye"></i> Voir les Notes</a>
                <a href="notifications.php" class="nav-item"><i class="fas fa-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> Profil</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </aside>
        <main class="main-content">
            <div class="header">
                <h1>Mes Notes</h1>
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
                <div class="students-notes-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Matière</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notes)): ?>
                                <tr><td colspan="5">Aucune note trouvée.</td></tr>
                            <?php else: ?>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($note['etudiant_prenom'] . ' ' . $note['etudiant_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($note['nom_matiere']); ?></td>
                                        <td><?php echo htmlspecialchars($note['note']); ?></td>
                                        <td><?php echo htmlspecialchars($note['commentaire']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($note['date_creation'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/teacher.js"></script>
</body>
</html>