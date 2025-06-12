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

    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['import_error'] = "Erreur lors du téléchargement du fichier.";
        header('Location: ../teacher/add-note.php');
        exit();
    }

    $file_tmp_path = $_FILES['import_file']['tmp_name'];
    $file_name = $_FILES['import_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if ($file_ext !== 'csv') {
        $_SESSION['import_error'] = "Le fichier doit être au format CSV.";
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

        // Open the CSV file for reading
        if (($handle = fopen($file_tmp_path, "r")) === false) {
            throw new Exception("Impossible d'ouvrir le fichier CSV.");
        }

        $row_number = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $row_number++;

            // Skip header row if present (optional: check if first row contains headers)
            if ($row_number == 1 && preg_match('/id_etudiant|id_matiere|id_enseignant/i', implode(',', $data))) {
                continue;
            }

            // Expecting CSV columns in order: id_etudiant, id_matiere, id_enseignant, note, commentaire
            if (count($data) < 5) {
                throw new Exception("Format CSV invalide à la ligne $row_number. Attendu 5 colonnes.");
            }

            $student_id = (int)trim($data[0]);
            $matiere_id = (int)trim($data[1]);
            $enseignant_id = (int)trim($data[2]);
            $note = trim($data[3]) !== '' ? (float)trim($data[3]) : null;
            $commentaire = trim($data[4]);

            // Validate teacher ID matches logged in teacher
            if ($enseignant_id !== (int)$teacher_id) {
                throw new Exception("Ligne $row_number: L'ID enseignant ne correspond pas à l'utilisateur connecté.");
            }

            // Validate note value
            if ($note !== null && ($note < 0 || $note > 20)) {
                throw new Exception("Ligne $row_number: La note doit être entre 0 et 20.");
            }

            // Check if a note already exists for this student, subject, and teacher
            $stmt_check_note = $pdo->prepare("SELECT id FROM notes WHERE id_etudiant = :student_id AND id_matiere = :matiere_id AND id_enseignant = :teacher_id");
            $stmt_check_note->bindParam(':student_id', $student_id);
            $stmt_check_note->bindParam(':matiere_id', $matiere_id);
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
                    $stmt_insert->bindParam(':matiere_id', $matiere_id);
                    $stmt_insert->bindParam(':teacher_id', $teacher_id);
                    $stmt_insert->bindParam(':note', $note);
                    $stmt_insert->bindParam(':commentaire', $commentaire);
                    $stmt_insert->execute();
                }
            }
        }

        fclose($handle);
        $pdo->commit();
        $_SESSION['import_success'] = "Importation des notes réussie avec succès !";
        header('Location: ../teacher/add-note.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['import_error'] = "Erreur lors de l'importation des notes : " . $e->getMessage();
        header('Location: ../teacher/add-note.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['import_error'] = "Erreur : " . $e->getMessage();
        header('Location: ../teacher/add-note.php');
        exit();
    }
} else {
    header('Location: ../teacher/dashboard.php');
    exit();
}
?>
