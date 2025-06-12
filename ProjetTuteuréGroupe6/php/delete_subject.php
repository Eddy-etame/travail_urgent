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

    if ($subject_id <= 0) {
        $_SESSION['error'] = 'ID matière invalide pour la suppression.';
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM matieres WHERE id = :subject_id');
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();

        // Delete related student-subject and teacher-subject links
        $stmt_delete_student_subject = $pdo->prepare('DELETE FROM etudiant_matiere WHERE id_matiere = :subject_id');
        $stmt_delete_student_subject->bindParam(':subject_id', $subject_id);
        $stmt_delete_student_subject->execute();

        $stmt_delete_teacher_subject = $pdo->prepare('DELETE FROM enseignant_matiere WHERE id_matiere = :subject_id');
        $stmt_delete_teacher_subject->bindParam(':subject_id', $subject_id);
        $stmt_delete_teacher_subject->execute();

        $_SESSION['success'] = 'Matière supprimée avec succès.';
        header('Location: ../director/dashboard.php?section=subjects');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression de la matière : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=subjects');
    exit();
}
?>
