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

// calendar full calendar
<head>
    <link href="../css/matieres.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }
            #calendar {
                max-width: 900px;
                margin: 50px auto;
            }
    </style>
</head>

<section>
    <div class="titre_matieres">
        <h1>
            Emploi du temps
        </h1>
    </div>

    <?php
        // header('Content-Type: application/json');

        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=web_formation', 'root', '');

        // Récupérer les événements
        $query = $pdo->query('SELECT title, start, end, description FROM events');
        $events = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $events[] = [
                'title' => $row['title'],
                'start' => $row['start'],
                'end' => $row['end'],
                'description' => $row['description']
            ];
        }

        // Retourner les événements en JSON
        echo json_encode($events);
    ?>

    <body>
    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek', // Vue hebdomadaire
                initialDate: new Date(), // Date initiale
                locale: 'fr', // Localisation en français
                firstDay: 1, // Lundi comme premier jour de la semaine
                hiddenDays: [0, 6], // Masquer dimanche (0) et samedi (6)
                headerToolbar: {
                    left: 'prev,next today', // Boutons pour naviguer
                    center: 'title', // Titre (ex. "17 - 21 Décembre 2024")
                    right: '' // Pas de vue à changer
                },
                slotMinTime: '08:00:00', // Heure de début (8h)
                slotMaxTime: '18:00:00', // Heure de fin (18h)
                events: [
                    {
                        title: 'Réunion',
                        start: '2024-12-18T10:00:00',
                        end: '2024-12-18T12:00:00',
                        description: 'Réunion avec l\'équipe'
                    },
                    {
                        title: 'Cours PHP',
                        start: '2024-12-19T14:00:00',
                        end: '2024-12-19T16:00:00',
                        description: 'Introduction à PDO'
                    }
                ],
                eventClick: function(info) {
                    alert('Détail de l\'événement : ' + info.event.title + '\n' + info.event.extendedProps.description);
                }
            });
            calendar.render();
        });
    </script>

    </body>
    </html>

    <script src="../js/emploi_du_temps.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</section>

<?php
  include "footer.php";
?>