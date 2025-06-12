<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = isset($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : 0;

    if ($teacher_id <= 0) {
        $_SESSION['error'] = 'ID enseignant invalide pour la suppression.';
        header('Location: ../director/dashboard.php?section=teachers');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Get user_id for the teacher
        $stmt_user_id = $pdo->prepare('SELECT id_user FROM enseignants WHERE id = :teacher_id');
        $stmt_user_id->bindParam(':teacher_id', $teacher_id);
        $stmt_user_id->execute();
        $user_id = $stmt_user_id->fetchColumn();

        // Delete from enseignants
        $stmt_teacher = $pdo->prepare('DELETE FROM enseignants WHERE id = :teacher_id');
        $stmt_teacher->bindParam(':teacher_id', $teacher_id);
        $stmt_teacher->execute();

        // Delete from users
        if ($user_id) {
            $stmt_user = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
            $stmt_user->bindParam(':user_id', $user_id);
            $stmt_user->execute();
        }

        $pdo->commit();
        $_SESSION['success'] = 'Enseignant supprimé avec succès.';
        header('Location: ../director/dashboard.php?section=teachers');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Erreur lors de la suppression de l\'enseignant : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=teachers');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=teachers');
    exit();
}
?>
