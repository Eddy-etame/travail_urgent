<?php
session_start();
require_once 'database.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$availability_date = $_GET['availability_date'] ?? '';

if (empty($availability_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing availability_date parameter']);
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT start_time, duration_minutes FROM teacher_availability WHERE teacher_id = :teacher_id AND availability_date = :availability_date ORDER BY start_time');
    $stmt->execute([
        ':teacher_id' => $teacher_id,
        ':availability_date' => $availability_date
    ]);
    $availability = $stmt->fetchAll();

    echo json_encode(['availability' => $availability]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
