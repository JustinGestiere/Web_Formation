<?php
session_start(); // Démarre la session si ce n'est pas déjà fait
require_once 'config.php';

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

$error="";
?>

<head>
    <link href="../css/utilisateurs.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_utilisateurs">
        <h1>
            Gestion des utilisateurs
        </h1>
    </div>

    <div class="page_utilisateurs"> 
        <div class="blocs_utilisateurs"> <!-- Créer les utilisateurs -->
        <details>
                <summary>
                    <h4>Créer un utilisateur</h4>
                </summary>
                <form method="POST" action="">
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

                    <label for="roles">Rôle :</label>
                    <select id="roles" name="roles" required>
                        <option value="admin">Admin</option>
                        <option value="prof">Professeur</option>
                        <option value="eleve">Élève</option>
                        <option value="visiteur">Visiteur</option>
                    </select>
                    <br><br>

                    <label for="classe_id">Classe :</label>
                    <select id="classe_id" name="classe_id">
                        <option value="">-- Sélectionner une classe --</option>
                        <?php
                        // Récupérer toutes les classes
                        $stmt = $pdo->query("SELECT id, name FROM class");
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
        </div>

        <?php
        // Gestion de la création d'utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
            if (!empty($_POST['nom']) && !empty($_POST['prenoms']) && !empty($_POST['emails']) && !empty($_POST['ages']) && !empty($_POST['passwords']) && !empty($_POST['roles'])) {
                $nom = $_POST['nom'];
                $prenoms = $_POST['prenoms'];
                $emails = $_POST['emails'];
                $ages = $_POST['ages'];
                $passwords = password_hash($_POST['passwords'], PASSWORD_BCRYPT);
                $roles = $_POST['roles'];
                $classe_id = $_POST['classe_id'];

                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE emails = :emails");
                $stmt->execute([':emails' => $emails]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "Un utilisateur avec cet email existe déjà.";
                } else {
                    // Insérer un nouvel utilisateur
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
                        $message = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
                    }
                }
            } else {
                $message = "Tous les champs sont obligatoires.";
            }
        }
        ?>
        </div>

        <div class="blocs_utilisateurs"> <!-- Modifier un utilisateur -->
            <details>
                <summary><h4>Modifier un utilisateur</h4></summary>
                <form method="GET" action="">
                    <label for="select_user">Sélectionnez un utilisateur :</label>
                    <select id="select_user" name="user_id" onchange="this.form.submit()">
                        <option value="">-- Choisissez un utilisateur --</option>
                        <?php
                        // Récupérer les utilisateurs
                        $stmt = $pdo->query("SELECT id, nom, prenoms FROM users");
                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <option value="<?php echo $user['id']; ?>" <?php if (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenoms']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>

                <?php
                // Charger les détails de l'utilisateur sélectionné
                if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                    $userId = $_GET['user_id'];

                    // Récupérer les détails de l'utilisateur
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->execute([':id' => $userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user): ?>
                        <form method="POST" action="modifier_utilisateur.php">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

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

                            <label for="roles">Rôle :</label>
                            <select id="roles" name="roles" required>
                                <option value="admin" <?php echo $user['roles'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="prof" <?php echo $user['roles'] === 'prof' ? 'selected' : ''; ?>>Professeur</option>
                                <option value="eleve" <?php echo $user['roles'] === 'eleve' ? 'selected' : ''; ?>>Élève</option>
                                <option value="visiteur" <?php echo $user['roles'] === 'visiteur' ? 'selected' : ''; ?>>Visiteur</option>
                            </select>
                            <br><br>

                            <label for="classe_id">Classe :</label>
                            <select id="classe_id" name="classe_id">
                                <?php
                                // Charger les informations actuelles de la classe
                                $currentClasseStmt = $pdo->prepare("SELECT name FROM class WHERE id = :id");
                                $currentClasseStmt->execute([':id' => $user['classe_id']]);
                                $currentClasse = $currentClasseStmt->fetch(PDO::FETCH_ASSOC);

                                // Vérification si la classe existe
                                if ($currentClasse): ?>
                                    <option value="<?php echo $user['classe_id']; ?>" selected>
                                        <?php echo htmlspecialchars($currentClasse['name']); ?>
                                    </option>
                                <?php endif; ?>

                                <option value="">-- Sélectionner une autre classe --</option>
                                <?php
                                // Charger toutes les autres classes disponibles
                                $stmt = $pdo->query("SELECT id, name FROM class");
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

        <div class="blocs_utilisateurs"> <!-- Voir les utilisateurs -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les utilisateurs
                            $sql = "SELECT nom, prenoms, roles FROM users ORDER BY nom, prenoms, roles";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les utilisateurs</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["nom"]) . " " . htmlspecialchars($name["prenoms"]) . " : " . htmlspecialchars($name["roles"]) ."</li>";
                            }
                        } else {
                            echo "<p>Aucun utilisateurs trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <div class="blocs_utilisateurs"> <!-- Supprimer les utilisateurs -->
            <details>
                <summary>
                    <h4>Supprimer un utilisateur</h4>
                </summary> 
                <form method="POST" action="">
                    <label for="delete_user">Sélectionner un utilisateur :</label>
                    <select id="delete_user" name="delete_user" required>
                        <option value="">-- Sélectionner un utilisateur --</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, nom, prenoms FROM users");
                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['nom']) . " " . htmlspecialchars($user['prenoms']) . "</option>";
                        }
                        ?>
                    </select>
                    <br><br>
                    <button type="submit" name="delete_user_btn">Supprimer</button>
                </form>
            </details>
        </div>

        <?php
        // Gestion de la suppression d'utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_btn'])) {
            $user_id = $_POST['delete_user'];
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $user_id]);
                $message = "L'utilisateur a été supprimé avec succès.";
            } catch (PDOException $e) {
                $message = "Erreur lors de la suppression : " . $e->getMessage();
            }
        }
        ?>
    </div>

    <div class="message">
        <!-- Afficher les erreurs ici -->
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>  
</section>

<?php
  include "footer.php";
?>