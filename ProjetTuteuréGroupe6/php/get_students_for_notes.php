<?php
session_start();
require_once 'database.php';

// Ensure this is an AJAX request and teacher is logged in
if (!isset($_GET['class_id']) || !isset($_GET['subject_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé.']);
    exit();
}

$class_id = (int)$_GET['class_id'];
$subject_id = (int)$_GET['subject_id'];
$teacher_id_session = $_SESSION['user_id']; // This is user_id from users table

try {
    // Get the actual teacher_id from the enseignants table
    $stmt_teacher_id = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :user_id");
    $stmt_teacher_id->bindParam(':user_id', $teacher_id_session);
    $stmt_teacher_id->execute();
    $teacher_id = $stmt_teacher_id->fetchColumn();

    if (!$teacher_id) {
        echo json_encode(['error' => 'Informations de l\'enseignant introuvables.']);
        exit();
    }

    // Fetch students in the selected class, and their existing notes for the given subject and teacher
    $stmt = $pdo->prepare("
        SELECT
            e.id AS id_etudiant,
            e.nom,
            e.prenom,
            u.matricule,
            n.note,
            n.commentaire
        FROM
            etudiants e
        JOIN
            users u ON e.id_user = u.id
        JOIN
            etudiant_classe ec ON e.id = ec.id_etudiant
        LEFT JOIN
            notes n ON e.id = n.id_etudiant AND n.id_matiere = :subject_id AND n.id_enseignant = :teacher_id
        WHERE
            ec.id_classe = :class_id
        ORDER BY
            e.nom, e.prenom
    ");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->bindParam(':teacher_id', $teacher_id); // Use the actual teacher_id
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>