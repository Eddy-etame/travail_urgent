<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

    if ($student_id <= 0) {
        $_SESSION['error'] = 'ID étudiant invalide pour la suppression.';
        header('Location: ../director/dashboard.php?section=students');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Delete from etudiant_classe
        $stmt_ec = $pdo->prepare('DELETE FROM etudiant_classe WHERE id_etudiant = :student_id');
        $stmt_ec->bindParam(':student_id', $student_id);
        $stmt_ec->execute();

        // Get user_id for the student
        $stmt_user_id = $pdo->prepare('SELECT id_user FROM etudiants WHERE id = :student_id');
        $stmt_user_id->bindParam(':student_id', $student_id);
        $stmt_user_id->execute();
        $user_id = $stmt_user_id->fetchColumn();

        // Delete from etudiants
        $stmt_student = $pdo->prepare('DELETE FROM etudiants WHERE id = :student_id');
        $stmt_student->bindParam(':student_id', $student_id);
        $stmt_student->execute();

        // Delete from users
        if ($user_id) {
            $stmt_user = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
            $stmt_user->bindParam(':user_id', $user_id);
            $stmt_user->execute();
        }

        $pdo->commit();
        $_SESSION['success'] = 'Étudiant supprimé avec succès.';
        header('Location: ../director/dashboard.php?section=students');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Erreur lors de la suppression de l\'étudiant : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=students');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=students');
    exit();
}
?>
