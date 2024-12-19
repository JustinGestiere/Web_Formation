<?php
session_start(); // Démarre la session si ce n'est pas déjà fait

// Inclure le header approprié en fonction du rôle
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Si rôle admin
            break;
        case 'prof':
            include "header_prof.php"; // Si rôle prof
            break;
        default:
            include "header.php"; // Sinon le header par défaut
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
    header("Location: login.php");
    exit();
}
?>

<div class="container mt-5">
    <h1>Mentions Légales</h1>

    <section>
        <h2>1. Présentation du site</h2>
        <p>
            Le site <strong>Web Formation</strong> est une plateforme éducative accessible à l'adresse suivante : 
            <a href="http://localhost/bts_sio/Web_Formation/files/login.php">www.web_formation.com</a>.
            Il permet aux utilisateurs d'accéder à des ressources pédagogiques, de consulter des emplois du temps, de signer leur présence en cours, et d'interagir avec la communauté éducative.
        </p>
    </section>

    <section>
        <h2>2. Responsable de la publication</h2>
        <p>
            Le responsable de la publication du site est : <strong>Justin Gestiere</strong>.
            Vous pouvez le contacter par e-mail à l'adresse suivante : <a href="mailto:justin.gestiere@gmail.com">justin.gestiere@gmail.com</a>.
        </p>
    </section>

    <section>
        <h2>3. Hébergement</h2>
        <p>
            Le site est hébergé par : <strong>Gestiere Justin</strong><br>
            Adresse : <strong>1 rue Georges Mandel Angers</strong><br>
            Téléphone : <strong>02 03 04 05 06</strong>
        </p>
    </section>

    <section>
        <h2>4. Propriété intellectuelle</h2>
        <p>
            Tous les contenus présents sur le site, tels que les textes, images, vidéos, et autres éléments sont protégés par les lois sur la propriété intellectuelle. 
            Toute reproduction ou utilisation non autorisée des contenus du site est interdite sans l'accord préalable de l'éditeur.
        </p>
    </section>

    <section>
        <h2>5. Données personnelles</h2>
        <p>
            Conformément aux réglementations sur la protection des données personnelles (notamment le RGPD), les données collectées par le site sont traitées de manière sécurisée. 
            Les utilisateurs peuvent consulter la politique de confidentialité du site pour en savoir plus sur la gestion de leurs données personnelles.
        </p>
    </section>

    <section>
        <h2>6. Responsabilité</h2>
        <p>
            Web Formation s'efforce d'assurer l'exactitude des informations présentes sur le site, mais ne saurait être tenu responsable des erreurs ou omissions. 
            Le site peut contenir des liens vers des sites externes, et Web Formation ne peut être responsable du contenu de ces sites.
        </p>
    </section>

    <section>
        <h2>7. Modifications des mentions légales</h2>
        <p>
            Web Formation se réserve le droit de modifier ces mentions légales à tout moment. Il est conseillé aux utilisateurs de consulter régulièrement cette page pour être informés des éventuelles mises à jour.
        </p>
    </section>

    <section>
        <h2>8. Loi applicable</h2>
        <p>
            Ces mentions légales sont régies par la législation française. En cas de litige, seuls les tribunaux français seront compétents.
        </p>
    </section>
</div>

<?php
  include "footer.php"; // Inclure le pied de page
?>
