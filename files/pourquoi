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
        // Si l'utilisateur n'est pas connecté, on peut rediriger vers login
        header("Location: login.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Erreur dans cours.php : " . $e->getMessage());
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
        <h1>
            Gestion des cours
        </h1>
    </div>

    <div class="page_cours"> 
        <div class="blocs_cours"> <!-- Créer les cours -->
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                        // Insérer les données dans la table "cours"
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
            $stmt = $pdo->query("SELECT id, name FROM class"); // Assurez-vous que la table "class" existe
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les matieres
            $stmt = $pdo->query("SELECT id, name FROM matieres"); // Assurez-vous que la table "matieres" existe
            $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                <?php echo htmlspecialchars($matiere['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>

                    <button type="submit">Créer le cours</button>
                </form>
            </details>
        </div>
    </div>
</section>
