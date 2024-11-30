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
            <div class="blocs_utilisateurs">
                <details>
                    <summary>
                        <h4>Créer un utilisateur</h4>
                    </summary>
                    <form method="post" class="p-4 border border-light rounded">
                        <?php
                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                // Récupération des données du formulaire et nettoyage
                                $nom = trim($_POST['nom']);
                                $prenom = trim($_POST['prenom']);
                                $email = trim($_POST['username']);
                                $age = trim($_POST['age']);
                                $class = trim($_POST['class']);
                                $roles = trim($_POST['roles']);
                                $password = $_POST['password'];
                                $confirmpassword = $_POST['confirmpassword'];
                            
                                // Validation des entrées
                                if (empty($nom) || empty($prenom) || empty($email) || empty($age) || empty($roles) || empty($password) || empty($confirmpassword)) {
                                    $error = "Tous les champs doivent être remplis.";
                                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $error = "L'adresse e-mail n'est pas valide.";
                                } elseif (!is_numeric($age) || $age < 16) {
                                    $error = "L'âge doit être un nombre valide et supérieur ou égal à 16.";
                                } elseif ($password !== $confirmpassword) {
                                    $error = "Les mots de passe ne correspondent pas.";
                                } else {
                                    // Vérifier si l'email existe déjà dans la base de données
                                    $sql = "SELECT * FROM users WHERE emails = :email";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindParam(':email', $email);
                                    $stmt->execute();
                                    
                                    if ($stmt->rowCount() > 0) {
                                        $error = "Cette adresse e-mail est déjà utilisée.";
                                    } else {
                                        // Hachage du mot de passe
                                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                            
                                        // Préparation de la requête d'insertion
                                        $sql = "INSERT INTO users (nom, prenoms, emails, ages, classe_id, roles, passwords) VALUES (:nom, :prenom, :email, :age, :class, :roles, :mot_de_passe)";
                                        $stmt = $pdo->prepare($sql);
                            
                                        // Liaison des paramètres
                                        $stmt->bindParam(':nom', $nom);
                                        $stmt->bindParam(':prenom', $prenom);
                                        $stmt->bindParam(':email', $email);
                                        $stmt->bindParam(':age', $age);
                                        $stmt->bindParam(':class', $class);
                                        $stmt->bindParam(':roles', $roles);
                                        $stmt->bindParam(':mot_de_passe', $hashed_password);
                            
                                        // Exécution de la requête
                                        if ($stmt->execute()) {
                                            // Redirection après l'inscription réussie
                                            $error = "Inscription réussie.";
                                        } else {
                                            $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                                        }
                                    }
                                }
                            }
                        ?>
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" placeholder="Martin" class="form-control" id="nom" name="nom" required>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" placeholder="Jean" class="form-control" id="prenom" name="prenom" required>
                        </div>

                        <div class="form-group">
                            <label for="username">Adresse mail :</label>
                            <input type="email" placeholder="exemple@gmail.com" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Âge :</label>
                            <input type="number" placeholder="Votre âge" class="form-control" id="age" name="age" required>
                        </div>

                        <div class="form-group">
                            <label for="class">Classe :</label>
                            <input type="text" placeholder="Classe" class="form-control" id="class" name="class">
                        </div>

                        <div class="form-group">
                            <label for="roles">Rôles :</label>
                            <input type="text" placeholder="Rôles" class="form-control" id="roles" name="roles" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe :</label>
                            <input type="password" placeholder="Votre mot de passe" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirmpassword">Confirmer le mot de passe :</label>
                            <input type="password" placeholder="Confirmation de votre mot de passe" class="form-control" id="confirmpassword" name="confirmpassword" required>
                        </div>

                        <!-- Afficher les erreurs ici -->
                        <?php if ($error): ?>
                            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">Inscription</button>
                    </form>
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
</section>

<?php
  include "footer.php";
?>