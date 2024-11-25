<?php
session_start(); // Démarre la session si ce n'est pas déjà fait
include('bdd.php');

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

<head>
    <link href="../css/utilisateurs.css" rel="stylesheet" />
</head>

<section>
    <div>
        <div class="titre_utilisateurs">
            <h2>
                Gestion des utilisateurs
            </h2>
        </div>
        <div class="page_utilisateurs">
            <div class="blocs_utilisateurs">
                <details>
                    <summary>
                        <h4>Créer un utilisateur</h4>
                    </summary>
                    <div class="formulaire">
                        <form>
                            <div>
                                <label for="nom">Nom :</label>
                                <input type="text" placeholder="Nom" required>
                            </div>
                            <div>
                                <label for="prenom">Prenom :</label>
                                <input type="text" placeholder="Prenom" required>
                            </div>
                            <div>
                                <label for="email">Adresse Mail :</label>
                                <input type="email" placeholder="Adresse Mail" required>
                            </div>
                            <div>
                                <label for="age">Age :</label>
                                <input type="number" placeholder="Age" required>
                            </div>
                            <div>
                                <label for="role">Rôle :</label>
                                <input type="text" placeholder="Rôle" required>
                            </div>
                            <div>
                                <label for="classe">Classe :</label>
                                <input type="number" placeholder="Classe">
                            </div>
                            <div>
                                <label for="password">Mot de passe :</label>
                                <input type="password" placeholder="Mot de passe" required>
                            </div>
                            <div>
                                <label for="password">Confirmation du mot de passe :</label>
                                <input type="password" placeholder="Confirmation du mot de passe" required>
                            </div>
                        </form>
                    </div>
                </details>
            </div>
            <div class="blocs_utilisateurs">
                <details>
                    <summary>
                        <h4>Modifier un utilisateur</h4>
                    </summary>
                    <div>
                        ok2
                    </div>
                </details>
            </div>
            <div class="blocs_utilisateurs">
                <details>
                    <summary>
                        <h4>Voir un utilisateur</h4>
                    </summary>
                    <div>
                        ok3
                    </div>
                </details>
            </div>
            <div class="blocs_utilisateurs">
                <details>
                    <summary>
                        <h4>Supprimer un utilisateur</h4>
                    </summary>
                    <div>
                        ok4
                    </div>
                </details>
            </div>
        </div>  
    </div>
</section>

<?php
  include "footer.php";
?>