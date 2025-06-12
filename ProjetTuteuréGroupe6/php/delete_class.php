<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;

    if ($class_id <= 0) {
        $_SESSION['error'] = 'ID classe invalide pour la suppression.';
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM classes WHERE id = :class_id');
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();

        $_SESSION['success'] = 'Classe supprimée avec succès.';
        header('Location: ../director/dashboard.php?section=classes');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression de la classe : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=classes');
    exit();
}
?>
