<?php
session_start();
require_once '../php/database.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../index.php?role=teacher');
    exit();
}

$user_id = $_SESSION['user_id'];
$teacher_info = [];

try {
    $stmt = $pdo->prepare("SELECT en.nom, en.prenom, u.matricule FROM enseignants en JOIN users u ON en.id_user = u.id WHERE en.id_user = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $teacher_info = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors du chargement des informations : " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Enseignant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/teacher-dashboard.css" />
    <style>
        /* Add or override styles specific to teacher dashboard */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f5f6fa;
            color: #333;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #2d3436;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            margin: 0;
            color: #fff;
        }

        .sidebar-header img {
            max-width: 80px;
            margin-bottom: 10px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .sidebar nav {
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .nav-item i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .nav-item:hover, .nav-item.active {
            background-color: #4CAF50; /* A vibrant green for active/hover */
            transform: translateX(5px);
        }

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid #444;
            text-align: center;
        }

        .sidebar-footer a {
            color: #f8d7da; /* A light red for logout */
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #dc3545; /* Darker red on hover */
            transition: background-color 0.3s ease;
            display: inline-block; /* To apply padding */
        }

        .sidebar-footer a:hover {
            background-color: #c82333;
        }

        /* Main content styles */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            background-color: #f5f6fa;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #2d3436;
        }

        .header-right {
            display: flex;
            align-items: center;
        }



        .weather-info {
            display: flex;
            flex-direction: column;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 15px;
            font-weight: 600;
            color: #555;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card h3 {
            margin: 0 0 15px 0;
            font-size: 1.4rem;
            color: #2d3436;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .info-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-card ul li {
            padding: 8px 0;
            color: #555;
            display: flex;
            align-items: center;
        }

        .info-card ul li i {
            margin-right: 10px;
            color: #007bff;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .action-buttons .btn {
            background-color: #007bff;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .action-buttons .btn i {
            margin-right: 10px;
        }

        .action-buttons .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

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
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo_keyce.jpeg" alt="Logo Keyce" class="logo" />
                <h2>Espace Enseignant</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
                <a href="add-note.php" class="nav-item">
                    <i class="fas fa-graduation-cap"></i> Gestion des notes
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="profile.php" class="nav-item active">
                    <i class="fas fa-user-circle"></i> Profil
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </aside>
        <main class="main-content">
            <div class="header">
                <h1>Profil Enseignant</h1>
            </div>
            <section class="info-cards">
                <div class="info-card">
                    <!-- Place your profile info here -->
                    <?php if (!empty($teacher_info)): ?>
                        <div class="profile-info">
                            <p><strong>Nom:</strong> <?php echo htmlspecialchars($teacher_info['nom']); ?></p>
                            <p><strong>Prénom:</strong> <?php echo htmlspecialchars($teacher_info['prenom']); ?></p>
                            <p><strong>Matricule:</strong> <?php echo htmlspecialchars($teacher_info['matricule']); ?></p>
                        </div>
                    <?php else: ?>
                        <p>Informations non disponibles.</p>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-primary back-button" style="margin-top: 20px;">Retour au tableau de bord</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
