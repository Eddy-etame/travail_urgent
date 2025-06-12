<?php
session_start();
require_once '../php/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $matricule = trim($_POST["matricule"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($nom) || empty($prenom) || empty($matricule) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($matricule, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "L'adresse email n'est pas valide.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE matricule = :matricule");
            $stmt_check->bindParam(':matricule', $matricule);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['error'] = "Ce matricule est déjà utilisé.";
            } else {
                $stmt_role = $pdo->prepare("SELECT id FROM roles WHERE nom_role = 'teacher'");
                $stmt_role->execute();
                $role_id = $stmt_role->fetchColumn();
                if (!$role_id) throw new Exception("Rôle 'teacher' non trouvé dans la base de données.");
                $stmt_user = $pdo->prepare("INSERT INTO users (matricule, password_hash, id_role) VALUES (:matricule, :password_hash, :id_role)");
                $stmt_user->bindParam(':matricule', $matricule);
                $stmt_user->bindParam(':password_hash', $password_hash);
                $stmt_user->bindParam(':id_role', $role_id);
                $stmt_user->execute();
                $user_id = $pdo->lastInsertId();
                $stmt_check_enseignant = $pdo->prepare("SELECT id FROM enseignants WHERE id_user = :id_user");
                $stmt_check_enseignant->bindParam(':id_user', $user_id);
                $stmt_check_enseignant->execute();
                $existing_enseignant = $stmt_check_enseignant->fetch();
                if ($existing_enseignant) {
                    $teacher_id = $existing_enseignant['id'];
                    $stmt_update_enseignant = $pdo->prepare("UPDATE enseignants SET nom = :nom, prenom = :prenom WHERE id = :id");
                    $stmt_update_enseignant->bindParam(':nom', $nom);
                    $stmt_update_enseignant->bindParam(':prenom', $prenom);
                    $stmt_update_enseignant->bindParam(':id', $teacher_id);
                    $stmt_update_enseignant->execute();
                } else {
                    $stmt_enseignant = $pdo->prepare("INSERT INTO enseignants (id_user, nom, prenom) VALUES (:id_user, :nom, :prenom)");
                    $stmt_enseignant->bindParam(':id_user', $user_id);
                    $stmt_enseignant->bindParam(':nom', $nom);
                    $stmt_enseignant->bindParam(':prenom', $prenom);
                    $stmt_enseignant->execute();
                    $teacher_id = $pdo->lastInsertId();
                }
                if (!empty($_POST['subjects']) && is_array($_POST['subjects'])) {
                    $stmt_delete = $pdo->prepare("DELETE FROM enseignant_matiere WHERE id_enseignant = :teacher_id");
                    $stmt_delete->bindParam(':teacher_id', $teacher_id);
                    $stmt_delete->execute();
                    $stmt_subject = $pdo->prepare("INSERT INTO enseignant_matiere (id_enseignant, id_matiere) VALUES (:teacher_id, :subject_id)");
                    foreach ($_POST['subjects'] as $subject_id) {
                        $stmt_subject->bindParam(':teacher_id', $teacher_id);
                        $stmt_subject->bindParam(':subject_id', $subject_id);
                        $stmt_subject->execute();
                    }
                }
                $log_dir = __DIR__ . '/../admin_logs';
                if (!is_dir($log_dir)) mkdir($log_dir, 0700, true);
                $log_file = $log_dir . '/account_creations.log';
                $timestamp = date('Y-m-d H:i:s');
                $entry = "[$timestamp] Enseignant ajouté: $nom $prenom, Matricule: $matricule" . PHP_EOL;
                file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
                $pdo->commit();
                $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("Location: ../index.php?role=teacher");
                exit();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Enseignant - Keyce</title>
    <link rel="stylesheet" href="../css/director.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f6fa;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header-container {
            background: #fff;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .header-title {
            color: #2d3436;
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
            text-align: center;
        }

        .header-left {
            position: absolute;
            left: 2rem;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
        }

        .inscription-container {
            background: #fff;
            padding: 2.5rem 3rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .inscription-title {
            color: #2d3436;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 20px);
            padding: 0.75rem 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #28a745;
            outline: none;
        }

        .inscription-button {
            background-color: #28a745;
            color: #fff;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .inscription-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .login-link {
            margin-top: 1.5rem;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Message styles */
        .message {
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <a href="javascript:history.back()" style="color: #2d3436; font-size: 1.5rem; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <h1 class="header-title">Keyce School</h1>
        </div>
    </header>

    <main class="main-content">
        <div class="inscription-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message message-success">
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="message message-error">
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <h1 class="inscription-title">Inscription Enseignant</h1>
            <form action="inscription_enseignant.php" method="POST">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>

                <div class="form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>

                <div class="form-group">
                    <label for="subjects">Matières</label>
                    <select id="subjects" name="subjects[]" multiple required>
                        <?php
                        require_once '../php/database.php';
                        try {
                            $stmt = $pdo->query("SELECT id, nom_matiere FROM matieres");
                            $subjects = $stmt->fetchAll();
                            foreach ($subjects as $subject) {
                                echo '<option value="' . htmlspecialchars($subject['id']) . '">' . htmlspecialchars($subject['nom_matiere']) . '</option>';
                            }
                        } catch (PDOException $e) {
                            echo '<option disabled>Erreur de chargement des matières</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="inscription-button">Inscrire</button>
            </form>
        </div>
    </main>
</body>
</html>
