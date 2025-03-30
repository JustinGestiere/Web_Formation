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
?>

<canvas id="signature-pad" width="400" height="200" style="border:1px solid #000;"></canvas>
<button onclick="clearCanvas()">Effacer</button>
<button onclick="saveSignature()">Signer</button>

<script>
let canvas = document.getElementById("signature-pad");
let ctx = canvas.getContext("2d");
let drawing = false;

canvas.addEventListener("mousedown", () => drawing = true);
canvas.addEventListener("mouseup", () => drawing = false);
canvas.addEventListener("mousemove", draw);

function draw(e) {
    if (!drawing) return;
    ctx.lineTo(e.clientX - canvas.offsetLeft, e.clientY - canvas.offsetTop);
    ctx.stroke();
}

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveSignature() {
    let signature = canvas.toDataURL("image/png");
    document.getElementById("signatureInput").value = signature;
    document.getElementById("signatureForm").submit();
}
</script>

<form id="signatureForm" action="sauvegarde_signature.php" method="POST">
    <input type="hidden" name="signature" id="signatureInput">
    <input type="hidden" name="sign_id" value="ID_DE_LA_SIGNATURE">
</form>


<?php
  include "footer.php";
?>