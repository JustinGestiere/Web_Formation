// Sélectionner l'élément sur lequel vous voulez écouter le clic
const element = document.getElementById("refreshButton");

// Ajouter un EventListener pour écouter le clic
element.addEventListener("click", function () {
    // Rediriger vers l'URL actuelle pour rafraîchir la page
    window.location.href = window.location.href;
});

