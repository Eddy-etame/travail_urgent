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
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $matricule = trim($_POST['matricule']);

    if ($student_id <= 0 || empty($nom) || empty($prenom) || empty($matricule)) {
        $_SESSION['error'] = 'Données invalides pour la mise à jour de l\'étudiant.';
        header('Location: ../director/dashboard.php?section=students');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Update users table matricule
        $stmt_user = $pdo->prepare('UPDATE users u JOIN etudiants e ON u.id = e.id_user SET u.matricule = :matricule WHERE e.id = :student_id');
        $stmt_user->bindParam(':matricule', $matricule);
        $stmt_user->bindParam(':student_id', $student_id);
        $stmt_user->execute();

        // Update etudiants table
        $stmt_student = $pdo->prepare('UPDATE etudiants SET nom = :nom, prenom = :prenom WHERE id = :student_id');
        $stmt_student->bindParam(':nom', $nom);
        $stmt_student->bindParam(':prenom', $prenom);
        $stmt_student->bindParam(':student_id', $student_id);
        $stmt_student->execute();

        $pdo->commit();
        $_SESSION['success'] = 'Étudiant mis à jour avec succès.';
        header('Location: ../director/dashboard.php?section=students');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Erreur lors de la mise à jour de l\'étudiant : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=students');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=students');
    exit();
}
?>
