<?php
session_start();
require_once 'database.php'; // Include the database connection

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST["matricule"]);
    $password = $_POST["password"];
    $role_name = strtolower(trim($_POST["role"])); // Convert role to lowercase

    // Debug log
    error_log("Login attempt - Matricule: $matricule, Role: $role_name");

    // Input validation
    if (empty($matricule) || empty($password) || empty($role_name)) {
        $_SESSION["error"] = "Veuillez remplir tous les champs.";
        header("Location: ../index.php?role=" . htmlspecialchars($role_name));
        exit();
    }

    try {
        // Fetch user from 'users' table based on matricule and role (case-insensitive)
        $stmt = $pdo->prepare("SELECT u.id, u.matricule, u.password_hash, LOWER(r.nom_role) as nom_role 
                              FROM users u 
                              JOIN roles r ON u.id_role = r.id 
                              WHERE u.matricule = :matricule AND LOWER(r.nom_role) = :role_name");
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':role_name', $role_name);
        $stmt->execute();
        $user = $stmt->fetch();

        // Debug log
        error_log("User query result: " . print_r($user, true));

        if ($user && password_verify($password, $user['password_hash'])) {
            // Authentication successful
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["user_role"] = $user['nom_role']; // This will be lowercase
            $_SESSION["user_matricule"] = $user['matricule'];

            // Debug log
            error_log("Authentication successful - Role: " . $user['nom_role']);

            // Set a success message
            $_SESSION["success"] = "Connexion réussie!";

            // Redirection vers le tableau de bord approprié
            switch ($user['nom_role']) {
                case "admin":
                    header("Location: ../director/dashboard.php");
                    break;
                case "teacher":
                    header("Location: ../teacher/dashboard.php");
                    break;
                case "student":
                    header("Location: ../student/dashboard.php");
                    break;
                default:
                    // Should not happen if roles are managed correctly
                    $_SESSION["error"] = "Type de rôle non reconnu: " . $user['nom_role'];
                    header("Location: ../index.php?role=" . htmlspecialchars($role_name));
            }
            exit();
        } else {
            // Invalid credentials
            $_SESSION["error"] = "Matricule ou mot de passe incorrect.";
            header("Location: ../index.php?role=" . htmlspecialchars($role_name));
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION["error"] = "Erreur de connexion : " . $e->getMessage();
        header("Location: ../index.php?role=" . htmlspecialchars($role_name));
        exit();
    }
} else {
    // If accessed directly without POST request
    header("Location: ../espace.php");
    exit();
}
?>