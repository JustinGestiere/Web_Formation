<?php
// Démarrage de la session pour gérer l'authentification utilisateur
session_start(); 

// Inclusion du fichier de connexion à la base de données
require_once 'bdd.php';

// Gestion des droits d'accès selon le rôle de l'utilisateur
// Chaque rôle a un header spécifique avec des options adaptées
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            include "header_admin.php"; // Interface administrateur avec toutes les fonctionnalités
            break;
        case 'prof':
            include "header_prof.php"; // Interface professeur avec des fonctionnalités limitées
            break;
        default:
            include "header.php"; // Interface par défaut pour les autres rôles
            break;
    }
} else {
    // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
    header("Location: login.php");
    exit();
}

// Variable pour stocker les messages d'erreur
$error = "";
?>

<head>
    <!-- Inclusion de la feuille de style spécifique à cette page -->
    <link href="../css/utilisateurs.css" rel="stylesheet" />
</head>

<section>
    <!-- En-tête de la page -->
    <div class="titre_utilisateurs">
        <h1>
            Gestion des utilisateurs
        </h1>
    </div>

    <div class="page_utilisateurs">

        <!-- BLOC 1: CRÉATION D'UTILISATEUR -->
        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <h4>Créer un utilisateur</h4>
                </summary>
                <!-- Formulaire de création avec les champs nécessaires -->
                <form method="POST" action="">
                    <!-- Champs pour les informations de base -->
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                    <br><br>

                    <label for="prenoms">Prénom :</label>
                    <input type="text" id="prenoms" name="prenoms" required>
                    <br><br>

                    <label for="emails">Email :</label>
                    <input type="email" id="emails" name="emails" required>
                    <br><br>

                    <label for="ages">Âge :</label>
                    <input type="number" id="ages" name="ages" min="0" required>
                    <br><br>

                    <label for="passwords">Mot de passe :</label>
                    <input type="password" id="passwords" name="passwords" required>
                    <br><br>

                    <!-- Menu déroulant pour sélectionner le rôle -->
                    <label for="roles">Rôle :</label>
                    <select id="roles" name="roles" required>
                        <option value="admin">Admin</option>
                        <option value="prof">Professeur</option>
                        <option value="eleve">Élève</option>
                        <option value="visiteur">Visiteur</option>
                    </select>
                    <br><br>

                    <!-- Menu déroulant pour sélectionner la classe -->
                    <label for="classe_id">Classe :</label>
                    <select id="classe_id" name="classe_id">
                        <option value="">-- Sélectionner une classe --</option>
                        <?php
                        // Récupération de toutes les classes disponibles depuis la base de données
                        $stmt = $pdo->query("SELECT id, name FROM classes");
                        // Boucle pour afficher chaque classe comme option
                        while ($classe = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $classe['id']; ?>">
                                <?php echo htmlspecialchars($classe['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <br><br>

                    <button type="submit" name="create_user">Créer l'utilisateur</button>
                </form>
            </details>

            <?php
            // Traitement du formulaire de création d'utilisateur
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
                // Vérification que tous les champs obligatoires sont remplis
                if (!empty($_POST['nom']) && !empty($_POST['prenoms']) && !empty($_POST['emails']) && 
                    !empty($_POST['ages']) && !empty($_POST['passwords']) && !empty($_POST['roles'])) {
                    
                    // Récupération des valeurs du formulaire
                    $nom = $_POST['nom'];
                    $prenoms = $_POST['prenoms'];
                    $emails = $_POST['emails'];
                    $ages = $_POST['ages'];
                    // Hachage du mot de passe pour sécuriser le stockage
                    $passwords = password_hash($_POST['passwords'], PASSWORD_BCRYPT);
                    $roles = $_POST['roles'];
                    $classe_id = $_POST['classe_id']; // Peut être vide si non applicable

                    // Vérification de l'unicité de l'email (évite les doublons)
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE emails = :emails");
                    $stmt->execute([':emails' => $emails]);
                    if ($stmt->fetchColumn() > 0) {
                        $message = "Un utilisateur avec cet email existe déjà.";
                    } else {
                        // Insertion du nouvel utilisateur dans la base de données
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO users (nom, prenoms, emails, ages, passwords, roles, classe_id)
                                VALUES (:nom, :prenoms, :emails, :ages, :passwords, :roles, :classe_id)
                            ");
                            $stmt->execute([
                                ':nom' => $nom,
                                ':prenoms' => $prenoms,
                                ':emails' => $emails,
                                ':ages' => $ages,
                                ':passwords' => $passwords,
                                ':roles' => $roles,
                                ':classe_id' => $classe_id
                            ]);
                            $message = "L'utilisateur a été créé avec succès.";
                        } catch (PDOException $e) {
                            // Gestion des erreurs lors de l'insertion
                            $message = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
                        }
                    }
                } else {
                    $message = "Tous les champs sont obligatoires.";
                }
            }
            ?>
        </div>

        <!-- BLOC 2: MODIFICATION D'UTILISATEUR -->
        <div class="blocs_utilisateurs">
            <details>
                <summary><h4>Modifier un utilisateur</h4></summary>
                <!-- Formulaire de sélection d'utilisateur à modifier -->
                <form method="GET" action="">
                    <label for="select_user">Sélectionnez un utilisateur :</label>
                    <select id="select_user" name="user_id" onchange="this.form.submit()">
                        <option value="">-- Choisissez un utilisateur --</option>
                        <?php
                        // Récupération de tous les utilisateurs pour le menu déroulant
                        $stmt = $pdo->query("SELECT id, nom, prenoms FROM users");
                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <!-- Option avec préselection si l'utilisateur est déjà choisi -->
                            <option value="<?php echo $user['id']; ?>" 
                                <?php if (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenoms']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>

                <?php
                // Affichage et traitement du formulaire de modification pour l'utilisateur sélectionné
                if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                    $userId = $_GET['user_id'];

                    // Récupération des données de l'utilisateur sélectionné
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->execute([':id' => $userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Affichage du formulaire pré-rempli avec les données actuelles
                    if ($user): ?>
                        <form method="POST" action="modifier_utilisateur.php">
                            <!-- Champ caché pour l'ID de l'utilisateur -->
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                            <!-- Champs pour modifier les informations utilisateur -->
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            <br><br>

                            <label for="prenoms">Prénom :</label>
                            <input type="text" id="prenoms" name="prenoms" value="<?php echo htmlspecialchars($user['prenoms']); ?>" required>
                            <br><br>

                            <label for="emails">Email :</label>
                            <input type="email" id="emails" name="emails" value="<?php echo htmlspecialchars($user['emails']); ?>" required>
                            <br><br>

                            <label for="ages">Âge :</label>
                            <input type="number" id="ages" name="ages" value="<?php echo $user['ages']; ?>" required>
                            <br><br>

                            <!-- Menu déroulant pour le rôle avec option actuelle présélectionnée -->
                            <label for="roles">Rôle :</label>
                            <select id="roles" name="roles" required>
                                <option value="admin" <?php echo $user['roles'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="prof" <?php echo $user['roles'] === 'prof' ? 'selected' : ''; ?>>Professeur</option>
                                <option value="eleve" <?php echo $user['roles'] === 'eleve' ? 'selected' : ''; ?>>Élève</option>
                                <option value="visiteur" <?php echo $user['roles'] === 'visiteur' ? 'selected' : ''; ?>>Visiteur</option>
                            </select>
                            <br><br>

                            <!-- Menu déroulant pour la classe avec option actuelle présélectionnée -->
                            <label for="classe_id">Classe :</label>
                            <select id="classe_id" name="classe_id">
                                <?php
                                // Récupération du nom de la classe actuelle
                                $currentClasseStmt = $pdo->prepare("SELECT name FROM classes WHERE id = :id");
                                $currentClasseStmt->execute([':id' => $user['classe_id']]);
                                $currentClasse = $currentClasseStmt->fetch(PDO::FETCH_ASSOC);

                                // Affichage de la classe actuelle en premier si elle existe
                                if ($currentClasse): ?>
                                    <option value="<?php echo $user['classe_id']; ?>" selected>
                                        <?php echo htmlspecialchars($currentClasse['name']); ?>
                                    </option>
                                <?php endif; ?>

                                <option value="">-- Sélectionner une autre classe --</option>
                                <?php
                                // Liste des autres classes disponibles
                                $stmt = $pdo->query("SELECT id, name FROM classes");
                                while ($classe = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $classe['id']; ?>">
                                        <?php echo htmlspecialchars($classe['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <br><br>

                            <button type="submit" name="update_user">Enregistrer les modifications</button>
                        </form>
                    <?php endif;
                }
                ?>
            </details>
        </div>

        <!-- BLOC 3: LISTE DES UTILISATEURS -->
        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <?php
                        try {
                            // Récupération de tous les utilisateurs triés par nom, prénom et rôle
                            $sql = "SELECT nom, prenoms, roles FROM users ORDER BY nom, prenoms, roles";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            // Journalisation des erreurs sans les afficher aux utilisateurs
                            error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les utilisateurs</h4>
                    </p>
                </summary>
                <!-- Affichage de la liste des utilisateurs -->
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        // Vérification qu'il y a des utilisateurs à afficher
                        if ($namesCount > 0) {
                            // Boucle sur tous les utilisateurs pour les afficher
                            foreach ($names as $name) {
                                // Échappement des caractères spéciaux pour éviter les attaques XSS
                                echo "<li>" . htmlspecialchars($name["nom"]) . " " . 
                                     htmlspecialchars($name["prenoms"]) . " : " . 
                                     htmlspecialchars($name["roles"]) ."</li>";
                            }
                        } else {
                            echo "<p>Aucun utilisateurs trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- BLOC 4: SUPPRESSION D'UTILISATEUR -->
        <div class="blocs_utilisateurs">
            <details>
                <summary>
                    <h4>Supprimer un utilisateur</h4>
                </summary> 
                <!-- Formulaire de suppression d'utilisateur -->
                <form method="POST" action="">
                    <label for="delete_user">Sélectionner un utilisateur :</label>
                    <select id="delete_user" name="delete_user" required>
                        <option value="">-- Sélectionner un utilisateur --</option>
                        <?php
                        // Récupération de tous les utilisateurs pour le menu déroulant
                        $stmt = $pdo->query("SELECT id, nom, prenoms FROM users");
                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $user['id'] . "'>" . 
                                 htmlspecialchars($user['nom']) . " " . 
                                 htmlspecialchars($user['prenoms']) . "</option>";
                        }
                        ?>
                    </select>
                    <br><br>
                    <button type="submit" name="delete_user_btn">Supprimer</button>
                </form>
            </details>
            <?php
                // Traitement de la demande de suppression d'utilisateur
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_btn'])) {
                    $user_id = $_POST['delete_user'];
                    if (empty($user_id)) {
                        $_SESSION['message'] = "Aucun utilisateur sélectionné.";
                    } else {
                        try {
                            // Suppression de l'utilisateur de la base de données
                            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                            $stmt->execute([':id' => $user_id]);
                            $_SESSION['message'] = "L'utilisateur a été supprimé avec succès.";
                        } catch (PDOException $e) {
                            // Gestion des erreurs lors de la suppression
                            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
                            $_SESSION['message'] = "Erreur lors de la suppression de l'utilisateur.";
                        }
                    }
                    // Redirection pour actualiser la page et afficher le message
                    header("Location: utilisateurs.php");
                    exit();
                }
            ?>
        </div>
        
    </div>

    <!-- Affichage des messages de confirmation ou d'erreur -->
    <div class="message">
        <?php 
        // Affichage du message de session s'il existe
        if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
            echo '<p>' . htmlspecialchars($_SESSION['message']) . '</p>';
            unset($_SESSION['message']); // Effacer le message après l'avoir affiché
        } 
        // Affichage du message local s'il existe
        elseif (isset($message) && !empty($message)) {
            echo '<p>' . htmlspecialchars($message) . '</p>';
        }
        ?>
    </div>  
</section>

<?php
  // Inclusion du pied de page commun à toutes les pages
  include "footer.php";
?>