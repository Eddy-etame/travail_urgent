<?php
session_start();
require_once '../php/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?role=admin');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    $target_audience = trim($_POST['target_audience']);

    if (empty($message) || empty($target_audience)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add_notifications.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (message, target_audience, created_at) VALUES (:message, :target_audience, NOW())");
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':target_audience', $target_audience);
        $stmt->execute();

        $_SESSION['success'] = "Notification ajoutée avec succès.";
        header("Location: add_notifications.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout de la notification : " . $e->getMessage();
        header("Location: add_notifications.php");
        exit();
    }
}

// Fetch notifications for display
try {
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Notifications</title>
    <link rel="stylesheet" href="../css/director.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo_keyce.JPEG" alt="Logo Keyce" class="logo">
                <h2>Espace Directeur</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i> Tableau de bord</a>
                <a href="dashboard.php#notifications-section" class="nav-item active"><i class="fas fa-bell"></i> Notifications</a>
                <a href="dashboard.php#students-section" class="nav-item"><i class="fas fa-users"></i> Étudiants</a>
                <a href="dashboard.php#teachers-section" class="nav-item"><i class="fas fa-chalkboard-teacher"></i> Enseignants</a>
                <a href="dashboard.php#classes-section" class="nav-item"><i class="fas fa-school"></i> Classes</a>
                <a href="dashboard.php#subjects-section" class="nav-item"><i class="fas fa-book"></i> Matières</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php" class="btn btn-danger" style="width:100%;margin-top:20px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </aside>
        <main class="main-content">
            <h1>Gestion des Notifications</h1>
            <form method="POST" action="add_notifications.php" class="notification-form">
                <textarea name="message" rows="4" cols="50" required></textarea><br>
                <label for="target_audience">Destinataires</label>
                <select name="target_audience" id="target_audience" required>
                    <option value="all">Tous (Enseignants et Étudiants)</option>
                    <option value="teachers">Enseignants</option>
                    <option value="students">Étudiants</option>
                </select><br>
                <button type="submit" class="btn-primary">Ajouter</button>
            </form>
            <h2>Notifications existantes</h2>
            <!-- In notifications.php, update notifications list -->
            <ul class="notifications-list">
                <?php if (count($notifications) === 0): ?>
                    <li>Aucune notification trouvée.</li>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php $audience = isset($notification['target_audience']) ? $notification['target_audience'] : 'Tous'; ?>
                        <li class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" data-id="<?php echo $notification['id']; ?>">
                            <?php echo htmlspecialchars($notification['message']); ?><br>
                            <small>
                                <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?><br>
                                Destinataires: <?php echo htmlspecialchars(ucfirst($audience)); ?>
                            </small>
                            <?php if (!$notification['is_read']): ?>
                                <button class="mark-as-read">Marquer comme lu</button>
                            <?php endif; ?>
                            <button class="delete-notification">Supprimer</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <script src="../js/jquery.min.js"></script>
            <script src="../js/director_notifications.js"></script>
            <script src="../js/sweetalert2.all.min.js"></script>
            <link rel="stylesheet" href="../css/sweetalert2.min.css">   
            <script src="../js/notifications.js"></script>
        </main>
    </div>
</body>
</html>
