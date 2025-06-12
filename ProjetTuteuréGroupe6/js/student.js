document.addEventListener('DOMContentLoaded', () => {
    // Éléments du DOM
    const semesterFilter = document.getElementById('semesterFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const downloadReleveBtn = document.getElementById('downloadReleve');
    const releveSemester = document.getElementById('releveSemester');

    // Données de test (à remplacer par des appels API)
    let notes = [];
    let studentInfo = {};

    // Initialisation
    loadStudentData();

    // Gestionnaires d'événements
    semesterFilter.addEventListener('change', () => {
        filterNotes();
    });

    subjectFilter.addEventListener('change', () => {
        filterNotes();
    });

    downloadReleveBtn.addEventListener('click', () => {
        handleDownloadReleve();
    });

    releveSemester.addEventListener('change', () => {
        updateReleve();
    });

    // Fonctions
    async function loadStudentData() {
        try {
            // Simulation de chargement des données (à remplacer par des appels API)
            studentInfo = {
                id: 'ETU123',
                name: 'Jean Dupont',
                class: 'B1',
                email: 'jean.dupont@example.com'
            };

            notes = [
                {
                    id: 1,
                    subject: 'Mathématiques',
                    grade: 15.5,
                    date: '2024-03-15',
                    semester: 'S1',
                    comment: 'Bon travail'
                },
                {
                    id: 2,
                    subject: 'Informatique',
                    grade: 16.0,
                    date: '2024-03-10',
                    semester: 'S1',
                    comment: 'Excellent'
                },
                {
                    id: 3,
                    subject: 'Anglais',
                    grade: 14.5,
                    date: '2024-03-05',
                    semester: 'S1',
                    comment: 'Peut mieux faire'
                }
            ];

            updateStudentInfo();
            updateStats();
            updateNotesTable();
            updateRecentNotes();
            populateFilters();
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
        }
    }

    function updateStudentInfo() {
        document.getElementById('studentFullName').textContent = studentInfo.name;
        document.getElementById('studentId').textContent = studentInfo.id;
        document.getElementById('studentClass').textContent = studentInfo.class;
    }

    function updateStats() {
        const averageGrade = calculateAverage(notes);
        const subjectCount = new Set(notes.map(note => note.subject)).size;
        const evolution = calculateEvolution(notes);

        document.getElementById('averageGrade').textContent = `${formatGrade(averageGrade)}/20`;
        document.getElementById('subjectCount').textContent = subjectCount;
        document.getElementById('evolution').textContent = evolution;
    }

    function calculateAverage(notes) {
        if (notes.length === 0) return 0;
        const sum = notes.reduce((acc, note) => acc + note.grade, 0);
        return sum / notes.length;
    }

    function calculateEvolution(notes) {
        if (notes.length < 2) return '--';
        
        const sortedNotes = [...notes].sort((a, b) => new Date(a.date) - new Date(b.date));
        const firstGrade = sortedNotes[0].grade;
        const lastGrade = sortedNotes[sortedNotes.length - 1].grade;
        const difference = lastGrade - firstGrade;
        
        if (difference > 0) return `+${formatGrade(difference)}`;
        if (difference < 0) return `${formatGrade(difference)}`;
        return '0';
    }

    function updateNotesTable(filteredNotes = notes) {
        const tbody = document.getElementById('notesTableBody');
        tbody.innerHTML = '';

        filteredNotes.forEach(note => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${note.subject}</td>
                <td>${formatGrade(note.grade)}</td>
                <td>${formatDate(note.date)}</td>
                <td>${note.comment}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function updateRecentNotes() {
        const tbody = document.getElementById('recentNotesTableBody');
        tbody.innerHTML = '';

        const recentNotes = [...notes]
            .sort((a, b) => new Date(b.date) - new Date(a.date))
            .slice(0, 5);

        recentNotes.forEach(note => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${note.subject}</td>
                <td>${formatGrade(note.grade)}</td>
                <td>${formatDate(note.date)}</td>
                <td>${note.comment}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function populateFilters() {
        const subjects = [...new Set(notes.map(note => note.subject))];
        const subjectFilter = document.getElementById('subjectFilter');

        subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject;
            option.textContent = subject;
            subjectFilter.appendChild(option);
        });
    }

    function filterNotes() {
        const selectedSemester = semesterFilter.value;
        const selectedSubject = subjectFilter.value;

        const filteredNotes = notes.filter(note => {
            const matchesSemester = !selectedSemester || note.semester === selectedSemester;
            const matchesSubject = !selectedSubject || note.subject === selectedSubject;
            return matchesSemester && matchesSubject;
        });

        updateNotesTable(filteredNotes);
    }

    function updateReleve() {
        const selectedSemester = releveSemester.value;
        let filteredNotes = notes;

        if (selectedSemester === 'current') {
            filteredNotes = notes.filter(note => note.semester === 'S1');
        } else if (selectedSemester === 'previous') {
            filteredNotes = notes.filter(note => note.semester === 'S2');
        }

        const summary = document.getElementById('gradesSummary');
        summary.innerHTML = '';

        const subjects = [...new Set(filteredNotes.map(note => note.subject))];
        subjects.forEach(subject => {
            const subjectNotes = filteredNotes.filter(note => note.subject === subject);
            const average = calculateAverage(subjectNotes);

            const subjectDiv = document.createElement('div');
            subjectDiv.className = 'subject-summary';
            subjectDiv.innerHTML = `
                <h4>${subject}</h4>
                <p>Moyenne: ${formatGrade(average)}/20</p>
                <div class="grades-list">
                    ${subjectNotes.map(note => `
                        <div class="grade-item">
                            <span>${formatDate(note.date)}</span>
                            <span>${formatGrade(note.grade)}/20</span>
                        </div>
                    `).join('')}
                </div>
            `;
            summary.appendChild(subjectDiv);
        });
    }

    async function handleDownloadReleve() {
        try {
            const selectedSemester = releveSemester.value;
            let filteredNotes = notes;

            if (selectedSemester === 'current') {
                filteredNotes = notes.filter(note => note.semester === 'S1');
            } else if (selectedSemester === 'previous') {
                filteredNotes = notes.filter(note => note.semester === 'S2');
            }

            // Création du contenu du relevé
            const releveContent = generateReleveContent(filteredNotes);
            
            // Création et téléchargement du fichier PDF (simulation)
            showMessage('Génération du relevé en cours...', 'info');
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Simulation de téléchargement
            const blob = new Blob([releveContent], { type: 'text/plain' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `releve_notes_${studentInfo.id}_${formatDate(new Date())}.txt`;
            link.click();
            
            showMessage('Relevé téléchargé avec succès', 'success');
        } catch (error) {
            showMessage('Erreur lors du téléchargement du relevé', 'error');
        }
    }

    function generateReleveContent(notes) {
        const subjects = [...new Set(notes.map(note => note.subject))];
        let content = `RELEVÉ DE NOTES\n`;
        content += `Étudiant: ${studentInfo.name}\n`;
        content += `Numéro étudiant: ${studentInfo.id}\n`;
        content += `Classe: ${studentInfo.class}\n\n`;

        subjects.forEach(subject => {
            const subjectNotes = notes.filter(note => note.subject === subject);
            const average = calculateAverage(subjectNotes);

            content += `${subject}\n`;
            content += `Moyenne: ${formatGrade(average)}/20\n`;
            content += 'Notes détaillées:\n';
            
            subjectNotes.forEach(note => {
                content += `${formatDate(note.date)}: ${formatGrade(note.grade)}/20`;
                if (note.comment) {
                    content += ` - ${note.comment}`;
                }
                content += '\n';
            });
            
            content += '\n';
        });

        return content;
    }

    if (!userRole || !userEmail) {
        window.location.href = '../index.php';
    }
}); 