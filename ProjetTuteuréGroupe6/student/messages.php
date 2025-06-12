<?php
session_start();
require_once '../php/database.php';

// Vérification de la session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../index.php?role=student');
    exit();
}

$user_id = $_SESSION['user_id'];
$student_info = null;

try {
    // Get student's information
    $stmt_student = $pdo->prepare("
        SELECT
            e.id AS student_id,
            e.nom,
            e.prenom,
            u.matricule,
            c.nom_classe
        FROM
            etudiants e
        JOIN
            users u ON e.id_user = u.id
        LEFT JOIN
            etudiant_classe ec ON e.id = ec.id_etudiant
        LEFT JOIN
            classes c ON ec.id_classe = c.id
        WHERE
            e.id_user = :user_id
    ");
    $stmt_student->bindParam(':user_id', $user_id);
    $stmt_student->execute();
    $student_info = $stmt_student->fetch();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <style>
        .messages-container {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            height: calc(100vh - 4rem);
        }

        .contacts-list {
            width: 300px;
            border-right: 1px solid #eee;
            padding-right: 1rem;
            overflow-y: auto;
        }

        .search-box {
            margin-bottom: 1rem;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #eee;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .contact-item:hover {
            background-color: var(--light-blue);
        }

        .contact-item.active {
            background-color: var(--light-blue);
        }

        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .contact-info {
            flex-grow: 1;
        }

        .contact-name {
            font-weight: bold;
            margin-bottom: 0.2rem;
        }

        .contact-preview {
            font-size: 0.8rem;
            color: #666;
        }

        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding-left: 1rem;
        }

        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 70%;
            padding: 1rem;
            border-radius: 10px;
            position: relative;
        }

        .message.sent {
            background-color: var(--primary-color);
            color: white;
            align-self: flex-end;
        }

        .message.received {
            background-color: var(--light-blue);
            align-self: flex-start;
        }

        .message-time {
            font-size: 0.7rem;
            margin-top: 0.5rem;
            opacity: 0.8;
        }

        .chat-input {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 1rem;
        }

        .chat-input textarea {
            flex-grow: 1;
            padding: 0.8rem;
            border: 1px solid #eee;
            border-radius: 5px;
            resize: none;
            height: 45px;
        }

        .send-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .send-button:hover {
            background-color: #0056b3;
        }

        .unread-badge {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-section">
                <img src="../images/student-avatar.png" alt="Profile" class="profile-image">
                <h3 class="profile-name"><?php echo htmlspecialchars($student_info['prenom'] . ' ' . $student_info['nom']); ?></h3>
                <p class="profile-role">Étudiant</p>
            </div>

            <div class="nav-section">
                <p class="nav-title">Apprentissage</p>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    Tableau de bord
                </a>
                <a href="emploi-du-temps.php" class="nav-item">
                    <i class="fas fa-calendar"></i>
                    Emploi du temps
                </a>
                <a href="notifications.php" class="nav-item">
                    <i class="fas fa-bell"></i>
                    Notifications
                </a>
                <a href="messages.php" class="nav-item active">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
                <a href="plan-apprentissage.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    Plan d'apprentissage
                </a>
            </div>

            <div class="nav-section">
                <p class="nav-title">Aide & Support</p>
                <a href="aide.php" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    Aide/Signalement
                </a>
                <a href="contact.php" class="nav-item">
                    <i class="fas fa-phone"></i>
                    Nous contacter
                </a>
            </div>


        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="messages-container">
                <div class="contacts-list">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un contact...">
                    </div>

                    <div class="contact-item active">
                        <img src="../images/teacher-avatar.png" alt="Contact" class="contact-avatar">
                        <div class="contact-info">
                            <div class="contact-name">Prof. Martin</div>
                            <div class="contact-preview">D'accord, je vais regarder ça...</div>
                        </div>
                        <span class="unread-badge">2</span>
                    </div>

                    <div class="contact-item">
                        <img src="../images/teacher-avatar.png" alt="Contact" class="contact-avatar">
                        <div class="contact-info">
                            <div class="contact-name">Prof. Dubois</div>
                            <div class="contact-preview">Le prochain cours sera...</div>
                        </div>
                    </div>

                    <div class="contact-item">
                        <img src="../images/teacher-avatar.png" alt="Contact" class="contact-avatar">
                        <div class="contact-info">
                            <div class="contact-name">Prof. Bernard</div>
                            <div class="contact-preview">N'oubliez pas le devoir...</div>
                        </div>
                    </div>
                </div>

                <div class="chat-area">
                    <div class="chat-header">
                        <img src="../images/teacher-avatar.png" alt="Contact" class="contact-avatar">
                        <div class="contact-info">
                            <div class="contact-name">Prof. Martin</div>
                            <div class="contact-preview">En ligne</div>
                        </div>
                    </div>

                    <div class="chat-messages">
                        <div class="message received">
                            <div class="message-content">
                                Bonjour, avez-vous des questions sur le dernier cours ?
                            </div>
                            <div class="message-time">10:30</div>
                        </div>

                        <div class="message sent">
                            <div class="message-content">
                                Oui, je n'ai pas bien compris la partie sur les fonctions récursives.
                            </div>
                            <div class="message-time">10:32</div>
                        </div>

                        <div class="message received">
                            <div class="message-content">
                                Je peux vous expliquer. Une fonction récursive est une fonction qui s'appelle elle-même...
                            </div>
                            <div class="message-time">10:35</div>
                        </div>
                    </div>

                    <div class="chat-input">
                        <textarea placeholder="Écrivez votre message..."></textarea>
                        <button class="send-button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Contact selection
            const contactItems = document.querySelectorAll('.contact-item');
            contactItems.forEach(item => {
                item.addEventListener('click', function() {
                    contactItems.forEach(contact => contact.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Send message functionality
            const sendButton = document.querySelector('.send-button');
            const messageInput = document.querySelector('.chat-input textarea');
            const chatMessages = document.querySelector('.chat-messages');

            sendButton.addEventListener('click', function() {
                const messageText = messageInput.value.trim();
                if (messageText) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'message sent';
                    messageElement.innerHTML = `
                        <div class="message-content">${messageText}</div>
                        <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    `;
                    chatMessages.appendChild(messageElement);
                    messageInput.value = '';
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });

            // Enter key to send message
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendButton.click();
                }
            });
        });
    </script>
</body>
</html> 