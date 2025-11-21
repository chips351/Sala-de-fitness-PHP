<?php
session_start();
require_once 'connectDB.php';
require_once 'operatiiDB.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "trainer") {
    die("Acces interzis.");
}

$trainer_id = $_SESSION['user_id'];

// Procesare editare clasa
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];

    OperatiiDB::update(
        'classes',
        [
            'title'       => $_POST['title'],
            'description' => $_POST['description'],
            'date'        => $_POST['date'],
            'time'        => $_POST['time'],
            'duration'    => $_POST['duration'],
            'max_clients' => $_POST['max_clients'],
            'location'    => $_POST['location']
        ],
        'id = :id AND trainer_id = :trainer_id',
        [':id' => $id, ':trainer_id' => $trainer_id]
    );
}

//pt delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    OperatiiDB::delete('classes', 'id = :id AND trainer_id = :trainer_id', [
        ':id' => $_POST['delete_id'],
        ':trainer_id' => $trainer_id
    ]);
}

//pt read
$classes = OperatiiDB::read(
    'classes',
    'WHERE trainer_id = :trainer_id ORDER BY date, time',
    [':trainer_id' => $trainer_id]
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Clasele Tale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('imagini/dashboardBG.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen relative py-20">

    <!-- Gray mask overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    <!-- Back button -->

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

    <div class="relative z-10 flex flex-col items-center min-h-screen pt-32 px-4">

        <h2 class="text-3xl font-extrabold text-white mb-8 drop-shadow-[0_0_5px_black]">
            Clasele tale
        </h2>

        <div class="bg-white/20 backdrop-blur-lg p-6 rounded-2xl shadow-xl w-full max-w-5xl border border-white/30">
            <?php if (empty($classes)): ?>
                <p class="text-white font-semibold text-center">Nu ai creat nicio clasă încă.</p>
            <?php else: ?>
                <table class="w-full text-white border-collapse">
                    <thead>
                        <tr>
                            <th class="border-b border-white/50 px-4 py-2 text-left">Titlu</th>
                            <th class="border-b border-white/50 px-4 py-2 text-left">Descriere</th>
                            <th class="border-b border-white/50 px-4 py-2">Data</th>
                            <th class="border-b border-white/50 px-4 py-2">Ora</th>
                            <th class="border-b border-white/50 px-4 py-2">Durată</th>
                            <th class="border-b border-white/50 px-4 py-2">Max Clienți</th>
                            <th class="border-b border-white/50 px-4 py-2">Locație</th>
                            <th class="border-b border-white/50 px-4 py-2">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $cls): ?>
                            <tr class="hover:bg-white/10 transition">
                                <form method="POST" class="flex flex-row gap-2 items-center">
                                    <input type="hidden" name="id" value="<?= $cls['id'] ?>">

                                    <td><input type="text" name="title" value="<?= htmlspecialchars($cls['title']) ?>"
                                            class="w-full bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="text" name="description" value="<?= htmlspecialchars($cls['description']) ?>"
                                            class="w-full bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="date" name="date" value="<?= $cls['DATE'] ?>"
                                            class="bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="time" name="time" value="<?= $cls['TIME'] ?>"
                                            class="bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="number" name="duration" value="<?= $cls['duration'] ?>"
                                            class="w-full bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="number" name="max_clients" value="<?= $cls['max_clients'] ?>"
                                            class="w-full bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td><input type="text" name="location" value="<?= htmlspecialchars($cls['location']) ?>"
                                            class="w-full bg-white/30 text-white px-2 rounded focus:outline-none font-semibold"></td>

                                    <td class="flex flex-row gap-2 ml-2">
                                        <button type="submit"
                                            class="bg-red-600 px-3 py-1 rounded hover:scale-105 transition font-extrabold">
                                            Salvează
                                        </button>

                                        <!-- DELETE direct aici -->
                                        <button type="submit" name="delete_id" value="<?= $cls['id'] ?>"
                                            onclick="return confirm('Sigur vrei să ștergi această clasă?');"
                                            class="bg-gray-600 px-3 py-1 rounded hover:scale-105 transition font-extrabold">
                                            Șterge
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>