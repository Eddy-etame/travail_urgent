<?php
// C:\wamp64\www\ProjetTuteuréGroupe6\teacher\dashboard.php
session_start();
require_once '../php/database.php';

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la session
if (!isset($_SESSION['user_role'])) {
    $_SESSION['error'] = "Session non définie";
    header('Location: ../index.php?role=teacher');
    exit();
}

$teacher_id = $pdo->query("SELECT id FROM enseignants WHERE id_user = {$_SESSION['user_id']}")->fetchColumn();
$stmt = $pdo->prepare("SELECT m.id AS subject_id, m.nom_matiere, c.id AS class_id, c.nom_classe 
                       FROM enseignant_matiere em 
                       JOIN matieres m ON em.id_matiere = m.id 
                       LEFT JOIN classes c ON m.id_classe = c.id 
                       WHERE em.id_enseignant = :teacher_id");
$stmt->bindParam(':teacher_id', $teacher_id);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert both strings to lowercase for case-insensitive comparison
if (strtolower($_SESSION['user_role']) !== 'teacher') {
    $_SESSION['error'] = "Rôle incorrect : " . $_SESSION['user_role'];
    header('Location: ../index.php?role=teacher');
    exit();
}

$user_id = $_SESSION['user_id'];
$teacher_nom = '';
$teacher_prenom = '';
$assigned_subjects = [];
$assigned_classes = [];

try {
    // Get teacher's name and ID
    $stmt_teacher_info = $pdo->prepare("SELECT id, nom, prenom FROM enseignants WHERE id_user = :user_id");
    $stmt_teacher_info->bindParam(':user_id', $user_id);
    $stmt_teacher_info->execute();
    $teacher_info = $stmt_teacher_info->fetch();

    if ($teacher_info) {
        $teacher_id = $teacher_info['id'];
        $teacher_nom = $teacher_info['nom'];
        $teacher_prenom = $teacher_info['prenom'];

        // Get subjects assigned to this teacher (fetch id and name)
        $stmt_subjects = $pdo->prepare("SELECT m.id, m.nom_matiere FROM matieres m JOIN enseignant_matiere em ON m.id = em.id_matiere WHERE em.id_enseignant = :teacher_id");
        $stmt_subjects->bindParam(':teacher_id', $teacher_id);
        $stmt_subjects->execute();
        $assigned_subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

        // Get classes associated with the subjects this teacher teaches
        $stmt_classes = $pdo->prepare("
            SELECT DISTINCT c.nom_classe
            FROM classes c
            JOIN matieres m ON c.id = m.id_classe
            JOIN enseignant_matiere em ON m.id = em.id_matiere
            WHERE em.id_enseignant = :teacher_id
        ");
        $stmt_classes->bindParam(':teacher_id', $teacher_id);
        $stmt_classes->execute();
        $assigned_classes = $stmt_classes->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $_SESSION['error'] = "Vos informations d'enseignant n'ont pas été trouvées.";
        header('Location: ../php/logout.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de chargement des données de l'enseignant : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Enseignant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
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
            background-color: #4CAF50;
            transform: translateX(5px);
        }

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid #444;
            text-align: center;
        }

        .sidebar-footer a {
            color: #f8d7da;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #dc3545;
            transition: background-color 0.3s ease;
            display: inline-block;
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

        .view-students-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .view-students-btn:hover {
            background-color: #0056b3;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
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

        .btn i {
            margin-right: 10px;
        }

        .btn:hover {
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

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
            color: #333;
        }

        #studentsList {
            list-style: none;
            padding: 0;
            max-height: 300px;
            overflow-y: auto;
        }

        #studentsList li {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo_keyce.JPEG" alt="Logo Keyce" class="logo">
                <h2>Espace Enseignant</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    Tableau de bord
                </a>
                <a href="add-note.php" class="nav-item">
                    <i class="fas fa-graduation-cap"></i>
                    Gestion des notes
                </a>
                <a href="notifications.php" class="nav-item" data-section="notifications">
                    <i class="fas fa-bell"></i>
                    Notifications
                    <span class="notification-badge">3</span>
                </a>
                <a href="profile.php" class="nav-item" data-section="profile">
                    <i class="fas fa-user-circle"></i>
                    Profil
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../php/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Bienvenue, <?php echo htmlspecialchars($teacher_prenom . ' ' . $teacher_nom); ?> !</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['user_matricule']); ?> (Enseignant)</span>
                    <img src="../images/teacher-avatar.png" alt="Teacher Avatar">
                </div>
            </div>

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

            <section class="info-cards">
                <div class="info-card">
                    <h3>Mes Matières</h3>
                    <ul>
                        <?php if (!empty($assigned_subjects)): ?>
                            <?php foreach ($assigned_subjects as $subject): ?>
                                <li>
                                    <i class="fas fa-book-open"></i>
                                    <?php echo htmlspecialchars($subject['nom_matiere']); ?>
                                    <button class="view-students-btn" data-subject-id="<?php echo htmlspecialchars($subject['id']); ?>">
                                        Voir les étudiants
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Aucune matière assignée pour le moment.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="info-card">
                    <h3>Mes Classes</h3>
                    <ul>
                        <?php if (!empty($assigned_classes)): ?>
                            <?php foreach ($assigned_classes as $class): ?>
                                <li><i class="fas fa-chalkboard"></i> <?php echo htmlspecialchars($class); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Aucune classe associée pour le moment.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="info-card">
                    <h3>Ma Disponibilité</h3>
                    <form id="availabilityForm">
                        <label for="availabilityDate">Date:</label>
                        <input type="date" id="availabilityDate" name="availability_date" required>

                        <label for="availabilitySlot">Créneau horaire:</label>
                        <select id="availabilitySlot" name="start_time" required>
                            <option value="08:30:00">08:30 - 12:30</option>
                            <option value="12:30:00">12:30 - 16:30</option>
                            <option value="16:30:00">16:30 - 20:30</option>
                            <option value="20:30:00">20:30 - 00:30</option>
                        </select>

                        <button type="submit" class="btn" style="margin-top: 10px;">Enregistrer</button>
                    </form>
                    <div id="availabilityMessage"></div>
                </div>

                <div class="info-card">
                    <h3>Importer des Notes</h3>
                    <p>Importez rapidement les notes de vos étudiants via un fichier CSV conforme à la structure attendue.</p>
                    <a href="../php/sample_notes.csv" class="btn" style="margin-bottom:10px;display:inline-block;">
                        <i class="fas fa-download"></i> Télécharger un exemple CSV
                    </a>
                    <button class="btn" id="openImportNotesModal"><i class="fas fa-file-import"></i> Importer des Notes</button>
                </div>
            </section>

            <section class="action-buttons">
                <a href="#" class="btn" id="openAddNoteModal">
                    <i class="fas fa-plus-circle"></i>
                    Ajouter une Note
                </a>
                <a href="view-notes.php" class="btn">
                    <i class="fas fa-eye"></i>
                    Voir les Notes
                </a>
            </section>
        </main>
    </div>

    <!-- Modal for Students List -->
    <div id="studentsListModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Étudiants Inscrits</h2>
            <ul id="studentsList"></ul>
        </div>
    </div>

    <!-- Modal for Add Note -->
    <div id="addNoteModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeAddNoteModal">&times;</span>
            <h2>Ajouter une Note</h2>
            <iframe src="add-note.php" style="width:100%;height:500px;border:none;"></iframe>
        </div>
    </div>

    <!-- Modal for Import Notes -->
    <div id="importNotesModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeImportNotesModal">&times;</span>
            <h2>Importer des Notes</h2>
            <form id="importNotesForm" class="add-note-form" action="../php/import_notes.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="import_class">Classe</label>
                    <select name="class_id" id="import_class" required>
                        <option value="">Sélectionner une classe</option>
                        <?php foreach ($assigned_classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['nom_classe']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="import_subject">Matière</label>
                    <select name="subject_id" id="import_subject" required>
                        <option value="">Sélectionner une matière</option>
                        <?php foreach ($assigned_subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['nom_matiere']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes_file">Fichier CSV des notes</label>
                    <input type="file" name="notes_file" id="notes_file" accept=".csv" required />
                    <small>Le fichier doit suivre l'ordre des colonnes de la table <b>notes</b> : id, id_etudiant, id_matiere, note, commentaire, date_creation. Les colonnes <b>id</b> et <b>date_creation</b> peuvent être laissées vides ou ignorées.</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Importer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Removed e.preventDefault() to allow normal navigation
                    // e.preventDefault();
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                    // Navigation logic can be expanded here for SPA-like behavior if needed
                });
            });

            // Availability form submission
            const availabilityForm = document.getElementById('availabilityForm');
            const availabilityMessage = document.getElementById('availabilityMessage');

            availabilityForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('../php/save_teacher_availability.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        availabilityMessage.textContent = 'Disponibilité enregistrée avec succès.';
                        availabilityMessage.style.color = 'green';
                    } else if (data.error) {
                        availabilityMessage.textContent = 'Erreur: ' + data.error;
                        availabilityMessage.style.color = 'red';
                    } else {
                        availabilityMessage.textContent = 'Erreur inconnue.';
                        availabilityMessage.style.color = 'red';
                    }
                })
                .catch(error => {
                    availabilityMessage.textContent = 'Erreur lors de la requête.';
                    availabilityMessage.style.color = 'red';
                });
            });

            // View students for subject
            const studentsListModal = document.getElementById('studentsListModal');
            const studentsList = document.getElementById('studentsList');
            const closeButtons = document.querySelectorAll('.close');

            closeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    studentsListModal.style.display = 'none';
                    studentsList.innerHTML = '';
                });
            });

            document.querySelectorAll('.view-students-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const subjectId = button.getAttribute('data-subject-id');
                    if (!subjectId) return;

                    fetch(`view_subject_students.php?subject_id=${subjectId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert('Erreur: ' + data.error);
                                return;
                            }
                            studentsList.innerHTML = '';
                            if (data.students.length === 0) {
                                studentsList.innerHTML = '<li>Aucun étudiant inscrit pour cette matière.</li>';
                            } else {
                                data.students.forEach(student => {
                                    const li = document.createElement('li');
                                    li.textContent = `${student.nom} ${student.prenom} (Matricule: ${student.matricule})`;
                                    studentsList.appendChild(li);
                                });
                            }
                            studentsListModal.style.display = 'flex';
                        })
                        .catch(() => {
                            alert('Erreur lors de la récupération des étudiants.');
                        });
                });
            });

            // Add Note Modal logic
            const openAddNoteModalBtn = document.getElementById('openAddNoteModal');
            const addNoteModal = document.getElementById('addNoteModal');
            const closeAddNoteModalBtn = document.getElementById('closeAddNoteModal');
            if (openAddNoteModalBtn && addNoteModal && closeAddNoteModalBtn) {
                openAddNoteModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    addNoteModal.style.display = 'flex';
                });
                closeAddNoteModalBtn.addEventListener('click', function() {
                    addNoteModal.style.display = 'none';
                });
                window.addEventListener('click', function(event) {
                    if (event.target === addNoteModal) {
                        addNoteModal.style.display = 'none';
                    }
                });
            }

            // Modal logic for import notes
            const importNotesModal = document.getElementById('importNotesModal');
            const openImportNotesModalBtn = document.getElementById('openImportNotesModal');
            const closeImportNotesModalBtn = document.getElementById('closeImportNotesModal');
            if (openImportNotesModalBtn && importNotesModal) {
                openImportNotesModalBtn.onclick = () => importNotesModal.style.display = 'flex';
            }
            if (closeImportNotesModalBtn && importNotesModal) {
                closeImportNotesModalBtn.onclick = () => importNotesModal.style.display = 'none';
            }
            window.onclick = function(event) {
                if (event.target === importNotesModal) importNotesModal.style.display = 'none';
            };
        });
    </script>
</body>
</html>