<?php
session_start();
require_once '../models/FitnessClass.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "trainer") {
    die("Acces interzis.");
}

$trainer_id = $_SESSION['user_id'];

// trebuie sa existe id in URL
if (!isset($_GET['id'])) {
    die("Clasă invalidă.");
}

$class_id = $_GET['id'];

// citim clasa selectata
$fitnessClass = FitnessClass::findById($class_id, $trainer_id);

if (!$fitnessClass) {
    die("Clasă inexistentă sau inaccesibilă.");
}

$errorMessage = '';

// PROCESARE POST (UPDATE sau DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => ''];
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // DELETE
    if (isset($data['delete'])) {
        try {
            $fitnessClass->delete();
            
            $response['success'] = true;
            $response['message'] = 'Clasa a fost ștearsă cu succes!';
            $response['redirect'] = 'viewClasses.php';
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
    // UPDATE (default)
    else {
        try {
            $fitnessClass->setTitle($data['title'] ?? '');
            $fitnessClass->setDescription($data['description'] ?? '');
            $fitnessClass->setDate($data['date'] ?? '');
            $fitnessClass->setTime($data['time'] ?? '');
            $fitnessClass->setDuration($data['duration'] ?? 0);
            $fitnessClass->setMaxClients($data['max_clients'] ?? 0);
            $fitnessClass->setLocation($data['location'] ?? '');

            $fitnessClass->update();
            
            $response['success'] = true;
            $response['message'] = 'Clasa a fost actualizată cu succes!';
            $response['redirect'] = 'viewClasses.php';
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Editare Clasă</title>
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

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    <!-- buton inapoi -->
    <div class="absolute top-0 left-0 w-full flex items-center px-10 py-6 z-10">
        <a href="viewClasses.php"
           class="text-white text-xl font-extrabold drop-shadow-[0_0_5px_black] hover:scale-110 transition flex items-center gap-2">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 850 1000" fill="currentColor">
                <path d="M750 310c26.667 0 50 9.667 70 29c20 19.333 30 43 30 71v290c0 26.667 -10 50 -30 70c-20 20 -43.333 30 -70 30H60v-140h650V420H210v110L0 350l210-180v110h540z"/>
            </svg>
            Înapoi la Clase
        </a>
    </div>

    <div class="relative z-10 flex flex-col items-center min-h-screen pt-32 px-4">
        <h2 class="text-2xl font-extrabold text-white mb-6 drop-shadow-[0_0_5px_black] text-center">
            Editare Clasă: <?= htmlspecialchars($fitnessClass->getTitle()) ?>
        </h2>

        <form id="editClassForm" method="POST" class="bg-white/20 backdrop-blur-lg p-8 rounded-2xl shadow-xl w-full max-w-3xl border border-white/30 space-y-6">

            <div>
                <label class="text-white font-bold">Titlu:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($fitnessClass->getTitle()) ?>"
                       class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
            </div>

            <div>
                <label class="text-white font-bold">Descriere:</label>
                <textarea name="description" rows="4"
                          class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold"><?= htmlspecialchars($fitnessClass->getDescription()) ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label class="text-white font-bold">Data:</label>
                    <input type="date" name="date" value="<?= $fitnessClass->getDate() ?>"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Ora:</label>
                    <input type="time" name="time" value="<?= $fitnessClass->getTime() ?>"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Durată (minute):</label>
                    <input type="number" name="duration" value="<?= $fitnessClass->getDuration() ?>"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

                <div>
                    <label class="text-white font-bold">Max clienți:</label>
                    <input type="number" name="max_clients" value="<?= $fitnessClass->getMaxClients() ?>"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
                </div>

            </div>

            <div>
                <label class="text-white font-bold">Locație:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($fitnessClass->getLocation()) ?>"
                       class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold">
            </div>

            <div class="flex justify-between pt-6">
                <button type="submit" name="update"
                        class="bg-green-600 px-5 py-2 rounded text-white font-extrabold hover:scale-105 transition">
                    Salvează Modificările
                </button>

                <button type="submit" name="delete"
                        class="bg-red-600 px-5 py-2 rounded text-white font-extrabold hover:scale-105 transition">
                    Șterge Clasa
                </button>
            </div>

            <div id="message" class="hidden bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center"></div>
        </form>
    </div>

    <script>
        const form = document.getElementById('editClassForm');
        const messageDiv = document.getElementById('message');

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const isDelete = event.submitter && event.submitter.name === 'delete';
            
            if (isDelete && !confirm('Sigur vrei să ștergi această clasă?')) {
                return;
            }
            
            const formattedFormData = {
                title: this.title.value,
                description: this.description.value,
                date: this.date.value,
                time: this.time.value,
                duration: this.duration.value,
                max_clients: this.max_clients.value,
                location: this.location.value
            };
            
            if (event.submitter && event.submitter.name) {
                formattedFormData[event.submitter.name] = event.submitter.value || '';
            }
            
            postData(formattedFormData);
        });
        
        async function postData(formattedFormData) {
            try {
                const response = await fetch('editClass.php?id=<?= $class_id ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formattedFormData)
                });

                const data = await response.json();
                
                if (data) {
                    messageDiv.classList.remove('hidden');
                    messageDiv.textContent = data.message;
                    
                    if (data.success) {
                        messageDiv.className = 'bg-green-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                        
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                messageDiv.classList.add('hidden');
                            }, 3000);
                        }
                    } else {
                        messageDiv.className = 'bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                    }
                }
            } catch (err) {
                console.error(err);
                messageDiv.classList.remove('hidden');
                messageDiv.className = 'bg-red-500/80 text-white px-4 py-3 rounded-xl font-semibold text-center';
                messageDiv.textContent = err.message || 'Eroare de rețea';
            }
        }
    </script>

</body>
</html>