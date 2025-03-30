<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification de la connexion et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: login.php");
    exit();
}

// Inclusion du header
include "header_prof.php";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $professeur_id = $_SESSION['user_id'];
    $classe_id = $_POST['classe_id'];
    $cours_id = $_POST['cours_id'];
    $date_presence = date("Y-m-d");
    
    $eleves_presents = isset($_POST['presences']) ? $_POST['presences'] : [];

    foreach ($eleves_presents as $eleve_id) {
        // Vérifier si une demande existe déjà
        $sql_check = "SELECT id FROM sign WHERE user_id = ? AND cours_id = ? AND date_signature = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$eleve_id, $cours_id, $date_presence]);
        
        if (!$stmt_check->fetch()) {
            // Ajouter la demande de signature si elle n'existe pas
            $sql_insert = "INSERT INTO sign (user_id, cours_id, date_signature, file_name, statut, professeur_id) 
                          VALUES (?, ?, ?, NULL, 'En attente', ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$eleve_id, $cours_id, $date_presence, $professeur_id]);
        }
    }
    
    echo "<div class='alert alert-success'>Demandes de signatures envoyées avec succès !</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Signatures - Professeur</title>
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .signature-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .eleves-list {
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .signature-status {
            margin-top: 30px;
        }
        .status-pending {
            color: orange;
        }
        .status-signed {
            color: green;
        }
    </style>
</head>
<body>
    <div class="signature-form">
        <h2>Demander des Signatures</h2>
        
        <!-- Formulaire de sélection -->
        <form method="POST" action="">
            <!-- Sélection de la classe -->
            <div class="form-group">
                <label for="classe_id">Sélectionnez une classe :</label>
                <select name="classe_id" id="classe_id" class="form-control" required>
                    <option value="">-- Choisir une classe --</option>
                    <?php
                    // Récupérer les classes du professeur
                    $sql_classes = "SELECT DISTINCT c.id, c.name 
                                  FROM classes c 
                                  INNER JOIN cours co ON c.id = co.class_id 
                                  WHERE co.professeur_id = ?
                                  ORDER BY c.name";
                    $stmt_classes = $pdo->prepare($sql_classes);
                    $stmt_classes->execute([$_SESSION['user_id']]);
                    while ($classe = $stmt_classes->fetch()) {
                        echo "<option value='" . $classe['id'] . "'>" . htmlspecialchars($classe['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Sélection du cours -->
            <div class="form-group">
                <label for="cours_id">Sélectionnez un cours :</label>
                <select name="cours_id" id="cours_id" class="form-control" required>
                    <option value="">-- Choisir un cours --</option>
                </select>
            </div>

            <!-- Liste des élèves (sera remplie par AJAX) -->
            <div class="eleves-list" id="eleves-list">
                <!-- Les élèves seront affichés ici -->
            </div>

            <button type="submit" class="btn btn-primary">Demander les signatures</button>
        </form>

        <!-- Affichage des signatures en attente -->
        <div class="signature-status">
            <h3>Statut des Signatures</h3>
            <?php
            $sql_status = "SELECT s.*, u.nom, u.prenoms, c.titre as cours_titre
                          FROM sign s
                          JOIN users u ON s.user_id = u.id
                          JOIN cours c ON s.cours_id = c.id
                          WHERE s.professeur_id = ?
                          AND s.date_signature = CURRENT_DATE
                          ORDER BY s.statut DESC, u.nom, u.prenoms";
            $stmt_status = $pdo->prepare($sql_status);
            $stmt_status->execute([$_SESSION['user_id']]);
            
            if ($stmt_status->rowCount() > 0) {
                echo "<ul class='list-group'>";
                while ($signature = $stmt_status->fetch()) {
                    $status_class = $signature['statut'] == 'En attente' ? 'status-pending' : 'status-signed';
                    echo "<li class='list-group-item'>";
                    echo htmlspecialchars($signature['nom']) . " " . htmlspecialchars($signature['prenoms']);
                    echo " - " . htmlspecialchars($signature['cours_titre']);
                    echo " <span class='" . $status_class . "'>[" . htmlspecialchars($signature['statut']) . "]</span>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Aucune signature en attente pour aujourd'hui.</p>";
            }
            ?>
        </div>
    </div>

    <script>
    document.getElementById('classe_id').addEventListener('change', function() {
        const classeId = this.value;
        const coursSelect = document.getElementById('cours_id');
        const elevesDiv = document.getElementById('eleves-list');
        
        // Vider les sélections précédentes
        coursSelect.innerHTML = '<option value="">-- Choisir un cours --</option>';
        elevesDiv.innerHTML = '';
        
        if (classeId) {
            // Charger les cours de la classe
            fetch(`get_cours.php?classe_id=${classeId}`)
                .then(response => response.json())
                .then(cours => {
                    cours.forEach(c => {
                        coursSelect.innerHTML += `<option value="${c.id}">${c.titre}</option>`;
                    });
                });
        }
    });

    document.getElementById('cours_id').addEventListener('change', function() {
        const classeId = document.getElementById('classe_id').value;
        const elevesDiv = document.getElementById('eleves-list');
        
        if (classeId) {
            // Charger les élèves de la classe
            fetch(`get_eleves.php?classe_id=${classeId}`)
                .then(response => response.json())
                .then(eleves => {
                    elevesDiv.innerHTML = '<div class="form-group">';
                    eleves.forEach(eleve => {
                        elevesDiv.innerHTML += `
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="presences[]" 
                                       value="${eleve.id}" id="eleve_${eleve.id}">
                                <label class="form-check-label" for="eleve_${eleve.id}">
                                    ${eleve.nom} ${eleve.prenoms}
                                </label>
                            </div>`;
                    });
                    elevesDiv.innerHTML += '</div>';
                });
        }
    });
    </script>
</body>
</html>