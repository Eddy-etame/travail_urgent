document.addEventListener('DOMContentLoaded', () => {
    // Éléments du DOM
    const addNoteBtn = document.getElementById('addNoteBtn');
    const importNotesBtn = document.getElementById('importNotesBtn');
    const exportNotesBtn = document.getElementById('exportNotesBtn');
    const searchInput = document.getElementById('searchInput');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const noteForm = document.getElementById('noteForm');
    const modal = document.getElementById('noteModal');

    // Données de test (à remplacer par des appels API)
    let notes = [];
    let students = [];
    let subjects = [];

    // Initialisation
    loadInitialData();

    // Gestionnaires d'événements
    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', () => {
            showModal();
        });
    }

    if (importNotesBtn) {
        importNotesBtn.addEventListener('click', () => {
            handleImport();
        });
    }

    if (exportNotesBtn) {
        exportNotesBtn.addEventListener('click', () => {
            handleExport();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterNotes();
        });
    }

    if (classFilter) {
        classFilter.addEventListener('change', () => {
            filterNotes();
        });
    }

    if (subjectFilter) {
        subjectFilter.addEventListener('change', () => {
            filterNotes();
        });
    }

    if (noteForm) {
        noteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleNoteSubmit();
        });
    }

    // Gestion des notifications
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsContainer = document.querySelector('.notifications-container');
    const closeNotificationsBtn = document.querySelector('.close-notifications');

    if (notificationsBtn && notificationsContainer && closeNotificationsBtn) {
        // Ouvrir le conteneur de notifications
        notificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationsContainer.classList.add('active');
        });

        // Fermer le conteneur de notifications
        closeNotificationsBtn.addEventListener('click', function() {
            notificationsContainer.classList.remove('active');
        });

        // Fermer le conteneur si on clique en dehors
        document.addEventListener('click', function(e) {
            if (!notificationsContainer.contains(e.target) && !notificationsBtn.contains(e.target)) {
                notificationsContainer.classList.remove('active');
            }
        });

        // Gestion des actions sur les notifications
        const markAsReadButtons = document.querySelectorAll('.mark-as-read');
        const deleteButtons = document.querySelectorAll('.delete-notification');

        markAsReadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                notificationItem.classList.remove('unread');
                updateNotificationCount();
            });
        });

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                notificationItem.remove();
                updateNotificationCount();
            });
        });
    }

    // Fonction pour mettre à jour le compteur de notifications
    function updateNotificationCount() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Fonctions
    async function loadInitialData() {
        try {
            // Simulation de chargement des données (à remplacer par des appels API)
            students = [
                { id: 1, name: 'Jean Dupont', class: 'B1' },
                { id: 2, name: 'Marie Martin', class: 'B1' },
                { id: 3, name: 'Pierre Durand', class: 'B2' }
            ];

            subjects = [
                { id: 1, name: 'Mathématiques' },
                { id: 2, name: 'Informatique' },
                { id: 3, name: 'Anglais' }
            ];

            notes = [
                { id: 1, studentId: 1, subjectId: 1, grade: 15.5, date: '2024-03-15', comment: 'Bon travail' },
                { id: 2, studentId: 2, subjectId: 1, grade: 14.0, date: '2024-03-15', comment: 'Peut mieux faire' }
            ];

            updateNotesTable();
            populateFilters();
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
        }
    }

    function updateNotesTable(filteredNotes = notes) {
        const tbody = document.getElementById('notesTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        filteredNotes.forEach(note => {
            const student = students.find(s => s.id === note.studentId);
            const subject = subjects.find(s => s.id === note.subjectId);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.name}</td>
                <td>${subject.name}</td>
                <td>${formatGrade(note.grade)}</td>
                <td>${formatDate(note.date)}</td>
                <td>
                    <button class="btn-secondary" onclick="editNote(${note.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-secondary" onclick="deleteNote(${note.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function populateFilters() {
        const studentSelect = document.getElementById('studentSelect');
        const subjectSelect = document.getElementById('subjectSelect');

        if (studentSelect) {
            students.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = student.name;
                studentSelect.appendChild(option);
            });
        }

        if (subjectSelect) {
            subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                subjectSelect.appendChild(option);
            });
        }
    }

    function filterNotes() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const selectedClass = classFilter ? classFilter.value : '';
        const selectedSubject = subjectFilter ? subjectFilter.value : '';

        const filteredNotes = notes.filter(note => {
            const student = students.find(s => s.id === note.studentId);
            const subject = subjects.find(s => s.id === note.subjectId);

            const matchesSearch = student.name.toLowerCase().includes(searchTerm);
            const matchesClass = !selectedClass || student.class === selectedClass;
            const matchesSubject = !selectedSubject || subject.id === parseInt(selectedSubject);

            return matchesSearch && matchesClass && matchesSubject;
        });

        updateNotesTable(filteredNotes);
    }

    function showModal(noteId = null) {
        const form = document.getElementById('noteForm');
        form.reset();

        if (noteId) {
            const note = notes.find(n => n.id === noteId);
            if (note) {
                document.getElementById('studentSelect').value = note.studentId;
                document.getElementById('subjectSelect').value = note.subjectId;
                document.getElementById('noteValue').value = note.grade;
                document.getElementById('noteDate').value = note.date;
                document.getElementById('noteComment').value = note.comment;
            }
        }

        modal.style.display = 'block';
    }

    async function handleNoteSubmit() {
        const formData = {
            studentId: parseInt(document.getElementById('studentSelect').value),
            subjectId: parseInt(document.getElementById('subjectSelect').value),
            grade: parseFloat(document.getElementById('noteValue').value),
            date: document.getElementById('noteDate').value,
            comment: document.getElementById('noteComment').value
        };

        try {
            // Simulation d'un appel API (à remplacer par votre API)
            if (formData.grade >= 0 && formData.grade <= 20) {
                const newNote = {
                    id: notes.length + 1,
                    ...formData
                };
                notes.push(newNote);
                updateNotesTable();
                modal.style.display = 'none';
                showMessage('Note enregistrée avec succès', 'success');
            } else {
                showMessage('La note doit être comprise entre 0 et 20', 'error');
            }
        } catch (error) {
            showMessage('Erreur lors de l\'enregistrement de la note', 'error');
        }
    }

    async function handleImport() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv,.xlsx';
        
        input.onchange = async (e) => {
            const file = e.target.files[0];
            if (file) {
                try {
                    // Simulation d'importation (à remplacer par votre logique d'import)
                    showMessage('Importation en cours...', 'info');
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    showMessage('Notes importées avec succès', 'success');
                } catch (error) {
                    showMessage('Erreur lors de l\'importation', 'error');
                }
            }
        };

        input.click();
    }

    async function handleExport() {
        try {
            // Simulation d'exportation (à remplacer par votre logique d'export)
            const csvContent = convertToCSV(notes);
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `notes_${formatDate(new Date())}.csv`;
            link.click();
            showMessage('Exportation réussie', 'success');
        } catch (error) {
            showMessage('Erreur lors de l\'exportation', 'error');
        }
    }

    function convertToCSV(data) {
        const headers = ['Étudiant', 'Matière', 'Note', 'Date', 'Commentaire'];
        const rows = data.map(note => {
            const student = students.find(s => s.id === note.studentId);
            const subject = subjects.find(s => s.id === note.subjectId);
            return [
                student.name,
                subject.name,
                note.grade,
                note.date,
                note.comment
            ];
        });

        return [
            headers.join(','),
            ...rows.map(row => row.join(','))
        ].join('\n');
    }

    // Fonctions globales pour les boutons d'action
    window.editNote = (noteId) => {
        showModal(noteId);
    };

    window.deleteNote = async (noteId) => {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
            try {
                // Simulation de suppression (à remplacer par votre API)
                notes = notes.filter(note => note.id !== noteId);
                updateNotesTable();
                showMessage('Note supprimée avec succès', 'success');
            } catch (error) {
                showMessage('Erreur lors de la suppression', 'error');
            }
        }
    };

    if (!userRole || !userEmail) {
        window.location.href = '../index.php';
    }
}); 