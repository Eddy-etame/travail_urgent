<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_classe = trim($_POST['nom_classe']);

    if (empty($nom_classe)) {
        $_SESSION['error'] = "Le nom de la classe ne peut pas être vide.";
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }

    try {
        $pdo->beginTransaction();
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE nom_classe = :nom_classe");
        $stmt_check->bindParam(':nom_classe', $nom_classe);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Cette classe existe déjà.";
            header('Location: ../director/dashboard.php?section=classes');
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO classes (nom_classe) VALUES (:nom_classe)");
        $stmt->bindParam(':nom_classe', $nom_classe);
        $stmt->execute();
        $class_id = $pdo->lastInsertId();

        // Assign selected subjects to this class
        if (!empty($_POST['subjects']) && is_array($_POST['subjects'])) {
            $stmt_update = $pdo->prepare("UPDATE matieres SET id_classe = :id_classe WHERE id = :id_matiere");
            foreach ($_POST['subjects'] as $subject_id) {
                $stmt_update->bindParam(':id_classe', $class_id, PDO::PARAM_INT);
                $stmt_update->bindParam(':id_matiere', $subject_id, PDO::PARAM_INT);
                $stmt_update->execute();
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Classe ajoutée avec succès !";
        header('Location: ../director/dashboard.php?section=classes');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'ajout de la classe : " . $e->getMessage();
        header('Location: ../director/dashboard.php?section=classes');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php');
    exit();
}
?>