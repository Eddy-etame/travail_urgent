<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Étudiant - Keyce</title>
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

            <h1 class="inscription-title">Inscription Étudiant</h1>
            <form action="php/traitement_etudiant.php" method="POST">
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
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="inscription-button">S'inscrire</button>
            </form>
            <div class="login-link">
                <a href="index.php?role=student">Déjà inscrit ? Se connecter</a>
            </div>
        </div>
    </main>
</body>
</html>