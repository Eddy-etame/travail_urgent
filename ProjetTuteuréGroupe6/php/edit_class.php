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
    $nom_classe = trim($_POST['nom_classe']);

    if ($class_id <= 0 || empty($nom_classe)) {
        $_SESSION['error'] = 'Données invalides pour la mise à jour de la classe.';
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }

    try {
        $stmt = $pdo->prepare('UPDATE classes SET nom_classe = :nom_classe WHERE id = :class_id');
        $stmt->bindParam(':nom_classe', $nom_classe);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->execute();

        $_SESSION['success'] = 'Classe mise à jour avec succès.';
        header('Location: ../director/dashboard.php?section=classes');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur lors de la mise à jour de la classe : ' . $e->getMessage();
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php?section=classes');
    exit();
}
?>
