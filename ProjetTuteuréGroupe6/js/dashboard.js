document.addEventListener('DOMContentLoaded', () => {
    // Gestion de la navigation
    const navLinks = document.querySelectorAll('.nav-links a');
    const sections = document.querySelectorAll('.section');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            
            // Mise à jour des classes actives
            navLinks.forEach(l => l.parentElement.classList.remove('active'));
            link.parentElement.classList.add('active');
            
            // Affichage de la section correspondante
            sections.forEach(section => {
                section.classList.remove('active-section');
                if (section.id === `${targetId}-section`) {
                    section.classList.add('active-section');
                }
            });
        });
    });

    // Gestion du modal
    const modal = document.getElementById('noteModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Fonction utilitaire pour formater les dates
    window.formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('fr-FR', options);
    };

    // Fonction utilitaire pour formater les notes
    window.formatGrade = (grade) => {
        return parseFloat(grade).toFixed(2);
    };

    // Fonction utilitaire pour afficher les messages
    window.showMessage = (message, type = 'info') => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${type}`;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    };

    // Gestion de la déconnexion
    const logoutBtn = document.querySelector('.logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '../index.php';
            }
        });
    }
}); 