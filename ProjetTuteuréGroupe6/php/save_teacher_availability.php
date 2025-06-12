<?php
session_start();
require_once 'database.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['user_id'];
    $availability_date = $_POST['availability_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $duration_minutes = 240; // fixed 4 hours

    if (empty($availability_date) || empty($start_time)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    try {
        // Check if availability already exists for this teacher, date, and time
        $stmt_check = $pdo->prepare('SELECT id FROM teacher_availability WHERE teacher_id = :teacher_id AND availability_date = :availability_date AND start_time = :start_time');
        $stmt_check->execute([
            ':teacher_id' => $teacher_id,
            ':availability_date' => $availability_date,
            ':start_time' => $start_time
        ]);
        $existing = $stmt_check->fetch();

        if ($existing) {
            // Update existing record (if needed)
            $stmt_update = $pdo->prepare('UPDATE teacher_availability SET duration_minutes = :duration WHERE id = :id');
            $stmt_update->execute([
                ':duration' => $duration_minutes,
                ':id' => $existing['id']
            ]);
        } else {
            // Insert new availability
            $stmt_insert = $pdo->prepare('INSERT INTO teacher_availability (teacher_id, availability_date, start_time, duration_minutes) VALUES (:teacher_id, :availability_date, :start_time, :duration)');
            $stmt_insert->execute([
                ':teacher_id' => $teacher_id,
                ':availability_date' => $availability_date,
                ':start_time' => $start_time,
                ':duration' => $duration_minutes
            ]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
