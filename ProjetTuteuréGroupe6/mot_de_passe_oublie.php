<?php
session_start();
$role = isset($_GET['role']) && in_array($_GET['role'], ['teacher', 'student']) ? $_GET['role'] : 'student';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du Mot de Passe - Keyce</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Inline CSS from index.php for consistency -->
    <style>
        /* Copy inline CSS from index.php */
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
            <h1 class="header-title">Réinitialisation du Mot de Passe</h1>
        </div>
    </header>
    <main class="main-content">
        <div class="login-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message message-success">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message message-error">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <h2 class="login-title">Réinitialiser votre mot de passe</h2>
            <form action="php/traitement_reset_password.php" method="POST">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                <div class="form-group">
                    <label for="matricule">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>
                <button type="submit" class="login-button">Envoyer le lien de réinitialisation</button>
            </form>
            <div class="links-container">
                <a href="index.php?role=<?php echo htmlspecialchars($role); ?>">Retour à la connexion</a>
            </div>
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