<?php
session_start();

try {
    require_once 'bdd.php';  // Inclut la configuration et la connexion BDD

    // Vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Inclut le header approprié en fonction du rôle
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
        header("Location: login.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Erreur dans cours : " . $e->getMessage());
    header("Location: login.php");
    exit();
}

$message = "";
?>

<head>
    <link href="../css/cours.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_cours">
        <h1>Gestion des cours</h1>
    </div>

    <div class="page_cours"> 
        <div class="blocs_cours"> <!-- Créer les cours -->
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Pour la création d'un cours, on attend que tous les champs soient envoyés
                if (isset($_POST['titre'], $_POST['description'], $_POST['date_debut'], $_POST['date_fin'], $_POST['professeur_id'], $_POST['class_id'], $_POST['matiere_id'])) {
                    // Récupérer les données du formulaire
                    $titre = $_POST['titre'];
                    $description = $_POST['description'];
                    $date_debut = $_POST['date_debut'];
                    $date_fin = $_POST['date_fin'];
                    $professeur_id = $_POST['professeur_id'];
                    $class_id = $_POST['class_id'];
                    $matiere_id = $_POST['matiere_id'];

                    // Vérifier si un cours avec ce titre existe déjà
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE titre = :titre");
                    $stmt->execute([':titre' => $titre]);
                    $count = $stmt->fetchColumn();

                    if ($count > 0) {
                        $message = "Un cours avec ce titre existe déjà. Veuillez en choisir un autre.";
                    } else {
                        // Insérer les données dans la table \"cours\"
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO cours (titre, description, date_debut, date_fin, professeur_id, class_id, matiere_id)
                                VALUES (:titre, :description, :date_debut, :date_fin, :professeur_id, :class_id, :matiere_id)
                            ");
                            $stmt->execute([
                                ':titre' => $titre,
                                ':description' => $description,
                                ':date_debut' => $date_debut,
                                ':date_fin' => $date_fin,
                                ':professeur_id' => $professeur_id,
                                ':class_id' => $class_id,
                                ':matiere_id' => $matiere_id
                            ]);
                            $message = "Le cours a été créé avec succès.";
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la création du cours : " . $e->getMessage());
                            $message = "Une erreur est survenue lors de la création du cours. Veuillez réessayer plus tard.";
                        }
                    }
                } else {
                    $message = "Certains champs sont manquants.";
                }
            }

            // Récupérer les professeurs
            $stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
            $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les classes
            $stmt = $pdo->query("SELECT id, name FROM class");
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les matières
            $stmt = $pdo->query("SELECT id, nom FROM matieres");
            $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <details>
                <summary><h4>Créer un cours</h4></summary>
                <form method="POST" action="">
                    <label for="titre">Titre :</label>
                    <input type="text" placeholder="Physique_15/12/2024-09h00-11h00" id="titre" name="titre" required>
                    <br><br>

                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                    <br><br>

                    <label for="date_debut">Date de début :</label>
                    <input type="datetime-local" id="date_debut" name="date_debut" required>
                    <br><br>

                    <label for="date_fin">Date de fin :</label>
                    <input type="datetime-local" id="date_fin" name="date_fin" required>
                    <br><br>

                    <label for="professeur_id">Professeur :</label>
                    <select id="professeur_id" name="professeur_id" required>
                        <option value="">-- Sélectionner un professeur --</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?php echo $professeur['id']; ?>">
                                <?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur['prenoms']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <label for="class_id">Classe :</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">-- Sélectionner une classe --</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?php echo $classe['id']; ?>">
                                <?php echo htmlspecialchars($classe['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <label for="matiere_id">Matière :</label>
                    <select id="matiere_id" name="matiere_id" required>
                        <option value="">-- Sélectionner une matière --</option>
                        <?php foreach ($matieres as $matiere): ?>
                            <option value="<?php echo $matiere['id']; ?>">
                                <?php echo htmlspecialchars($matiere['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <button type="submit">Créer le cours</button>
                </form>
            </details>
        </div>

        <div class="blocs_cours"> <!-- Modifier les cours -->
            <details>
                <summary><h4>Modifier les cours</h4></summary>
                <div class="cours-container">
                    <form method="GET" action="">
                        <label for="select_cours">Sélectionnez un cours :</label>
                        <select id="select_cours" name="cours_id" onchange="this.form.submit()">
                            <option value="">-- Choisissez un cours --</option>
                            <?php
                            // Récupérer les cours
                            $stmt = $pdo->query("SELECT id, titre FROM cours");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                                <option value="<?php echo $row['id']; ?>" <?php if (isset($_GET['cours_id']) && $_GET['cours_id'] == $row['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['titre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>

                    <?php
                    // Si un cours est sélectionné, on récupère ses détails
                    if (isset($_GET['cours_id']) && !empty($_GET['cours_id'])) {
                        $coursId = $_GET['cours_id'];

                        // Récupérer les détails du cours sélectionné
                        $stmt = $pdo->prepare("SELECT id, titre, description, date_debut, date_fin, professeur_id, class_id, matiere_id FROM cours WHERE id = :id");
                        $stmt->execute([':id' => $coursId]);
                        $cours = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Récupérer le nom du professeur
                        $prof_stmt = $pdo->prepare("SELECT nom, prenoms FROM users WHERE id = :id");
                        $prof_stmt->execute([':id' => $cours['professeur_id']]);
                        $professeur = $prof_stmt->fetch(PDO::FETCH_ASSOC);

                        // Récupérer le nom de la classe
                        $class_stmt = $pdo->prepare("SELECT name FROM class WHERE id = :id");
                        $class_stmt->execute([':id' => $cours['class_id']]);
                        $classe = $class_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Récupérer le nom de la matière
                        $matiere_stmt = $pdo->prepare("SELECT nom FROM matieres WHERE id = :id");
                        $matiere_stmt->execute([':id' => $cours['matiere_id']]);
                        $matiere = $matiere_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <form method="POST" action="modifier_cours.php" class="cours-form">
                            <div class="form-group">
                                <label for="titre">Titre :</label>
                                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($cours['titre']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description :</label>
                                <textarea id="description" name="description" rows="2"><?php echo htmlspecialchars($cours['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="date_debut">Date de début :</label>
                                <input type="datetime-local" id="date_debut" name="date_debut" value="<?php echo date('Y-m-d\\TH:i', strtotime($cours['date_debut'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="date_fin">Date de fin :</label>
                                <input type="datetime-local" id="date_fin" name="date_fin" value="<?php echo date('Y-m-d\\TH:i', strtotime($cours['date_fin'])); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="professeur_id">Professeur :</label>
                                <select id="professeur_id" name="professeur_id" required>
                                    <option value="<?php echo $cours['professeur_id']; ?>"><?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur['prenoms']); ?></option>
                                    <?php
                                    // Récupérer tous les professeurs
                                    $prof_stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
                                    while ($prof = $prof_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $prof['id']; ?>"><?php echo htmlspecialchars($prof['nom']) . " " . htmlspecialchars($prof['prenoms']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="class_id">Classe :</label>
                                <select id="class_id" name="class_id" required>
                                    <option value="<?php echo $cours['class_id']; ?>"><?php echo htmlspecialchars($classe['name']); ?></option>
                                    <?php
                                    // Récupérer toutes les classes
                                    $class_stmt = $pdo->query("SELECT id, name FROM class");
                                    while ($class = $class_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="matiere_id">Matière :</label>
                                <select id="matiere_id" name="matiere_id" required>
                                    <option value="<?php echo $cours['matiere_id']; ?>"><?php echo htmlspecialchars($matiere['nom']); ?></option>
                                    <?php
                                    // Récupérer toutes les matières
                                    $matiere_stmt = $pdo->query("SELECT id, nom FROM matieres");
                                    while ($m = $matiere_stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nom']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <input type="hidden" name="id" value="<?php echo $cours['id']; ?>">
                            <button type="submit">Enregistrer</button>
                        </form>
                    <?php } ?>
                </div>
            </details>
        </div>

        <div class="blocs_cours"> <!-- Voir les cours -->
            <details>
                <summary>
                    <?php
                        try {
                            $sql = "SELECT titre FROM cours ORDER BY titre";
                            $stmt = $pdo->query($sql);
                            $names = $stmt->fetchAll();
                            $namesCount = count($names);
                        } catch (PDOException $e) {
                            error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
                            $names = [];
                            $namesCount = 0;
                        }
                    ?>
                    <h4>Voir les cours</h4>
                </summary>
                <div class="liste_statistiques">
                    <ul>
                        <?php
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

        <div class="blocs_cours"> <!-- Supprimer les cours -->
            <details>
                <summary><h4>Supprimer un cours</h4></summary>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_id'])) {
                    $supprimer_id = $_POST['supprimer_id'];

                    try {
                        $stmt = $pdo->prepare("DELETE FROM cours WHERE id = :id");
                        $stmt->execute([':id' => $supprimer_id]);
                        $message = "Le cours a été supprimé avec succès.";
                    } catch (PDOException $e) {
                        error_log("Erreur lors de la suppression du cours : " . $e->getMessage());
                        $message = "Une erreur est survenue lors de la suppression du cours. Veuillez réessayer plus tard.";
                    }
                }

                $stmt = $pdo->query("SELECT id, titre FROM cours");
                $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

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

    <div class="message">
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
include "footer.php";
?>
