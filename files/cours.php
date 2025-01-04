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
$message="";
?>

<head>
    <link href="../css/cours.css" rel="stylesheet" />
</head>

<section>
    <div class="titre_cours">
        <h1>
            Gestion des cours
        </h1>
    </div>

    <div class="page_cours"> 
        <div class="blocs_cours"> <!-- Créer les cours -->
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $titre = $_POST['titre'];
                $description = $_POST['description'];
                $date_debut = $_POST['date_debut'];
                $date_fin = $_POST['date_fin'];
                $professeur_id = $_POST['professeur_id'];

                // Vérifier si un cours avec ce titre existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE titre = :titre");
                $stmt->execute([':titre' => $titre]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $message = "Un cours avec ce titre existe déjà. Veuillez en choisir un autre.";
                } else {
                    // Insérer les données dans la table "cours"
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO cours (titre, description, date_debut, date_fin, professeur_id)
                            VALUES (:titre, :description, :date_debut, :date_fin, :professeur_id)
                        ");
                        $stmt->execute([
                            ':titre' => $titre,
                            ':description' => $description,
                            ':date_debut' => $date_debut,
                            ':date_fin' => $date_fin,
                            ':professeur_id' => $professeur_id
                        ]);
                        $message = "Le cours a été créé avec succès.";
                    } catch (PDOException $e) {
                        $message = "Erreur lors de la création du cours : " . $e->getMessage();
                    }
                }
            }

            // Récupérer les professeurs
            $stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
            $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <details>
                <summary>
                    <h4>Créer un cours</h4>
                </summary>
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
                                <?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur["prenoms"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <button type="submit">Créer le cours</button>
                </form>
            </details>
        </div>

        <div class="blocs_cours"> <!-- Modifer les cours -->
            <details>
                <summary><h4>Modifier un cours</h4></summary>

                <?php
                // Vérifier si un ID de cours est passé via POST pour modification
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_id'])) {
                    $modifier_id = $_POST['modifier_id'];
                    $nouveau_titre = $_POST['nouveau_titre'];
                    $nouvelle_description = $_POST['nouvelle_description'];
                    $nouvelle_date_debut = $_POST['nouvelle_date_debut'];
                    $nouvelle_date_fin = $_POST['nouvelle_date_fin'];
                    $nouveau_professeur_id = $_POST['nouveau_professeur_id'];

                    try {
                        // Requête pour modifier les données du cours
                        $stmt = $pdo->prepare("
                            UPDATE cours SET 
                                titre = :titre,
                                description = :description,
                                date_debut = :date_debut,
                                date_fin = :date_fin,
                                professeur_id = :professeur_id
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            ':id' => $modifier_id,
                            ':titre' => $nouveau_titre,
                            ':description' => $nouvelle_description,
                            ':date_debut' => $nouvelle_date_debut,
                            ':date_fin' => $nouvelle_date_fin,
                            ':professeur_id' => $nouveau_professeur_id
                        ]);
                        $message = "Le cours a été modifié avec succès.";
                    } catch (PDOException $e) {
                        $message = "Erreur lors de la modification du cours : " . $e->getMessage();
                    }
                }

                // Récupérer les cours pour la modification
                $stmt = $pdo->query("SELECT id, titre FROM cours");
                $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Récupérer les professeurs
                $stmt = $pdo->query("SELECT id, nom, prenoms FROM users WHERE roles = 'prof'");
                $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <form method="POST" action="">
                    <label for="modifier_id">Sélectionner un cours à modifier :</label>
                    <select id="modifier_id" name="modifier_id" required>
                        <option value="">-- Sélectionner un cours --</option>
                        <?php foreach ($cours as $cours_item): ?>
                            <option value="<?php echo $cours_item['id']; ?>">
                                <?php echo htmlspecialchars($cours_item['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <!-- Champs pour modifier le cours -->
                    <label for="nouveau_titre">Nouveau titre :</label>
                    <input type="text" id="nouveau_titre" name="nouveau_titre" required>
                    <br><br>

                    <label for="nouvelle_description">Nouvelle description :</label>
                    <textarea id="nouvelle_description" name="nouvelle_description" rows="4"></textarea>
                    <br><br>

                    <label for="nouvelle_date_debut">Nouvelle date de début :</label>
                    <input type="datetime-local" id="nouvelle_date_debut" name="nouvelle_date_debut" required>
                    <br><br>

                    <label for="nouvelle_date_fin">Nouvelle date de fin :</label>
                    <input type="datetime-local" id="nouvelle_date_fin" name="nouvelle_date_fin" required>
                    <br><br>

                    <label for="nouveau_professeur_id">Professeur :</label>
                    <select id="nouveau_professeur_id" name="nouveau_professeur_id" required>
                        <option value="">-- Sélectionner un professeur --</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?php echo $professeur['id']; ?>">
                                <?php echo htmlspecialchars($professeur['nom']) . " " . htmlspecialchars($professeur["prenoms"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <button type="submit">Modifier le cours</button>
                </form>
            </details>
        </div>

        <div class="blocs_cours"> <!-- Voir les cours -->
            <details>
                <summary>
                    <?php
                        try {
                            // Récupérer les cours
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
                    <p>
                        <h4>Voir les cours</h4>
                    </p>
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
                // Vérifier si un ID de cours est passé via POST pour suppression
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_id'])) {
                    $supprimer_id = $_POST['supprimer_id'];

                    try {
                        // Requête pour supprimer le cours avec l'ID correspondant
                        $stmt = $pdo->prepare("DELETE FROM cours WHERE id = :id");
                        $stmt->execute([':id' => $supprimer_id]);
                        $message = "Le cours a été supprimé avec succès.";
                    } catch (PDOException $e) {
                        $message = "Erreur lors de la suppression du cours : " . $e->getMessage();
                    }
                }

                // Récupérer les cours à supprimer
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
        <!-- Afficher les erreurs ici -->
        <?php if (isset($message) && $message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>  
</section>

<?php
  include "footer.php";
?>