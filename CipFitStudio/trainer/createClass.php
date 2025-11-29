<?php
session_start();
require_once "../models/FitnessClass.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "trainer") {
    die("Acces interzis.");
}

$trainer_id = $_SESSION['user_id'];
$errorMessage = '';

//procesare formular
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => ''];
    
    try {
        $fitnessClass = new FitnessClass([
            'trainer_id' => $trainer_id,
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'DATE' => $_POST['date'] ?? '',
            'TIME' => $_POST['time'] ?? '',
            'duration' => $_POST['duration'] ?? 0,
            'max_clients' => $_POST['max_clients'] ?? 0,
            'location' => $_POST['location'] ?? ''
        ]);

        $fitnessClass->create();
        
        $response['success'] = true;
        $response['message'] = 'Clasa a fost creată cu succes!';
        $response['redirect'] = 'viewClasses.php';
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Class</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">

    <style>
        body {
            background-image: url('../imagini/dashboardBG.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen relative py-20">

    <div class="absolute inset-0 backdrop-blur-sm"></div>

    <div class="absolute top-0 left-0 w-full flex justify-between items-center px-10 py-6 z-10">

        <a href="trainerDashboard.php"
            class="text-white text-xl font-extrabold drop-shadow-[0_0_5px_black] hover:scale-110 transition flex items-center gap-2 cursor-pointer ">

            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 850 1000" fill="currentColor">
                <g>
                    <path d="M750 310c26.667 0 50 9.667 70 29c20 19.333 30 43 30 71c0 0 0 290 0 290c0 26.667 -10 50 -30 70c-20 20 -43.333 30 -70 30c0 0 -690 0 -690 0c0 0 0 -140 0 -140c0 0 650 0 650 0c0 0 0 -210 0 -210c0 0 -500 0 -500 0c0 0 0 110 0 110c0 0 -210 -180 -210 -180c0 0 210 -180 210 -180c0 0 0 110 0 110c0 0 540 0 540 0c0 0 0 0 0 0" />
                </g>
            </svg>

            Înapoi la Dashboard
        </a>

    </div>

    <!-- Main content -->
    <div class="relative z-10 flex flex-col items-center min-h-screen pt-32 px-4">
        
        <h2 class="text-2xl font-extrabold text-white mb-6 drop-shadow-[0_0_5px_black] text-center">
            Creează o nouă clasă
        </h2>

        <form id="createClassForm" method="POST" class="bg-white/20 backdrop-blur-lg p-8 rounded-2xl shadow-xl w-full max-w-3xl border border-white/30 space-y-6">

            <div>
                <label class="text-white font-bold">Titlu:</label>
                <input type="text" name="title"
                    class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
            </div>

            <div>
                <label class="text-white font-bold">Descriere:</label>
                <textarea name="description" rows="4"
                    class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label class="text-white font-bold">Data:</label>
                    <input type="date" name="date"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Ora:</label>
                    <input type="time" name="time"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Durată (minute):</label>
                    <input type="number" name="duration"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Max clienți:</label>
                    <input type="number" name="max_clients"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

            </div>

            <div>
                <label class="text-white font-bold">Locație:</label>
                <input type="text" name="location"
                       class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
            </div>

            <button type="submit"
                    class="w-full bg-red-600 text-white text-lg font-extrabold py-3 rounded-xl shadow-lg hover:scale-105 transition">
                Creează Clasa
            </button>

            <div id="message" class="hidden bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center"></div>
            </form>
    </div>

    <script>
        const form = document.getElementById('createClassForm');
        const messageDiv = document.getElementById('message');
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            postData(formData);
        });
        
        async function postData(formData) {
            try {
                const response = await fetch('createClass.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                messageDiv.classList.remove('hidden');
                
                if (data && data.success) {
                    messageDiv.className = 'bg-green-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                    messageDiv.textContent = data.message;
                    
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    messageDiv.className = 'bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                    messageDiv.textContent = data.message || 'A apărut o eroare.';
                }
            } catch (err) {
                console.error(err);
                messageDiv.classList.remove('hidden');
                messageDiv.className = 'bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                messageDiv.textContent = 'A apărut o eroare. Te rugăm să încerci din nou.';
            }
        }
    </script>

</body>

</html>
