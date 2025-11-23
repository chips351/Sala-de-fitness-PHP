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

// PROCESARE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        $fitnessClass->setTitle($_POST['title'] ?? '');
        $fitnessClass->setDescription($_POST['description'] ?? '');
        $fitnessClass->setDate($_POST['date'] ?? '');
        $fitnessClass->setTime($_POST['time'] ?? '');
        $fitnessClass->setDuration($_POST['duration'] ?? 0);
        $fitnessClass->setMaxClients($_POST['max_clients'] ?? 0);
        $fitnessClass->setLocation($_POST['location'] ?? '');

        $fitnessClass->update();
        
        // Reincarca clasa actualizata
        $fitnessClass = FitnessClass::findById($class_id, $trainer_id);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// PROCESARE DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $fitnessClass->delete();
    header("Location: viewClasses.php");
    exit();
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
            background-image: url('../imagini/dashboardBG.jpg');
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

        <?php if (!empty($errorMessage)): ?>
            <div class="bg-red-500/80 text-white px-4 py-3 rounded-xl mb-4 font-semibold text-center">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white/20 backdrop-blur-lg p-8 rounded-2xl shadow-xl w-full max-w-3xl border border-white/30 space-y-6">

            <div>
                <label class="text-white font-bold">Titlu:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($fitnessClass->getTitle()) ?>"
                       class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold" required>
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
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold" required>
                </div>

                <div>
                    <label class="text-white font-bold">Ora:</label>
                    <input type="time" name="time" value="<?= $fitnessClass->getTime() ?>"
                           class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold" required>
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
                       class="w-full bg-white/30 text-white px-3 py-2 rounded focus:outline-none font-semibold" required>
            </div>

            <div class="flex justify-between pt-6">
                <button type="submit" name="update"
                        class="bg-green-600 px-5 py-2 rounded text-white font-extrabold hover:scale-105 transition">
                    Salvează Modificările
                </button>

                <button type="submit" name="delete"
                        onclick="return confirm('Sigur vrei să ștergi această clasă?');"
                        class="bg-red-600 px-5 py-2 rounded text-white font-extrabold hover:scale-105 transition">
                    Șterge Clasa
                </button>
            </div>
        </form>
    </div>

</body>
</html>