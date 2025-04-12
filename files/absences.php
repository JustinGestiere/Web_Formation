<?php
/**
 * Page de gestion des absences/présences
 * Permet aux professeurs et élèves de consulter les absences et présences
 */

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification des droits d'accès
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['prof', 'eleve'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', 'AulrrpTCD7Tk2nJ55H4v');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur BDD (absences): " . $e->getMessage());
    exit("Erreur de connexion à la base de données.");
}

// Récupération des informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Variables pour le filtrage
$selectedClasse = isset($_GET['classe']) ? intval($_GET['classe']) : 0;
$selectedCours = isset($_GET['cours']) ? intval($_GET['cours']) : 0;
$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';

// Inclure le header approprié selon le rôle
if ($user_role === 'prof') {
    include 'header_prof.php';
} else {
    include 'header_eleve.php';
}
?>

<div class="container mt-4">
    <h1 class="mb-4">Gestion des Absences et Présences</h1>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Filtrer les résultats</h2>
        </div>
        <div class="card-body">
            <form method="get" action="absences.php" class="row g-3">
                <?php if ($user_role === 'prof'): ?>
                <!-- Les professeurs peuvent filtrer par classe -->
                <div class="col-md-4">
                    <label for="classe" class="form-label">Classe</label>
                    <select name="classe" id="classe" class="form-control">
                        <option value="0">Toutes les classes</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, name FROM classes ORDER BY name");
                        while ($row = $stmt->fetch()) {
                            $selected = ($selectedClasse == $row['id']) ? 'selected' : '';
                            echo "<option value=\"{$row['id']}\" $selected>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <label for="cours" class="form-label">Cours</label>
                    <select name="cours" id="cours" class="form-control">
                        <option value="0">Tous les cours</option>
                        <?php
                        if ($user_role === 'prof') {
                            // Les professeurs voient leurs cours
                            $stmt = $pdo->prepare("SELECT c.id, c.titre 
                                                 FROM cours c 
                                                 WHERE c.professeur_id = :prof_id
                                                 " . ($selectedClasse > 0 ? " AND c.classes_id = :classe_id" : "") . "
                                                 ORDER BY c.titre");
                            $stmt->bindParam(':prof_id', $user_id);
                            if ($selectedClasse > 0) {
                                $stmt->bindParam(':classe_id', $selectedClasse);
                            }
                        } else {
                            // Les élèves voient les cours de leur classe
                            $stmt = $pdo->prepare("SELECT c.id, c.titre 
                                                 FROM cours c 
                                                 JOIN users u ON u.classe_id = c.classes_id
                                                 WHERE u.id = :user_id
                                                 ORDER BY c.titre");
                            $stmt->bindParam(':user_id', $user_id);
                        }
                        
                        $stmt->execute();
                        while ($row = $stmt->fetch()) {
                            $selected = ($selectedCours == $row['id']) ? 'selected' : '';
                            echo "<option value=\"{$row['id']}\" $selected>{$row['titre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?php echo $selectedDate; ?>">
                </div>
                
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="absences.php" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Liste des Présences/Absences</h2>
        </div>
        <div class="card-body">
            <?php
            // Construction de la requête selon le rôle et les filtres
            if ($user_role === 'prof') {
                // Requête pour les professeurs
                $query = "SELECT s.id, u.nom, u.prenoms, c.titre as cours_titre, cl.name as classe_nom, 
                          s.date_signature, s.signed, s.statut
                          FROM sign s
                          JOIN users u ON s.user_id = u.id
                          JOIN cours c ON s.cours_id = c.id
                          JOIN classes cl ON s.classe_id = cl.id
                          WHERE s.professeur_id = :user_id";
                
                // Ajout des filtres
                if ($selectedClasse > 0) {
                    $query .= " AND s.classe_id = :classe_id";
                }
                if ($selectedCours > 0) {
                    $query .= " AND s.cours_id = :cours_id";
                }
                if (!empty($selectedDate)) {
                    $query .= " AND DATE(s.date_signature) = :date_signature";
                }
                
                $query .= " ORDER BY s.date_signature DESC, cl.name, u.nom, u.prenoms";
                
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                
                if ($selectedClasse > 0) {
                    $stmt->bindParam(':classe_id', $selectedClasse);
                }
                if ($selectedCours > 0) {
                    $stmt->bindParam(':cours_id', $selectedCours);
                }
                if (!empty($selectedDate)) {
                    $stmt->bindParam(':date_signature', $selectedDate);
                }
            } else {
                // Requête pour les élèves (ils ne voient que leurs propres présences/absences)
                $query = "SELECT s.id, c.titre as cours_titre, cl.name as classe_nom, 
                          s.date_signature, s.signed, s.statut, 
                          u.nom as prof_nom, u.prenoms as prof_prenom
                          FROM sign s
                          JOIN cours c ON s.cours_id = c.id
                          JOIN classes cl ON s.classe_id = cl.id
                          JOIN users u ON s.professeur_id = u.id
                          WHERE s.user_id = :user_id";
                
                // Ajout des filtres
                if ($selectedCours > 0) {
                    $query .= " AND s.cours_id = :cours_id";
                }
                if (!empty($selectedDate)) {
                    $query .= " AND DATE(s.date_signature) = :date_signature";
                }
                
                $query .= " ORDER BY s.date_signature DESC, c.titre";
                
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                
                if ($selectedCours > 0) {
                    $stmt->bindParam(':cours_id', $selectedCours);
                }
                if (!empty($selectedDate)) {
                    $stmt->bindParam(':date_signature', $selectedDate);
                }
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($results) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <?php if ($user_role === 'prof'): ?>
                                <th>Élève</th>
                            <?php else: ?>
                                <th>Professeur</th>
                            <?php endif; ?>
                            <th>Cours</th>
                            <th>Classe</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <?php if ($user_role === 'prof'): ?>
                                    <td><?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenoms']); ?></td>
                                <?php else: ?>
                                    <td><?php echo htmlspecialchars($row['prof_nom'] . ' ' . $row['prof_prenom']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($row['cours_titre']); ?></td>
                                <td><?php echo htmlspecialchars($row['classe_nom']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['date_signature'])); ?></td>
                                <td>
                                    <?php 
                                    if ($row['signed'] == 1) {
                                        echo '<span class="badge bg-success text-white">Présent</span>';
                                    } else {
                                        if ($row['statut'] == 'En attente') {
                                            echo '<span class="badge bg-warning text-dark">En attente</span>';
                                        } else {
                                            echo '<span class="badge bg-danger text-white">Absent</span>';
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucune donnée de présence/absence trouvée avec les filtres actuels.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts de Bootstrap (si non inclus dans le header) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
