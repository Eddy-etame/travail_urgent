<?php
session_start();

// Récupérer le rôle depuis l'URL
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Rediriger vers espace.php si aucun rôle n'est spécifié initialement
if (empty($role)) {
    header('Location: espace.php');
    exit();
}

// Définir le titre et l'action du formulaire en fonction du rôle
switch ($role) {
    case 'admin':
        $title = "Connexion Administrateur";
        $form_action = "php/traitement_connexion.php"; // Point to central handler
        break;
    case 'teacher':
        $title = "Connexion Enseignant";
        $form_action = "php/traitement_connexion.php"; // Point to central handler
        break;
    case 'student':
        $title = "Connexion Étudiant";
        $form_action = "php/traitement_connexion.php"; // Point to central handler
        break;
    default:
        header('Location: espace.php'); // Redirect to choice page if role is invalid
        exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Keyce</title>
    <link rel="stylesheet" href="css/style.css">
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

        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
        }

        .login-container {
            background: #fff;
            padding: 2.5rem 3rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .login-title {
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
            border-color: #007bff;
            outline: none;
        }

        .login-button {
            background-color: #007bff;
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

        .login-button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .links-container {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .links-container a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .links-container a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: #2d3436;
            color: #fff;
        }

        .footer-logo img {
            height: 40px;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 1.5rem;
            font-size: 0.9rem;
        }

        .footer-bottom {
            background-color: #1a1e21;
            color: #aaa;
            text-align: center;
            padding: 0.75rem 0;
            font-size: 0.85rem;
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
                <a href="/" class="logo">
                    <img src="images/logo_keyce.JPEG" alt="Logo Keyce">
                </a>
            </div>
            
            <h1 class="header-title"><?php echo htmlspecialchars($title); ?></h1>
            
            <div class="header-right">
                
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="login-container">
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

            <h2 class="login-title">Connectez-vous</h2>
            <form action="<?php echo htmlspecialchars($form_action); ?>" method="POST">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                <div class="form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-button">Se connecter</button>
            </form>
            <?php if ($role !== 'admin'): // Admin doesn't create account or reset password this way ?>
                <div class="links-container">
                    <a href="mot_de_passe_oublie.php?role=<?php echo htmlspecialchars($role); ?>" class="forgot-password">Mot de passe oublié ?</a>
                    <a href="inscription_<?php echo $role === 'student' ? 'etudiant' : 'enseignant'; ?>.php" class="create-account">Créer un compte</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="images/logo_keyce.JPEG" alt="Logo Keyce">
            </div>
            <div class="footer-links">
                <a href="#">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Keyce. Tous droits réservés.</p>
        </div>
    </footer>


</body>
</html>