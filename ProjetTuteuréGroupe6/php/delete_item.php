<?php
session_start();
require_once 'database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

try {
    $pdo->beginTransaction();

    switch ($data['type']) {
        case 'student':
            // Delete student's class associations
            $stmt = $pdo->prepare("DELETE FROM etudiant_classe WHERE id_etudiant = ?");
            $stmt->execute([$data['id']]);

            // Get user ID for the student
            $stmt = $pdo->prepare("SELECT id_user FROM etudiants WHERE id = ?");
            $stmt->execute([$data['id']]);
            $userId = $stmt->fetchColumn();

            // Delete student record
            $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
            $stmt->execute([$data['id']]);

            // Delete user record
            if ($userId) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
            }
            break;

        case 'teacher':
            // Delete teacher's subject associations
            $stmt = $pdo->prepare("DELETE FROM enseignant_matiere WHERE id_enseignant = ?");
            $stmt->execute([$data['id']]);

            // Get user ID for the teacher
            $stmt = $pdo->prepare("SELECT id_user FROM enseignants WHERE id = ?");
            $stmt->execute([$data['id']]);
            $userId = $stmt->fetchColumn();

            // Delete teacher record
            $stmt = $pdo->prepare("DELETE FROM enseignants WHERE id = ?");
            $stmt->execute([$data['id']]);

            // Delete user record
            if ($userId) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
            }
            break;

        case 'class':
            // Delete class associations with students
            $stmt = $pdo->prepare("DELETE FROM etudiant_classe WHERE id_classe = ?");
            $stmt->execute([$data['id']]);

            // Delete class record
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$data['id']]);
            break;

        case 'subject':
            // Delete subject associations with teachers
            $stmt = $pdo->prepare("DELETE FROM enseignant_matiere WHERE id_matiere = ?");
            $stmt->execute([$data['id']]);

            // Delete subject record
            $stmt = $pdo->prepare("DELETE FROM matieres WHERE id = ?");
            $stmt->execute([$data['id']]);
            break;

        default:
            throw new Exception('Type non valide');
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 