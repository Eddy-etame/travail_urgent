<?php
session_start();
require_once 'database.php';

// Vérification de la session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../index.php?role=teacher');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id_user = $_SESSION['user_id']; // user_id from users table
    $id_classe = (int)$_POST['id_classe'];
    $id_matiere = (int)$_POST['id_matiere'];
    $notes_data = $_POST['notes']; // Array of notes for students

    if (empty($id_classe) || empty($id_matiere) || empty($notes_data)) {
        $_SESSION['error'] = "Veuillez sélectionner une classe, une matière et saisir au moins une note.";
        header('Location: ../teacher/add-note.php');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Get the actual teacher_id from the enseignants table
        $stmt_teacher_id = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :user_id");
        $stmt_teacher_id->bindParam(':user_id', $teacher_id_user);
        $stmt_teacher_id->execute();
        $teacher_id = $stmt_teacher_id->fetchColumn();

        if (!$teacher_id) {
            throw new Exception("Informations de l'enseignant introuvables.");
        }

        foreach ($notes_data as $student_id => $data) {
            $student_id = (int)$data['student_id'];
            $note = isset($data['note']) && $data['note'] !== '' ? (float)$data['note'] : null;
            $commentaire = trim($data['comment']);

            // Validate note value
            if ($note !== null && ($note < 0 || $note > 20)) {
                throw new Exception("La note de l'étudiant " . htmlspecialchars($student_id) . " doit être entre 0 et 20.");
            }

            // Check if a note already exists for this student, subject, and teacher
            $stmt_check_note = $pdo->prepare("SELECT id FROM notes WHERE id_etudiant = :student_id AND id_matiere = :matiere_id AND id_enseignant = :teacher_id");
            $stmt_check_note->bindParam(':student_id', $student_id);
            $stmt_check_note->bindParam(':matiere_id', $id_matiere);
            $stmt_check_note->bindParam(':teacher_id', $teacher_id);
            $stmt_check_note->execute();
            $existing_note_id = $stmt_check_note->fetchColumn();

            if ($existing_note_id) {
                // Update existing note
                $stmt_update = $pdo->prepare("UPDATE notes SET note = :note, commentaire = :commentaire WHERE id = :note_id");
                $stmt_update->bindParam(':note', $note);
                $stmt_update->bindParam(':commentaire', $commentaire);
                $stmt_update->bindParam(':note_id', $existing_note_id);
                $stmt_update->execute();
            } else {
                // Insert new note
                if ($note !== null) { // Only insert if a note value is provided
                    $stmt_insert = $pdo->prepare("INSERT INTO notes (id_etudiant, id_matiere, id_enseignant, note, commentaire) VALUES (:student_id, :matiere_id, :teacher_id, :note, :commentaire)");
                    $stmt_insert->bindParam(':student_id', $student_id);
                    $stmt_insert->bindParam(':matiere_id', $id_matiere);
                    $stmt_insert->bindParam(':teacher_id', $teacher_id);
                    $stmt_insert->bindParam(':note', $note);
                    $stmt_insert->bindParam(':commentaire', $commentaire);
                    $stmt_insert->execute();
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Notes enregistrées avec succès !";
        header('Location: ../teacher/add-note.php'); // Redirect back to add-note page
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'enregistrement des notes : " . $e->getMessage();
        header('Location: ../teacher/add-note.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header('Location: ../teacher/add-note.php');
        exit();
    }
} else {
    header('Location: ../teacher/dashboard.php');
    exit();
}
?>