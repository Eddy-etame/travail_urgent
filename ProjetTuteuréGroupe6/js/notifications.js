document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de notifications chargé');
    
    // Sélection des éléments
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsContainer = document.querySelector('.notifications-container');
    const closeNotificationsBtn = document.querySelector('.close-notifications');

    console.log('Bouton notifications:', notificationsBtn);
    console.log('Conteneur notifications:', notificationsContainer);
    console.log('Bouton fermer:', closeNotificationsBtn);

    if (notificationsBtn && notificationsContainer && closeNotificationsBtn) {
        // Ouvrir le conteneur de notifications
        notificationsBtn.addEventListener('click', function(e) {
            console.log('Clic sur le bouton notifications');
            e.preventDefault();
            e.stopPropagation();
            notificationsContainer.classList.add('active');
        });

        // Fermer le conteneur de notifications
        closeNotificationsBtn.addEventListener('click', function() {
            console.log('Clic sur le bouton fermer');
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
                const notificationId = notificationItem.getAttribute('data-id');
                if (!notificationId) return;
                // AJAX request to delete_notification.php
                fetch('delete_notification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'notification_id=' + encodeURIComponent(notificationId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationItem.remove();
                        updateNotificationCount();
                    } else {
                        alert(data.message || 'Erreur lors de la suppression.');
                    }
                })
                .catch(() => {
                    alert('Erreur lors de la suppression.');
                });
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
});