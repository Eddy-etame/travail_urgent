document.addEventListener('DOMContentLoaded', () => {
    // Remove SPA-style nav-item click handler to allow normal navigation
    // const navItems = document.querySelectorAll('.nav-item');
    // const contentSections = document.querySelectorAll('.content-section');
    // navItems.forEach(item => {
    //     item.addEventListener('click', function(e) {
    //         e.preventDefault();
    //         navItems.forEach(nav => nav.classList.remove('active'));
    //         this.classList.add('active');
    //         const targetSection = this.getAttribute('data-section') + '-section';
    //         contentSections.forEach(section => {
    //             if (section.id === targetSection) {
    //                 section.classList.add('active');
    //             } else {
    //                 section.classList.remove('active');
    //             }
    //         });
    //     });
    // });

const editStudentButtons = document.querySelectorAll('.edit-student');
const editTeacherButtons = document.querySelectorAll('.edit-teacher');
const editSubjectButtons = document.querySelectorAll('.edit-subject');
const editStudentModal = document.getElementById('editStudentModal');
const editTeacherModal = document.getElementById('editTeacherModal');
const editSubjectModal = document.getElementById('editSubjectModal');

editStudentButtons.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const nom = button.dataset.nom;
        const prenom = button.dataset.prenom;
        const matricule = button.dataset.matricule;
        document.getElementById('editStudentId').value = id;
        document.getElementById('editStudentNom').value = nom;
        document.getElementById('editStudentPrenom').value = prenom;
        document.getElementById('editStudentMatricule').value = matricule;
        openModal(editStudentModal);
});

editTeacherButtons.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const nom = button.dataset.nom;
        const prenom = button.dataset.prenom;
        const matricule = button.dataset.matricule;
        document.getElementById('editTeacherId').value = id;
        document.getElementById('editTeacherNom').value = nom;
        document.getElementById('editTeacherPrenom').value = prenom;
        document.getElementById('editTeacherMatricule').value = matricule;
        // Populate subjects via AJAX if needed
        openModal(editTeacherModal);
    });
});

