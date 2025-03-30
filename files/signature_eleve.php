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
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

// Inclusion du header
include "header.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature - Élève</title>
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .signature-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        #signatureCanvas {
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
        }
        .signature-controls {
            margin-top: 10px;
        }
        .signature-request {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="signature-container">
        <h2>Mes Demandes de Signatures</h2>

        <?php
        // Récupérer les demandes de signature en attente pour l'élève
        $sql = "SELECT s.*, c.titre as cours_titre, u.nom as prof_nom, u.prenoms as prof_prenoms 
                FROM sign s
                JOIN cours c ON s.cours_id = c.id
                JOIN users u ON s.professeur_id = u.id
                WHERE s.user_id = ? AND s.statut = 'En attente'
                ORDER BY s.date_signature DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $signatures = $stmt->fetchAll();

        if (count($signatures) > 0):
            foreach ($signatures as $signature):
        ?>
            <div class="signature-request">
                <h4>Cours : <?php echo htmlspecialchars($signature['cours_titre']); ?></h4>
                <p>Professeur : <?php echo htmlspecialchars($signature['prof_nom']) . " " . htmlspecialchars($signature['prof_prenoms']); ?></p>
                <p>Date : <?php echo date('d/m/Y', strtotime($signature['date_signature'])); ?></p>
                
                <canvas id="signatureCanvas_<?php echo $signature['id']; ?>" width="600" height="200"></canvas>
                
                <div class="signature-controls">
                    <button class="btn btn-secondary" onclick="clearSignature(<?php echo $signature['id']; ?>)">Effacer</button>
                    <button class="btn btn-primary" onclick="saveSignature(<?php echo $signature['id']; ?>)">Signer</button>
                </div>
            </div>
        <?php
            endforeach;
        else:
            echo "<p>Aucune signature en attente.</p>";
        endif;
        ?>
    </div>

    <script>
    const canvases = {};
    const contexts = {};

    // Initialiser tous les canvas
    document.querySelectorAll('canvas[id^="signatureCanvas_"]').forEach(canvas => {
        const id = canvas.id.split('_')[1];
        canvases[id] = canvas;
        contexts[id] = canvas.getContext('2d');
        
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Configuration du contexte
        contexts[id].strokeStyle = '#000';
        contexts[id].lineWidth = 2;
        contexts[id].lineCap = 'round';
        contexts[id].lineJoin = 'round';

        // Événements de dessin
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Événements tactiles
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);

        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = getCoordinates(e);
        }

        function draw(e) {
            if (!isDrawing) return;
            
            e.preventDefault();
            const [x, y] = getCoordinates(e);
            
            contexts[id].beginPath();
            contexts[id].moveTo(lastX, lastY);
            contexts[id].lineTo(x, y);
            contexts[id].stroke();
            
            [lastX, lastY] = [x, y];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 'mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            return [
                e.clientX - rect.left,
                e.clientY - rect.top
            ];
        }
    });

    function clearSignature(id) {
        const canvas = canvases[id];
        const context = contexts[id];
        context.clearRect(0, 0, canvas.width, canvas.height);
    }

    function saveSignature(id) {
        const canvas = canvases[id];
        const imageData = canvas.toDataURL('image/png');
        
        fetch('sauvegarde_signature.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                signature_id: id,
                image_data: imageData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Signature enregistrée avec succès !');
                // Recharger la page pour mettre à jour la liste
                window.location.reload();
            } else {
                alert('Erreur lors de l\'enregistrement de la signature : ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur lors de l\'enregistrement de la signature');
            console.error('Error:', error);
        });
    }
    </script>
</body>
</html>

<?php
  include "footer.php";
?>