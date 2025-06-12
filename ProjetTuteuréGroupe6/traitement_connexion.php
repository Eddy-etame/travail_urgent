<?php
session_start();
require_once 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST["matricule"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    if (empty($matricule) || empty($password) || !in_array($role, ['admin', 'teacher', 'student'])) {
        $_SESSION["error"] = "Veuillez remplir tous les champs correctement.";
        header("Location: index.php?role=" . urlencode($role));
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT u.id, u.matricule, u.password, r.nom_role 
                               FROM users u 
                               JOIN roles r ON u.id_role = r.id 
                               WHERE u.matricule = :matricule AND r.nom_role = :role");
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION["user_role"] = $role;
            $_SESSION["user_matricule"] = $matricule;
            $_SESSION["user_id"] = $user['id'];

            switch ($role) {
                case "admin":
                    header("Location: director/dashboard.php");
                    break;
                case "teacher":
                    header("Location: teacher/dashboard.php");
                    break;
                case "student":
                    header("Location: student/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION["error"] = "Matricule ou mot de passe incorrect.";
            header("Location: index.php?role=" . urlencode($role));
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION["error"] = "Erreur de connexion : " . $e->getMessage();
        header("Location: index.php?role=" . urlencode($role));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>