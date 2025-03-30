<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de la connexion et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'eleve') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    require_once "bdd.php";
} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Inclusion du header
include "header.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatures - Élève</title>
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
        .signature-request {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        canvas {
            border: 1px solid #ccc;
            margin: 10px 0;
        }
        .btn-group {
            margin-top: 10px;
        }
        .btn {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="signature-container">
        <h2>Mes Signatures en Attente</h2>

        <?php
        // Récupérer les demandes de signature en attente pour l'élève
        $sql = "SELECT s.*, edt.title as cours_titre, u.nom as prof_nom, u.prenoms as prof_prenoms 
                FROM sign s
                JOIN emploi_du_temps edt ON s.emploi_du_temps_id = edt.id
                JOIN users u ON s.professeur_id = u.id
                WHERE s.user_id = ? AND s.statut = 'En attente'
                ORDER BY edt.start_datetime DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $signatures = $stmt->fetchAll();

        if (count($signatures) > 0):
            foreach ($signatures as $signature):
        ?>
            <div class="signature-request">
                <h4>Cours : <?php echo htmlspecialchars($signature['cours_titre']); ?></h4>
                <p>Professeur : <?php echo htmlspecialchars($signature['prof_nom']) . " " . htmlspecialchars($signature['prof_prenoms']); ?></p>
                
                <canvas id="signatureCanvas_<?php echo $signature['id']; ?>" width="600" height="200"></canvas>
                
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="clearCanvas(<?php echo $signature['id']; ?>)">Effacer</button>
                    <button class="btn btn-primary" onclick="saveSignature(<?php echo $signature['id']; ?>)">Signer</button>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <p>Aucune signature en attente.</p>
        <?php endif; ?>
    </div>

    <script>
    // Initialisation des canvas de signature
    document.querySelectorAll('canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let lastX = 0;
        let lastY = 0;

        // Configuration du style
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';

        // Événements tactiles
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // Événements souris
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        function startDrawing(e) {
            drawing = true;
            const pos = getPosition(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();

            const pos = getPosition(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDrawing() {
            drawing = false;
        }

        function getPosition(e) {
            let rect = canvas.getBoundingClientRect();
            let x, y;

            if (e.type.includes('touch')) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            return { x, y };
        }
    });

    function clearCanvas(signatureId) {
        const canvas = document.getElementById('signatureCanvas_' + signatureId);
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function saveSignature(signatureId) {
        const canvas = document.getElementById('signatureCanvas_' + signatureId);
        
        // Vérifier si la signature est vide
        const ctx = canvas.getContext('2d');
        const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        if (!pixels.some(pixel => pixel !== 0)) {
            alert('Veuillez signer avant de soumettre.');
            return;
        }

        // Envoyer la signature
        fetch('sauvegarde_signature.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                signature_id: signatureId,
                image_data: canvas.toDataURL('image/png')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Signature enregistrée avec succès !');
                // Recharger la page pour mettre à jour la liste
                location.reload();
            } else {
                alert('Erreur : ' + (data.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'enregistrement de la signature');
        });
    }
    </script>
</body>
</html>

<?php
  include "footer.php";
?>