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

// Fonction pour valider la complexité du mot de passe
function validatePassword() {
    const passwordInput = document.getElementById('password');
    const password = passwordInput.value;
    const passwordFeedback = document.getElementById('password-feedback');
    
    // Réinitialiser les messages d'erreur
    passwordFeedback.innerHTML = '';
    
    // Vérifier les critères
    let isValid = true;
    const errors = [];
    
    // Vérifier la longueur minimale
    if (password.length < 12) {
        errors.push('Le mot de passe doit contenir au moins 12 caractères');
        isValid = false;
    }
    
    // Vérifier la présence d'une majuscule
    if (!/[A-Z]/.test(password)) {
        errors.push('Le mot de passe doit contenir au moins une lettre majuscule');
        isValid = false;
    }
    
    // Vérifier la présence d'une minuscule
    if (!/[a-z]/.test(password)) {
        errors.push('Le mot de passe doit contenir au moins une lettre minuscule');
        isValid = false;
    }
    
    // Vérifier la présence d'un chiffre
    if (!/[0-9]/.test(password)) {
        errors.push('Le mot de passe doit contenir au moins un chiffre');
        isValid = false;
    }
    
    // Vérifier la présence d'un caractère spécial
    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        errors.push('Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()_+-=[]{};\':"|,.<>/?)'); 
        isValid = false;
    }
    
    // Afficher les erreurs
    if (errors.length > 0) {
        const errorList = document.createElement('ul');
        errorList.className = 'password-errors';
        
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        passwordFeedback.appendChild(errorList);
        passwordInput.classList.add('is-invalid');
        passwordInput.classList.remove('is-valid');
    } else {
        // Mot de passe valide
        passwordInput.classList.add('is-valid');
        passwordInput.classList.remove('is-invalid');
        passwordFeedback.innerHTML = '<div class="valid-feedback d-block">Mot de passe valide</div>';
    }
    
    return isValid;
}

// Verification que le mot de passe est bien égal au mot de passe de confirmation
function confirmPassword() {
    // Récupération des mdp
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmpassword').value;
    const confirmFeedback = document.getElementById('confirm-feedback');
    
    // Réinitialiser le message
    confirmFeedback.innerHTML = '';
    
    // Comparaison des deux mdp
    if (password === confirmPassword && confirmPassword !== '') {
        document.getElementById('confirmpassword').classList.add('is-valid');
        document.getElementById('confirmpassword').classList.remove('is-invalid');
        confirmFeedback.innerHTML = '<div class="valid-feedback d-block">Les mots de passe correspondent</div>';
        return true;
    } else if (confirmPassword !== '') {
        document.getElementById('confirmpassword').classList.add('is-invalid');
        document.getElementById('confirmpassword').classList.remove('is-valid');
        confirmFeedback.innerHTML = '<div class="invalid-feedback d-block">Les mots de passe ne correspondent pas</div>';
        return false;
    }
    return false;
}

// Fonction pour valider le formulaire complet
function validateForm(event) {
    const isPasswordValid = validatePassword();
    const isConfirmValid = confirmPassword();
    
    if (!isPasswordValid || !isConfirmValid) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Initialisation des événements lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le formulaire d'inscription
    const registerForm = document.querySelector('form');
    
    if (registerForm) {
        // Ajouter les écouteurs d'événements pour la validation en temps réel
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmpassword');
        
        if (passwordInput) {
            // Créer des éléments pour les messages de feedback s'ils n'existent pas
            if (!document.getElementById('password-feedback')) {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.id = 'password-feedback';
                passwordInput.parentNode.appendChild(feedbackDiv);
            }
            
            passwordInput.addEventListener('input', validatePassword);
            passwordInput.addEventListener('blur', validatePassword);
        }
        
        if (confirmPasswordInput) {
            // Créer des éléments pour les messages de feedback s'ils n'existent pas
            if (!document.getElementById('confirm-feedback')) {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.id = 'confirm-feedback';
                confirmPasswordInput.parentNode.appendChild(feedbackDiv);
            }
            
            confirmPasswordInput.addEventListener('input', confirmPassword);
            confirmPasswordInput.addEventListener('blur', confirmPassword);
        }
        
        // Valider le formulaire à la soumission
        registerForm.addEventListener('submit', validateForm);
        
        // Formater les champs nom et prénom
        const nomInput = document.getElementById('nom');
        const prenomInput = document.getElementById('prenom');
        
        if (nomInput) {
            nomInput.addEventListener('blur', formatInputs);
        }
        
        if (prenomInput) {
            prenomInput.addEventListener('blur', formatInputs);
        }
    }
});