<?php
session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = trim($_POST['user_type']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $matricule = trim($_POST['matricule']);
    $password = $_POST['password'];
    $id_classe = isset($_POST['id_classe']) ? (int)$_POST['id_classe'] : null;
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    // Basic validation
    if (empty($user_type) || empty($nom) || empty($prenom) || empty($matricule) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header('Location: ../director/dashboard.php?section=students'); // Or teachers depending on error context
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header('Location: ../director/dashboard.php?section=students');
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Check if matricule already exists
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE matricule = :matricule");
        $stmt_check->bindParam(':matricule', $matricule);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("Ce matricule est déjà utilisé.");
        }

        // Get role ID
        $stmt_role = $pdo->prepare("SELECT id FROM roles WHERE nom_role = :user_type");
        $stmt_role->bindParam(':user_type', $user_type);
        $stmt_role->execute();
        $role_id = $stmt_role->fetchColumn();

        if (!$role_id) {
            throw new Exception("Type d'utilisateur invalide.");
        }

        // Insert into users table
        $stmt_user = $pdo->prepare("INSERT INTO users (matricule, password_hash, id_role) VALUES (:matricule, :password_hash, :id_role)");
        $stmt_user->bindParam(':matricule', $matricule);
        $stmt_user->bindParam(':password_hash', $password_hash);
        $stmt_user->bindParam(':id_role', $role_id);
        $stmt_user->execute();
        $user_id = $pdo->lastInsertId();

        // Insert into specific tables based on user type
        if ($user_type === 'student') {
            $stmt_etudiant = $pdo->prepare("INSERT INTO etudiants (id_user, nom, prenom) VALUES (:id_user, :nom, :prenom)");
            $stmt_etudiant->bindParam(':id_user', $user_id);
            $stmt_etudiant->bindParam(':nom', $nom);
            $stmt_etudiant->bindParam(':prenom', $prenom);
            $stmt_etudiant->execute();
            $etudiant_id = $pdo->lastInsertId();

            if ($id_classe) {
                $stmt_etudiant_classe = $pdo->prepare("INSERT INTO etudiant_classe (id_etudiant, id_classe) VALUES (:id_etudiant, :id_classe)");
                $stmt_etudiant_classe->bindParam(':id_etudiant', $etudiant_id);
                $stmt_etudiant_classe->bindParam(':id_classe', $id_classe);
                $stmt_etudiant_classe->execute();

                // Automatically register student to all subjects of the class
                $stmt_subjects = $pdo->prepare("SELECT id FROM matieres WHERE id_classe = :id_classe");
                $stmt_subjects->bindParam(':id_classe', $id_classe);
                $stmt_subjects->execute();
                $subjects = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);

                $stmt_insert = $pdo->prepare("INSERT IGNORE INTO etudiant_matiere (id_etudiant, id_matiere) VALUES (:id_etudiant, :id_matiere)");
                foreach ($subjects as $subject_id) {
                    $stmt_insert->bindParam(':id_etudiant', $etudiant_id);
                    $stmt_insert->bindParam(':id_matiere', $subject_id);
                    $stmt_insert->execute();
                }
            }

        } elseif ($user_type === 'teacher') {
            $stmt_enseignant = $pdo->prepare("INSERT INTO enseignants (id_user, nom, prenom) VALUES (:id_user, :nom, :prenom)");
            $stmt_enseignant->bindParam(':id_user', $user_id);
            $stmt_enseignant->bindParam(':nom', $nom);
            $stmt_enseignant->bindParam(':prenom', $prenom);
            $stmt_enseignant->execute();
            $enseignant_id = $pdo->lastInsertId();

            foreach ($subjects as $subject_id) {
                // Check that subject is linked to a class
                $stmt_check_class = $pdo->prepare("SELECT id_classe FROM matieres WHERE id = :id_matiere");
                $stmt_check_class->bindParam(':id_matiere', $subject_id);
                $stmt_check_class->execute();
                $id_classe = $stmt_check_class->fetchColumn();
                if (!$id_classe) {
                    throw new Exception("La matière sélectionnée n'est pas liée à une classe. Veuillez d'abord l'attribuer à une classe.");
                }
                $stmt_enseignant_matiere = $pdo->prepare("INSERT INTO enseignant_matiere (id_enseignant, id_matiere) VALUES (:id_enseignant, :id_matiere)");
                $stmt_enseignant_matiere->bindParam(':id_enseignant', $enseignant_id);
                $stmt_enseignant_matiere->bindParam(':id_matiere', $subject_id);
                $stmt_enseignant_matiere->execute();
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Utilisateur ajouté avec succès !";
        header('Location: ../director/dashboard.php?section=' . ($user_type === 'student' ? 'students' : 'teachers'));
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
        header('Location: ../director/dashboard.php?section=' . ($user_type === 'student' ? 'students' : 'teachers'));
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header('Location: ../director/dashboard.php?section=' . ($user_type === 'student' ? 'students' : 'teachers'));
        exit();
    }
} else {
    header('Location: ../director/dashboard.php');
    exit();
}
?>