editSubjectButtons.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const nom = button.dataset.nom;
        const classId = button.dataset.classId;
        document.getElementById('editSubjectId').value = id;
        document.getElementById('editSubjectNom').value = nom;
        document.getElementById('editSubjectClass').value = classId || '';
        openModal(editSubjectModal);
    });
});

    // Modal functionality
    const addUserModal = document.getElementById('addUserModal');
    const addClassModal = document.getElementById('addClassModal');
    const addSubjectModal = document.getElementById('addSubjectModal');

    const addStudentBtn = document.getElementById('addStudentBtn');
    const addTeacherBtn = document.getElementById('addTeacherBtn');
    const addClassBtn = document.getElementById('addClassBtn');
    const addSubjectBtn = document.getElementById('addSubjectBtn');

    const closeButtons = document.querySelectorAll('.close-button, .close-modal');
    const userTypeSelect = document.getElementById('userType');
    const classGroup = document.getElementById('classGroup');
    const subjectsGroup = document.getElementById('subjectsGroup');

    // Function to open a modal
    function openModal(modal) {
        modal.style.display = 'flex'; // Use flex to center content
    }

    // Function to close a modal
    function closeModal(modal) {
        modal.style.display = 'none';
        // Reset form fields if needed
        if (modal.querySelector('form')) {
            modal.querySelector('form').reset();
        }
        // Hide specific groups for Add User modal
        if (classGroup) classGroup.style.display = 'none';
        if (subjectsGroup) subjectsGroup.style.display = 'none';
    }

    if (addStudentBtn) {
        addStudentBtn.addEventListener('click', () => {
            if (userTypeSelect) userTypeSelect.value = 'student'; // Pre-select student
            if (classGroup) classGroup.style.display = 'block';
            if (subjectsGroup) subjectsGroup.style.display = 'none';
            if (addUserModal) openModal(addUserModal);
        });
    }

    if (addTeacherBtn) {
        addTeacherBtn.addEventListener('click', () => {
            if (userTypeSelect) userTypeSelect.value = 'teacher'; // Pre-select teacher
            if (classGroup) classGroup.style.display = 'none';
            if (subjectsGroup) subjectsGroup.style.display = 'block';
            if (addUserModal) openModal(addUserModal);
        });
    }

    if (addClassBtn) {
        addClassBtn.addEventListener('click', () => {
            if (addClassModal) openModal(addClassModal);
        });
    }

    if (addSubjectBtn) {
        addSubjectBtn.addEventListener('click', () => {
            if (addSubjectModal) openModal(addSubjectModal);
        });
    }

    closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modalToClose = e.target.closest('.modal');
            if (modalToClose) {
                closeModal(modalToClose);
            }
        });
    });

    // New event listeners for informational modals
    const manageStudentsBtn = document.getElementById('manageStudentsBtn');
    const manageTeachersBtn = document.getElementById('manageTeachersBtn');
    const manageClassesBtn = document.getElementById('manageClassesBtn');
    const manageSubjectsBtn = document.getElementById('manageSubjectsBtn');

    const infoStudentsModal = document.getElementById('infoStudentsModal');
    const infoTeachersModal = document.getElementById('infoTeachersModal');
    const infoClassesModal = document.getElementById('infoClassesModal');
    const infoSubjectsModal = document.getElementById('infoSubjectsModal');

    if (manageStudentsBtn && infoStudentsModal) {
        manageStudentsBtn.addEventListener('click', () => {
            openModal(infoStudentsModal);
        });
    }

    if (manageTeachersBtn && infoTeachersModal) {
        manageTeachersBtn.addEventListener('click', () => {
            openModal(infoTeachersModal);
        });
    }

    if (manageClassesBtn && infoClassesModal) {
        manageClassesBtn.addEventListener('click', () => {
            openModal(infoClassesModal);
        });
    }

    if (manageSubjectsBtn && infoSubjectsModal) {
        manageSubjectsBtn.addEventListener('click', () => {
            openModal(infoSubjectsModal);
        });
    }

    // Remove old edit-student/edit-teacher/edit-subject logic and use only .edit-btn for all
    // This ensures all edit buttons work, regardless of section

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const type = button.dataset.type;
            if (type === 'student') {
                document.getElementById('editStudentId').value = button.dataset.id;
                document.getElementById('editStudentNom').value = button.dataset.nom;
                document.getElementById('editStudentPrenom').value = button.dataset.prenom;
                document.getElementById('editStudentMatricule').value = button.dataset.matricule;
                document.getElementById('editStudentClass').value = button.dataset.classe || '';
                openModal(document.getElementById('editStudentModal'));
            } else if (type === 'teacher') {
                document.getElementById('editTeacherId').value = button.dataset.id;
                document.getElementById('editTeacherNom').value = button.dataset.nom;
                document.getElementById('editTeacherPrenom').value = button.dataset.prenom;
                document.getElementById('editTeacherMatricule').value = button.dataset.matricule;
                openModal(document.getElementById('editTeacherModal'));
            } else if (type === 'class') {
                document.getElementById('editClassId').value = button.dataset.id;
                document.getElementById('editClassName').value = button.dataset.nom;
                openModal(document.getElementById('editClassModal'));
            } else if (type === 'subject') {
                document.getElementById('editSubjectId').value = button.dataset.id;
                document.getElementById('editSubjectNom').value = button.dataset.nom;
                openModal(document.getElementById('editSubjectModal'));
            }
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === addUserModal) closeModal(addUserModal);
        if (e.target === addClassModal) closeModal(addClassModal);
        if (e.target === addSubjectModal) closeModal(addSubjectModal);
    });

    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', () => {
            const selectedType = userTypeSelect.value;
            if (selectedType === 'student') {
                if (classGroup) classGroup.style.display = 'block';
                if (subjectsGroup) subjectsGroup.style.display = 'none';
            } else if (selectedType === 'teacher') {
                if (classGroup) classGroup.style.display = 'none';
                if (subjectsGroup) subjectsGroup.style.display = 'block';
            } else {
                if (classGroup) classGroup.style.display = 'none';
                if (subjectsGroup) subjectsGroup.style.display = 'none';
            }
        });
    }

    // Chart.js: Affichage du graphique des moyennes de notes par classe
    if (typeof classLabels !== 'undefined' && typeof classAverages !== 'undefined') {
        const ctx = document.getElementById('performanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: classLabels,
                    datasets: [{
                        label: 'Moyenne des notes',
                        data: classAverages.map(v => v === null ? 0 : v),
                        backgroundColor: classAverages.map(v => v === null ? 'rgba(220,53,69,0.7)' : 'rgba(54,162,235,0.7)'),
                        borderColor: classAverages.map(v => v === null ? 'rgba(220,53,69,1)' : 'rgba(54,162,235,1)'),
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (classAverages[context.dataIndex] === null) {
                                        return 'Aucune note';
                                    }
                                    return 'Moyenne: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20,
                            title: {
                                display: true,
                                text: 'Note sur 20'
                            }
                        }
                    }
                }
            });
        }
    }
});

    window.addEventListener('click', (e) => {
        if (e.target === addUserModal) closeModal(addUserModal);
        if (e.target === addClassModal) closeModal(addClassModal);
        if (e.target === addSubjectModal) closeModal(addSubjectModal);
    });

    if (userTypeSelect) {
        userTypeSelect.addEventListener('change', () => {
            const selectedType = userTypeSelect.value;
            if (selectedType === 'student') {
                if (classGroup) classGroup.style.display = 'block';
                if (subjectsGroup) subjectsGroup.style.display = 'none';
            } else if (selectedType === 'teacher') {
                if (classGroup) classGroup.style.display = 'none';
                if (subjectsGroup) subjectsGroup.style.display = 'block';
            } else {
                if (classGroup) classGroup.style.display = 'none';
                if (subjectsGroup) subjectsGroup.style.display = 'none';
            }
        });
    }

    // Chart.js: Affichage du graphique des moyennes de notes par classe
    if (typeof classLabels !== 'undefined' && typeof classAverages !== 'undefined') {
        const ctx = document.getElementById('performanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: classLabels,
                    datasets: [{
                        label: 'Moyenne des notes',
                        data: classAverages.map(v => v === null ? 0 : v),
                        backgroundColor: classAverages.map(v => v === null ? 'rgba(220,53,69,0.7)' : 'rgba(54,162,235,0.7)'),
                        borderColor: classAverages.map(v => v === null ? 'rgba(220,53,69,1)' : 'rgba(54,162,235,1)'),
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (classAverages[context.dataIndex] === null) {
                                        return 'Aucune note';
                                    }
                                    return 'Moyenne: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20,
                            title: {
                                display: true,
                                text: 'Note sur 20'
                            }
                        }
                    }
                }
            });
        }
    }
});


