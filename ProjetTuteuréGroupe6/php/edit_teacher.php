<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

// Fetch all teachers with their subjects and classes
$teachers = $pdo->query("SELECT e.id, e.nom, e.prenom, u.matricule FROM enseignants e JOIN users u ON e.id_user = u.id")->fetchAll();
$subjects = $pdo->query("SELECT m.id, m.nom_matiere, c.nom_classe, em.id_enseignant FROM matieres m LEFT JOIN classes c ON m.id_classe = c.id LEFT JOIN enseignant_matiere em ON m.id = em.id_matiere")->fetchAll();

// Group subjects by teacher
$teacher_subjects = [];
foreach ($subjects as $subject) {
    if ($subject['id_enseignant']) {
        $teacher_subjects[$subject['id_enseignant']][] = $subject;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Enseignants</title>
    <link rel="stylesheet" href="../css/director.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Gestion des Enseignants</h1>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message message-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message message-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <table class="management-table" style="width:100%;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.07);border-radius:8px;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Matricule</th>
                    <th>Matières (Classe)</th>
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
                            if (!empty($teacher_subjects[$teacher['id']])) {
                                $subs = array_map(function($s) {
                                    return htmlspecialchars($s['nom_matiere']) .
                                        ($s['nom_classe'] ? ' <span style=\'color:#888\'>(Classe: '.htmlspecialchars($s['nom_classe']).')</span>' : '');
                                }, $teacher_subjects[$teacher['id']]);
                                echo implode('<br>', $subs);
                            } else {
                                echo '<span style="color:#888;">Aucune matière</span>';
                            }
                            ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <form action="edit_item.php" method="post" style="display:inline;">
                                <input type="hidden" name="type" value="teacher">
                                <input type="hidden" name="id" value="<?php echo $teacher['id']; ?>">
                                <button type="submit" class="btn-primary">Modifier</button>
                            </form>
                            <form action="delete_teacher.php" method="post" style="display:inline;" onsubmit="return confirm('Supprimer cet enseignant ?');">
                                <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                <button type="submit" class="btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../director/dashboard.php?section=teachers" class="btn-secondary" style="margin-top:2rem;display:inline-block;">Retour au tableau de bord</a>
    </div>
</body>
</html>
