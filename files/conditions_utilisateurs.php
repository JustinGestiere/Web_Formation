<?php
// Démarre la session PHP si elle n'est pas déjà commencée, ce qui est nécessaire pour gérer les variables de session.
session_start(); 

// Vérifie si l'utilisateur a un rôle défini dans la session
if (isset($_SESSION['user_role'])) {
    // En fonction du rôle de l'utilisateur, on inclut le header correspondant.
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Si l'utilisateur est un administrateur
            break;
        case 'prof':
            include "header_prof.php"; // Si l'utilisateur est un professeur
            break;
        default:
            include "header.php"; // Pour les autres rôles (par exemple : élèves, parents)
            break;
    }
} else {
    // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion.
    header("Location: login.php");
    exit(); // Arrête l'exécution du script après la redirection
}
?>

<head>
    <!-- Lien vers la feuille de style CSS spécifique à cette page -->
    <link href="../css/conditions_utilisateurs.css" rel="stylesheet" />
</head>

<div class="container mt-5">
    <!-- Section principale qui contient toutes les Conditions Générales d'Utilisation (CGU) -->
    <section>
        <!-- Titre principal de la page -->
        <header>
            <h1>Conditions Générales d'Utilisation de Web Formation</h1>
            <!-- Paragraphe d'introduction sur les CGU -->
            <p class="long-text">Bienvenue sur Web Formation, une plateforme éducative conçue pour faciliter 
                l'accès aux cours, emplois du temps, classes et matières pour les élèves, 
                professeurs, parents, administrateurs, et visiteurs. Avant d'utiliser Web 
                Formation, veuillez lire attentivement les présentes Conditions Générales 
                d'Utilisation (CGU). En accédant au site, vous acceptez ces conditions sans réserve.
            </p>
        </header>
        
        <!-- Section présentant le site -->
        <section>
            <h2>1. Présentation du site</h2>
            <p class="long-text">Web Formation est une plateforme éducative accessible à 
                l'adresse suivante : [http://localhost/bts_sio/Web_Formation/files/login.php]. Le Site permet aux utilisateurs de s'informer 
                sur les cours, de consulter les emplois du temps, de signer leur présence et 
                d'interagir avec la communauté éducative. Web Formation est créé et administré par 
                Justin GESTIERE.
            </p>
        </section>

        <!-- Section expliquant l'acceptation des CGU -->
        <section>
            <h2>2. Acceptation des CGU</h2>
            <p class="long-text">En utilisant le Site, vous confirmez avoir lu et accepté ces CGU. Si vous ne les 
                acceptez pas, nous vous invitons à cesser toute utilisation de nos services.
            </p>
        </section>

        <!-- Section sur les différents types d'utilisateurs -->
        <section>
            <h2>3. Accès et profils utilisateur</h2>
            <p class="long-text">L'accès aux services de Web Formation est ouvert aux types d'utilisateurs suivants :
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

        <!-- Section détaillant les règles d'utilisation des services -->
        <section>
            <h2>4. Utilisation des services</h2>
            <pre class="long-text">
Les utilisateurs de Web Formation s'engagent à :

- Ne pas publier de contenu offensant ou illégal ;
- Respecter la confidentialité des informations ;
- Utiliser les informations de manière appropriée et respectueuse.
            </pre>
        </section>

        <!-- Section expliquant la signature de présence aux cours -->
        <section>
            <h2>5. Signature de présence aux cours</h2>
            <p class="long-text">Les signatures de présence sont destinées à un usage éducatif pour suivre la 
                fréquentation des élèves et des professeurs. Toute manipulation ou falsification 
                de présence est interdite.
            </p>
        </section>

        <!-- Section sur les droits de propriété intellectuelle -->
        <section>
            <h2>6. Droits de propriété intellectuelle</h2>
            <p class="long-text">Tous les contenus (textes, images, vidéos) diffusés sur Web Formation sont protégés 
                par les lois sur la propriété intellectuelle. Aucune reproduction ou distribution de 
                ces contenus n'est autorisée sans accord préalable.
            </p>
        </section>

        <!-- Section sur la protection des données personnelles -->
        <section>
            <h2>7. Protection des données personnelles</h2>
            <p class="long-text">Web Formation respecte les réglementations en matière de protection des données 
                notamment le RGPD. Vos informations personnelles sont sécurisées et ne seront 
                jamais partagées sans votre consentement.
            </p>
        </section>

        <!-- Section sur la limitation de responsabilité -->
        <section>
            <h2>8. Limitation de responsabilité</h2>
            <p class="long-text">Web Formation ne garantit pas que le service sera exempt de défauts ou 
                interruptions. Nous ne saurions être tenus responsables des dommages résultant de 
                l'utilisation du Site ou de l'impossibilité de l'utiliser.
            </p>
        </section>

        <!-- Section expliquant la possibilité de modification des CGU -->
        <section>
            <h2>9. Modification des CGU</h2>
            <p class="long-text">Les CGU peuvent être modifiées à tout moment. Nous vous invitons à consulter 
                cette page régulièrement pour prendre connaissance des éventuels changements.
            </p>
        </section>

        <!-- Section sur le droit applicable et la juridiction compétente -->
        <section>
            <h2>10. Droit applicable et juridiction</h2>
            <p class="long-text">Ces CGU sont régies par la loi française. En cas de litige, les tribunaux 
                compétents de Angers seront seuls compétents.
            </p>
        </section>

        <!-- Section pour le contact -->
        <section>
            <h2>11. Contact</h2>
            <p class="long-text">Pour toute question relative aux présentes CGU, vous pouvez nous contacter à 
                l'adresse suivante : justin.gestiere@gmail.com.
            </p>
        </section>
    </section>
</div>

<?php
// Inclut le footer à la fin de la page pour assurer la cohérence de la présentation sur toutes les pages.
include "footer.php";
?>
