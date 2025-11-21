<?php
session_start();
require_once "connectDB.php";

// Check trainer session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "trainer") {
    die("Acces interzis.");
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Procesare formular
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $trainer_id   = $_SESSION["user_id"];
    $title        = $_POST["title"];
    $description  = $_POST["description"];
    $date         = $_POST["date"];
    $time         = $_POST["time"];
    $duration     = $_POST["duration"];
    $max_clients  = $_POST["max_clients"];
    $location     = $_POST["location"];

    try {
        $sql = "INSERT INTO classes 
                (trainer_id, title, description, date, time, duration, max_clients, location)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $trainer_id,
            $title,
            $description,
            $date,
            $time,
            $duration,
            $max_clients,
            $location
        ]);

        header("Location: trainerDashboard.php?created=1");
        exit;
    } catch (PDOException $e) {
        die("Eroare DB: " . $e->getMessage());
    }
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
            background-image: url('imagini/dashboardBG.jpg');
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
    <div class="relative z-10 flex justify-center items-center min-h-screen">

        <div class="bg-black/40
                    backdrop-blur-md shadow-2xl rounded-3xl p-10 w-[500px]
                    border border-white/30">

            <h2 class="text-3xl font-extrabold text-white text-center mb-10 drop-shadow-[0_0_5px_black]">
                Creează o nouă clasă
            </h2>

            <form method="POST" class="flex flex-col gap-5">

                <input type="text" name="title" placeholder="Titlul clasei"
                    class="w-full h-12 px-4 bg-white/30 text-white placeholder-white
                           rounded-xl shadow focus:outline-none font-semibold" required>

                <textarea name="description" placeholder="Descriere"
                    class="w-full px-4 py-3 bg-white/30 text-white placeholder-white
                           rounded-xl shadow focus:outline-none font-semibold" rows="3"></textarea>

                <input type="date" name="date"
                    class="w-full h-12 px-4 bg-white/30 text-white rounded-xl shadow font-semibold" required>

                <input type="time" name="time"
                    class="w-full h-12 px-4 bg-white/30 text-white rounded-xl shadow font-semibold" required>

                <input type="number" name="duration" placeholder="Durată (minute)"
                    class="w-full h-12 px-4 bg-white/30 text-white placeholder-white
                           rounded-xl shadow font-semibold">

                <input type="number" name="max_clients" placeholder="Număr maxim clienți"
                    class="w-full h-12 px-4 bg-white/30 text-white placeholder-white
                           rounded-xl shadow font-semibold">

                <input type="text" name="location" placeholder="Locație" required
                    class="w-full h-12 px-4 bg-white/30 text-white placeholder-white
                           rounded-xl shadow font-semibold">

                <button
                    class="w-full bg-[#D10000] text-white text-lg font-extrabold py-3 rounded-xl
                           shadow-lg hover:scale-105 transition">
                    Creează Clasa
                </button>
            </form>
        </div>
    </div>

</body>

</html>
