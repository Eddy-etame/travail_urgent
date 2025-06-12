<?php
session_start();
require_once 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST['matricule']);
    $role = trim($_POST['role']);

    if (empty($matricule) || !in_array($role, ['teacher', 'student'])) {
        $_SESSION['error'] = "Veuillez fournir un matricule valide.";
        header("Location: ../mot_de_passe_oublie.php?role=" . urlencode($role));
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE matricule = :matricule AND id_role = (SELECT id FROM roles WHERE nom_role = :role)");
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            // Generate reset token (simplified; implement secure token generation and email sending in production)
            $_SESSION['success'] = "Un lien de réinitialisation a été envoyé à votre adresse email.";
        } else {
            $_SESSION['error'] = "Matricule non trouvé.";
        }
        header("Location: ../mot_de_passe_oublie.php?role=" . urlencode($role));
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: ../mot_de_passe_oublie.php?role=" . urlencode($role));
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>