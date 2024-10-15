// Function qui met les noms et prenoms en majuscule
function formatInputs() {
    // Récupérer les valeurs des champs
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');

    // Mettre le nom en majuscules
    nomInput.value = nomInput.value.toUpperCase();

    // Mettre la première lettre du prénom en majuscule
    if (prenomInput.value) {
        prenomInput.value = prenomInput.value.charAt(0).toUpperCase() + prenomInput.value.slice(1).toLowerCase();
    }
}

// Verification que le mot de passe est bien égal au mot de passe
function confirmmdp() {
    // Récupération des mdp
    const mdp = document.getElementById('password').value;
    const confirmmdp = document.getElementById('confirmpassword').value;
    const messageElement = document.getElementById('message');
    
    // Comparaison des deux mdp
    if (mdp===confirmmdp) {
        messageElement.textContent = ""; // Réinitialiser le message si les mdp correspondent
        return true; // Permettre la soumission du formulaire
    } else {
        messageElement.textContent = "Les mots de passe ne correspondent pas.";
        messageElement.style.color = "red"; // Changer la couleur du message
        event.preventDefault(); // Empêcher la soumission du formulaire
        return false; // Ne pas permettre la soumission
    }

}   