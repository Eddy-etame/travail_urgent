document.addEventListener('DOMContentLoaded', function() {
    // Sélection des éléments
    const requestBtn = document.getElementById('requestBtn');
    const requestModal = document.getElementById('requestModal');
    const closeRequestBtn = document.querySelector('.close-request');
    const requestForm = document.getElementById('requestForm');

    if (requestBtn && requestModal) {
        // Ouvrir le modal de requête
        requestBtn.addEventListener('click', function() {
            requestModal.classList.add('active');
        });

        // Fermer le modal de requête
        closeRequestBtn.addEventListener('click', function() {
            requestModal.classList.remove('active');
        });

        // Fermer le modal si on clique en dehors
        document.addEventListener('click', function(e) {
            if (requestModal.classList.contains('active') && 
                !requestModal.contains(e.target) && 
                !requestBtn.contains(e.target)) {
                requestModal.classList.remove('active');
            }
        });

        // Gérer la soumission du formulaire
        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                type: document.getElementById('requestType').value,
                subject: document.getElementById('requestSubject').value,
                message: document.getElementById('requestMessage').value,
                priority: document.getElementById('requestPriority').value
            };

            // Simuler l'envoi de la requête
            console.log('Requête envoyée:', formData);
            
            // Afficher un message de confirmation
            showMessage('Votre requête a été envoyée avec succès', 'success');
            
            // Fermer le modal
            requestModal.classList.remove('active');
            
            // Réinitialiser le formulaire
            requestForm.reset();
        });
    }

    // Fonction pour afficher les messages
    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        
        document.body.appendChild(messageDiv);
        
        // Supprimer le message après 3 secondes
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
}); 