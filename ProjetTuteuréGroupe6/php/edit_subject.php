<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $nom_matiere = trim($_POST['nom_matiere']);
    $id_classe = isset($_POST['id_classe']) ? (int)$_POST['id_classe'] : null;

    if ($subject_id <= 0 || empty($nom_matiere)) {
        $_SESSION['error'] = 'Données invalides pour la mise à jour de la matière.';
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }

    try {
        $stmt = $pdo->prepare('UPDATE matieres SET nom_matiere = :nom_matiere, id_classe = :id_classe WHERE id = :subject_id');
        $stmt->bindParam(':nom_matiere', $nom_matiere);
        $stmt->bindParam(':id_classe', $id_classe, PDO::PARAM_INT);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();

        $_SESSION['success'] = 'Matière mise à jour avec succès.';

        // Update student-subject registrations for the new class
        if ($id_classe !== null) {
            // Get all students in the new class
            $stmt_students = $pdo->prepare("SELECT id FROM etudiants e JOIN etudiant_classe ec ON e.id = ec.id_etudiant WHERE ec.id_classe = :id_classe");
            $stmt_students->bindParam(':id_classe', $id_classe);
            $stmt_students->execute();
            $students = $stmt_students->fetchAll(PDO::FETCH_COLUMN);

            // Insert student-subject links, ignoring duplicates
            $stmt_insert = $pdo->prepare("INSERT IGNORE INTO etudiant_matiere (id_etudiant, id_matiere) VALUES (:id_etudiant, :id_matiere)");
            foreach ($students as $student_id) {
                $stmt_insert->bindParam(':id_etudiant', $student_id);
                $stmt_insert->bindParam(':id_matiere', $subject_id);
                $stmt_insert->execute();
            }
        }

        header('Location: ../director/dashboard.php?section=subjects');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la mise à jour de la matière : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=subjects');
    exit();
}
?>
