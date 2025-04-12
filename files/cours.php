<?php
session_start(); // Démarre une session PHP pour gérer les données utilisateur entre les pages

// Variable pour stocker les messages à afficher à l'utilisateur
$message = "";

// Traitement des formulaires avant tout affichage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inclusion du fichier de configuration de la base de données
    require_once 'bdd.php';
    
    // Création d'un cours
    if (isset($_POST['titre'], $_POST['description'], $_POST['date_debut'], $_POST['date_fin'], 
              $_POST['professeur_id'], $_POST['classes_id'], $_POST['matiere_id'])) {
        
        // Récupération des données du formulaire
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $professeur_id = $_POST['professeur_id'];
        $classes_id = $_POST['classes_id'];
        $matiere_id = $_POST['matiere_id'];

        // Vérification de l'unicité du titre du cours (évite les doublons)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE titre = :titre");
        $stmt->execute([':titre' => $titre]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = "Un cours avec ce titre existe déjà. Veuillez en choisir un autre.";
        } else {
            // Insertion du nouveau cours dans la base de données
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO cours (titre, description, date_debut, date_fin, professeur_id, classes_id, matiere_id)
                    VALUES (:titre, :description, :date_debut, :date_fin, :professeur_id, :classes_id, :matiere_id)
                ");
                // Exécution avec tableau associatif pour lier les paramètres (sécurité contre les injections SQL)
                $stmt->execute([
                    ':titre' => $titre,
                    ':description' => $description,
                    ':date_debut' => $date_debut,
                    ':date_fin' => $date_fin,
                    ':professeur_id' => $professeur_id,
                    ':classes_id' => $classes_id,
                    ':matiere_id' => $matiere_id
                ]);
                $message = "Le cours a été créé avec succès.";
                
                // Redirection pour éviter la resoumission du formulaire
                header("Location: cours.php?message=" . urlencode($message));
                exit();
            } catch (PDOException $e) {
                // Gestion des erreurs SQL
                error_log("Erreur lors de la création du cours : " . $e->getMessage());
                $message = "Une erreur est survenue lors de la création du cours. Veuillez réessayer plus tard.";
            }
        }
    }
    
    // Suppression d'un cours
    if (isset($_POST['supprimer_id'])) {
        $supprimer_id = $_POST['supprimer_id'];

        try {
            // Suppression du cours sélectionné
            $stmt = $pdo->prepare("DELETE FROM cours WHERE id = :id");
            $stmt->execute([':id' => $supprimer_id]);
            $message = "Le cours a été supprimé avec succès.";
            
            // Redirection pour éviter la resoumission du formulaire
            header("Location: cours.php?message=" . urlencode($message));
            exit();
        } catch (PDOException $e) {
            // Gestion des erreurs
            error_log("Erreur lors de la suppression du cours : " . $e->getMessage());
            $message = "Une erreur est survenue lors de la suppression du cours. Veuillez réessayer plus tard.";
        }
    }
}

// Récupération du message depuis l'URL si présent
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

try {
    // Inclusion du fichier de configuration de la base de données
    require_once 'bdd.php';  // Contient les paramètres de connexion à la BDD
    
    // Vérification de l'authentification - Redirection si non connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php'); // Redirige vers la page de connexion
        exit(); // Arrête l'exécution du script
    }

    // Chargement du header approprié selon le rôle de l'utilisateur
    if (isset($_SESSION['user_role'])) {
        switch ($_SESSION['user_role']) {
            case 'admin':
                include "header_admin.php"; // Interface administrateur
                break;
            case 'prof':
                include "header_prof.php"; // Interface professeur
                break;
            default:
                include "header.php"; // Interface standard/élève
                break;
        }
    } else {
        // Redirection en cas d'absence de rôle (sécurité supplémentaire)
        header("Location: login.php");
        exit();
    }
} catch (Exception $e) {
    // Journalisation des erreurs et redirection en cas de problème
    error_log("Erreur dans cours.php : " . $e->getMessage());
    header("Location: login.php");
    exit();
}

// Ajout de la feuille de style CSS spécifique dans le head déjà ouvert
echo '<link href="../css/cours.css" rel="stylesheet" />';
?>

