<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_matiere = trim($_POST['nom_matiere']);
    $id_classe = isset($_POST['id_classe']) ? (int)$_POST['id_classe'] : null;

    if (empty($nom_matiere)) {
        $_SESSION['error'] = "Le nom de la matière ne peut pas être vide.";
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }

    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE nom_matiere = :nom_matiere");
        $stmt_check->bindParam(':nom_matiere', $nom_matiere);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Cette matière existe déjà.";
            header('Location: ../director/dashboard.php?section=subjects');
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO matieres (nom_matiere, id_classe) VALUES (:nom_matiere, :id_classe)");
        $stmt->bindParam(':nom_matiere', $nom_matiere);
        $stmt->bindParam(':id_classe', $id_classe, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "Matière ajoutée avec succès !";
        header('Location: ../director/dashboard.php?section=subjects');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout de la matière : " . $e->getMessage();
        header('Location: ../director/dashboard.php?section=subjects');
        exit();
    }
} else {
    header('Location: ../director/dashboard.php');
    exit();
}
?>