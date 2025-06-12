<?php
session_start();
require_once 'database.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $matricule = trim($_POST["matricule"]); // Assuming matricule is entered
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Input validation
    if (empty($nom) || empty($prenom) || empty($matricule) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: ../inscription_enseignant.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header("Location: ../inscription_enseignant.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header("Location: ../inscription_enseignant.php");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Check if matricule already exists in users table
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE matricule = :matricule");
        $stmt_check->bindParam(':matricule', $matricule);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['error'] = "Ce matricule est déjà utilisé.";
            header("Location: ../inscription_enseignant.php");
            exit();
        }

        // Get teacher role ID
        $stmt_role = $pdo->prepare("SELECT id FROM roles WHERE nom_role = 'teacher'");
        $stmt_role->execute();
        $role_id = $stmt_role->fetchColumn();

        if (!$role_id) {
            throw new Exception("Rôle 'teacher' non trouvé dans la base de données.");
        }

        // 1. Insert into users table
        $stmt_user = $pdo->prepare("INSERT INTO users (matricule, password_hash, id_role) VALUES (:matricule, :password_hash, :id_role)");
        $stmt_user->bindParam(':matricule', $matricule);
        $stmt_user->bindParam(':password_hash', $password_hash);
        $stmt_user->bindParam(':id_role', $role_id);
        $stmt_user->execute();
        $user_id = $pdo->lastInsertId();

        // 2. Insert into enseignants table
        $stmt_teacher = $pdo->prepare("INSERT INTO enseignants (id_user, nom, prenom) VALUES (:id_user, :nom, :prenom)");
        $stmt_teacher->bindParam(':id_user', $user_id);
        $stmt_teacher->bindParam(':nom', $nom);
        $stmt_teacher->bindParam(':prenom', $prenom);
        $stmt_teacher->execute();

        $pdo->commit();
        $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        header("Location: ../index.php?role=teacher");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'inscription : " . $e->getMessage();
        header("Location: ../inscription_enseignant.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: ../inscription_enseignant.php");
        exit();
    }
} else {
    header("Location: ../inscription_enseignant.php");
    exit();
}
?>