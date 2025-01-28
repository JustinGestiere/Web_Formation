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
    <!-- Global Section -->
    <section>
        <!-- Titre Principal -->
        <header>
            <h1>Conditions Générales d'Utilisation de Web Formation</h1>
            <p>Bienvenue sur Web Formation, une plateforme éducative conçue pour faciliter 
                l'accès aux cours, emplois du temps, classes et matières pour les élèves, 
                professeurs, parents, administrateurs, et visiteurs. Avant d'utiliser Web 
                Formation, veuillez lire attentivement les présentes Conditions Générales 
                d'Utilisation (CGU). En accédant au site, vous acceptez ces conditions sans réserve.
            </p>
        </header>
        
        <!-- Sections des CGU -->
        <section>
            <h2>1. Présentation du site</h2>
            <p>Web Formation est une plateforme éducative accessible à 
                l'adresse suivante : [http://localhost/bts_sio/Web_Formation/files/login.php]. Le Site permet aux utilisateurs de s'informer 
                sur les cours, de consulter les emplois du temps, de signer leur présence et 
                d'interagir avec la communauté éducative. Web Formation est créé et administré par 
                Justin GESTIERE.
            </p>
        </section>

        <section>
            <h2>2. Acceptation des CGU</h2>
            <p>En utilisant le Site, vous confirmez avoir lu et accepté ces CGU. Si vous ne les 
                acceptez pas, nous vous invitons à cesser toute utilisation de nos services.
            </p>
        </section>

        <section>
            <h2>3. Accès et profils utilisateur</h2>
            <p>L'accès aux services de Web Formation est ouvert aux types d'utilisateurs suivants :
                <ul>
                    <li>Élèves : accès aux cours, emplois du temps, signatures de présence.</li>
                    <li>Professeurs : accès à la gestion des cours et des présences.</li>
                    <li>Parents : accès limité aux informations de suivi scolaire.</li>
                    <li>Administrateurs : gestion du site, des comptes, des cours, et des emplois du temps.</li>
                    <li>Visiteurs externes : accès limité aux informations publiques.</li>
                </ul>
                L'inscription peut nécessiter des informations d'identification précises, complètes et à jour.
            </p>
        </section>

        <section>
            <h2>4. Utilisation des services</h2>
            <pre>
Les utilisateurs de Web Formation s'engagent à :

- Ne pas publier de contenu offensant ou illégal ;
- Respecter la confidentialité des informations ;
- Utiliser les informations de manière appropriée et respectueuse.
            </pre>
        </section>

        <section>
            <h2>5. Signature de présence aux cours</h2>
            <p>Les signatures de présence sont destinées à un usage éducatif pour suivre la 
                fréquentation des élèves et des professeurs. Toute manipulation ou falsification 
                de présence est interdite.
            </p>
        </section>

        <section>
            <h2>6. Droits de propriété intellectuelle</h2>
            <p>Tous les contenus (textes, images, vidéos) diffusés sur Web Formation sont protégés 
                par les lois sur la propriété intellectuelle. Aucune reproduction ou distribution de 
                ces contenus n'est autorisée sans accord préalable.
            </p>
        </section>

        <section>
            <h2>7. Protection des données personnelles</h2>
            <p>Web Formation respecte les réglementations en matière de protection des données 
                notamment le RGPD. Vos informations personnelles sont sécurisées et ne seront 
                jamais partagées sans votre consentement.
            </p>
        </section>

        <section>
            <h2>8. Limitation de responsabilité</h2>
            <p>Web Formation ne garantit pas que le service sera exempt de défauts ou 
                interruptions. Nous ne saurions être tenus responsables des dommages résultant de 
                l'utilisation du Site ou de l'impossibilité de l'utiliser.
            </p>
        </section>

        <section>
            <h2>9. Modification des CGU</h2>
            <p>Les CGU peuvent être modifiées à tout moment. Nous vous invitons à consulter 
                cette page régulièrement pour prendre connaissance des éventuels changements.
            </p>
        </section>

        <section>
            <h2>10. Droit applicable et juridiction</h2>
            <p>Ces CGU sont régies par la loi française. En cas de litige, les tribunaux 
                compétents de Angers seront seuls compétents.
            </p>
        </section>

        <section>
            <h2>11. Contact</h2>
            <p>Pour toute question relative aux présentes CGU, vous pouvez nous contacter à 
                l'adresse suivante : justin.gestiere@gmail.com.
            </p>
        </section>
    </section>
</div>

<?php
  include "footer.php";
?>