<section>
    <!-- Titre principal de la page -->
    <div class="titre_cours">
        <h1>Gestion des cours</h1>
    </div>

    <div class="page_cours"> 
        <!-- BLOC 1: CRÉATION DE COURS -->
        <div class="blocs_cours">
            <?php
            // Récupération des données pour les listes déroulantes du formulaire
            
            // Récupération des données pour les listes déroulantes du formulaire
            // Liste des professeurs
            $stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
            $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Liste des classes
            $stmt = $pdo->query("SELECT id, name FROM classes");
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Liste des matières
            $stmt = $pdo->query("SELECT id, name FROM matieres");
            $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <!-- Formulaire de création de cours (dans un élément déroulant) -->
            <details>
                <summary>
                    <h4>Créer un cours</h4>
                </summary>
                <form method="POST" action="">
                    <!-- Champ titre avec exemple de format -->
                    <label for="titre">Titre :</label>
                    <input type="text" placeholder="Physique_15/12/2024-09h00-11h00" id="titre" name="titre" required>
                    <br><br>

                    <!-- Description du cours (optionnelle) -->
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                    <br><br>

                    <!-- Date et heure de début du cours -->
                    <label for="date_debut">Date de début :</label>
                    <input type="datetime-local" id="date_debut" name="date_debut" required>
                    <br><br>

                    <!-- Date et heure de fin du cours -->
                    <label for="date_fin">Date de fin :</label>
                    <input type="datetime-local" id="date_fin" name="date_fin" required>
                    <br><br>

                    <!-- Sélection du professeur -->
                    <label for="professeur_id">Professeur :</label>
                    <select id="professeur_id" name="professeur_id" required>
                        <option value="">-- Sélectionner un professeur --</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?php echo $professeur['id']; ?>">
                                <?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur["prenoms"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <!-- Sélection de la classe -->
                    <label for="classes_id">Classe :</label>
                    <select id="classes_id" name="classes_id" required>
                        <option value="">-- Sélectionner une classe --</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?php echo $classe['id']; ?>">
                                <?php echo htmlspecialchars($classe['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <!-- Sélection de la matière -->
                    <label for="matiere_id">Matière :</label>
                    <select id="matiere_id" name="matiere_id" required>
                        <option value="">-- Sélectionner une matière --</option>
                        <?php foreach ($matieres as $matiere): ?>
                            <option value="<?php echo $matiere['id']; ?>">
                                <?php echo htmlspecialchars($matiere['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <!-- Bouton de soumission -->
                    <button type="submit">Créer le cours</button>
                </form>
            </details>
        </div>

        <!-- BLOC 2: MODIFICATION DE COURS -->
        <div class="blocs_cours">
            <details>
                <summary>
                    <h4>Modifier les cours</h4>
                </summary>
                <div class="cours-container">
                    <!-- Formulaire de sélection du cours à modifier -->
                    <form method="GET" action="">
                        <label for="select_cours">Sélectionnez un cours :</label>
                        <select id="select_cours" name="cours_id" onchange="this.form.submit()">
                            <option value="">-- Choisissez un cours --</option>
                            <?php
                            // Récupération de la liste des cours
                            $stmt = $pdo->query("SELECT id, titre FROM cours");

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                                <option value="<?php echo $row['id']; ?>" 
                                        <?php if (isset($_GET['cours_id']) && $_GET['cours_id'] == $row['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['titre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>

                    <?php
                    // Traitement si un cours est sélectionné
                    if (isset($_GET['cours_id']) && !empty($_GET['cours_id'])) {
                        $coursId = $_GET['cours_id'];

                        // Récupération des détails du cours
                        $stmt = $pdo->prepare("SELECT id, titre, description, date_debut, date_fin, 
                                              professeur_id, classes_id, matiere_id FROM cours WHERE id = :id");
                        $stmt->execute([':id' => $coursId]);
                        $cours = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Récupération des données associées (nom du professeur)
                        $prof_stmt = $pdo->prepare("SELECT nom, prenoms FROM users WHERE id = :id");
                        $prof_stmt->execute([':id' => $cours['professeur_id']]);
                        $professeur = $prof_stmt->fetch(PDO::FETCH_ASSOC);

                        // Récupération du nom de la classe
                        $class_stmt = $pdo->prepare("SELECT name FROM classes WHERE id = :id");
                        $class_stmt->execute([':id' => $cours['classes_id']]);
                        $classe = $class_stmt->fetch(PDO::FETCH_ASSOC);

                        // Récupération du nom de la matière
                        $matiere_stmt = $pdo->prepare("SELECT name FROM matieres WHERE id = :id");
                        $matiere_stmt->execute([':id' => $cours['matiere_id']]);
                        $matiere = $matiere_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <!-- Formulaire d'édition du cours -->
                        <form method="POST" action="modifier_cours.php" class="cours-form">
                            <div class="form-group">
                                <label for="titre">Titre :</label>
                                <input type="text" id="titre" name="titre" 
                                       value="<?php echo htmlspecialchars($cours['titre']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description :</label>
                                <textarea id="description" name="description" rows="2"><?php echo htmlspecialchars($cours['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <!-- Conversion du format de date pour l'affichage -->
                                <label for="date_debut">Date de début :</label>
                                <input type="datetime-local" id="date_debut" name="date_debut" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($cours['date_debut'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <!-- Conversion du format de date pour l'affichage -->
                                <label for="date_fin">Date de fin :</label>
                                <input type="datetime-local" id="date_fin" name="date_fin" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($cours['date_fin'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="professeur_id">Professeur :</label>
                                <select id="professeur_id" name="professeur_id" required>
                                    <!-- Option par défaut (professeur actuel) -->
                                    <option value="<?php echo $cours['professeur_id']; ?>">
                                        <?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur['prenoms']); ?>
                                    </option>
                                    <?php
                                    // Liste des autres professeurs disponibles
                                    $prof_stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
                                    while ($prof = $prof_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $prof['id']; ?>">
                                            <?php echo htmlspecialchars($prof['nom']) . " " . htmlspecialchars($prof['prenoms']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="classes_id">Classe :</label>
                                <select id="classes_id" name="classes_id" required>
                                    <!-- Option par défaut (classe actuelle) -->
                                    <option value="<?php echo $cours['classes_id']; ?>">
                                        <?php echo htmlspecialchars($classe['name']); ?>
                                    </option>
                                    <?php
                                    // Liste des autres classes disponibles
                                    $class_stmt = $pdo->query("SELECT id, name FROM classes");
                                    while ($class = $class_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="matiere_id">Matière :</label>
                                <select id="matiere_id" name="matiere_id" required>
                                    <!-- Option par défaut (matière actuelle) -->
                                    <option value="<?php echo $cours['matiere_id']; ?>">
                                        <?php echo htmlspecialchars($matiere['name']); ?>
                                    </option>
                                    <?php
                                    // Liste des autres matières disponibles
                                    $matiere_stmt = $pdo->query("SELECT id, name FROM matieres");
                                    while ($matiere = $matiere_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $matiere['id']; ?>">
                                            <?php echo htmlspecialchars($matiere['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- ID caché pour identifier le cours à modifier -->
                            <input type="hidden" name="id" value="<?php echo $cours['id']; ?>">
                            <button type="submit">Enregistrer</button>
                        </form>
                    <?php } ?>
                </div>
            </details>
        </div>

        <!-- BLOC 3: VISUALISATION DES COURS -->
        <div class="blocs_cours">
            <details>
                <summary>
                    <?php
                        try {
                            // Récupération de la liste de tous les cours
                            $sql = "SELECT titre FROM cours ORDER BY titre";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            // Gestion des erreurs
                            error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <p>
                        <h4>Voir les cours</h4>
                    </p>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
                        // Affichage de la liste des cours ou message si aucun cours
                        if ($namesCount > 0) {
                            foreach ($names as $name) {
                                echo "<li>" . htmlspecialchars($name["titre"]) . "</li>";
                            }
                        } else {
                            echo "<p>Aucun cours trouvé.</p>";
                        }
                        ?>
                    </ul>
                </div>
            </details>
        </div>

        <!-- BLOC 4: SUPPRESSION DE COURS -->
        <div class="blocs_cours">
            <details>
                <summary><h4>Supprimer un cours</h4></summary>

                <?php

                // Récupération de la liste des cours pour le menu déroulant
                $stmt = $pdo->query("SELECT id, titre FROM cours");
                $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <!-- Formulaire de suppression -->
                <form method="POST" action="">
                    <label for="supprimer_id">Sélectionner un cours à supprimer :</label>
                    <select id="supprimer_id" name="supprimer_id" required>
                        <option value="">-- Sélectionner un cours --</option>
                        <?php foreach ($cours as $cours_item): ?>
                            <option value="<?php echo $cours_item['id']; ?>">
                                <?php echo htmlspecialchars($cours_item['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button type="submit">Supprimer le cours</button>
                </form>
            </details>
        </div>
    </div>

    <!-- Zone d'affichage des messages (succès/erreur) -->
    <div class="message">
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>  
</section>

<?php
// Inclusion du pied de page
include "footer.php";
?>