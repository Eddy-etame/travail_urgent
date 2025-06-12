document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    const courseItems = document.querySelectorAll('.course-item');

    if (searchInput && courseItems.length > 0) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            courseItems.forEach(item => {
                const courseName = item.querySelector('.course-info h3').textContent.toLowerCase();
                if (courseName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // View Result button functionality
    const viewResultBtn = document.querySelector('.view-result');
    if (viewResultBtn) {
        viewResultBtn.addEventListener('click', function() {
            // Scroll to results section
            const resultsSection = document.querySelector('.results-section');
            if (resultsSection) {
                resultsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Quick action cards functionality
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('click', function() {
            const actionType = this.querySelector('.action-info h4').textContent.toLowerCase();
            switch(actionType) {
                case 'leave':
                    // Handle leave request
                    alert('Leave request feature coming soon!');
                    break;
                case 'complaint':
                    // Handle complaint
                    alert('Complaint feature coming soon!');
                    break;
            }
        });
    });

    // Progress bar animation
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });

    // Upgrade button functionality
    const upgradeBtn = document.querySelector('.upgrade-button');
    if (upgradeBtn) {
        upgradeBtn.addEventListener('click', function() {
            alert('Upgrade feature coming soon!');
        });
    }

    // Navigation item active state
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            navItems.forEach(i => i.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
            
            // If it's not the dashboard item, show coming soon message
            if (!this.classList.contains('active')) {
                e.preventDefault();
                alert('Cette fonctionnalité sera bientôt disponible!');
            }
        });
    });
}); 