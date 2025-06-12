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
            // Update student information
            $stmt = $pdo->prepare("
                UPDATE etudiants e 
                JOIN users u ON e.id_user = u.id 
                SET e.nom = ?, e.prenom = ?, u.matricule = ? 
                WHERE e.id = ?
            ");
            $stmt->execute([$data['nom'], $data['prenom'], $data['matricule'], $data['id']]);

            // Update student's class if provided
            if (isset($data['classe']) && $data['classe'] !== 'N/A') {
                // Get class ID
                $stmt = $pdo->prepare("SELECT id FROM classes WHERE nom_classe = ?");
                $stmt->execute([$data['classe']]);
                $classId = $stmt->fetchColumn();

                if ($classId) {
                    // Update or insert class association
                    $stmt = $pdo->prepare("
                        INSERT INTO etudiant_classe (id_etudiant, id_classe) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE id_classe = ?
                    ");
                    $stmt->execute([$data['id'], $classId, $classId]);
                }
            }
            break;

        case 'teacher':
            // Update teacher information
            $stmt = $pdo->prepare("
                UPDATE enseignants e 
                JOIN users u ON e.id_user = u.id 
                SET e.nom = ?, e.prenom = ?, u.matricule = ? 
                WHERE e.id = ?
            ");
            $stmt->execute([$data['nom'], $data['prenom'], $data['matricule'], $data['id']]);
            break;

        case 'class':
            // Update class name
            $stmt = $pdo->prepare("UPDATE classes SET nom_classe = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['id']]);
            break;

        case 'subject':
            // Update subject name
            $stmt = $pdo->prepare("UPDATE matieres SET nom_matiere = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['id']]);
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