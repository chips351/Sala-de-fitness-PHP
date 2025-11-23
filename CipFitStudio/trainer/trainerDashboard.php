<?php
session_start();

// Daca nu e logat ca trainer, n are acces
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
    header("Location: ../auth/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex justify-center items-center 
             bg-[url('../imagini/dashboardBG.jpg')] bg-cover bg-center py-20">

    <div class="bg-white/45
                backdrop-blur-sm shadow-xl rounded-3xl 
                p-10 w-[500px] flex flex-col items-center">

        <!-- Logo -->
        <a href="../homepage.php" class="flex items-center gap-6 mb-10 no-underline cursor-pointer hover:opacity-90">
            <img src="../imagini/logo.png" alt="CipFit logo" class="h-20 w-auto object-contain drop-shadow-lg" />
            <div class="w-[1.3px] h-16 bg-black drop-shadow-lg"></div>
            <div class="text-left drop-shadow-lg">
                <p class="text-4xl font-extrabold text-black drop-shadow-lg">CipFit</p>
                <p class="text-xl font-semibold text-black drop-shadow-lg">Studio</p>
            </div>
        </a>

        <!-- Welcome -->
        <h1 class="text-3xl font-bold text-black mb-8 text-center">
            Bine ai venit, <?= htmlspecialchars($_SESSION['name']) ?>!
        </h1>

        <!-- Buttons -->
        <a href="createClass.php"
            class="w-full bg-[#444346] text-white text-lg font-bold py-3 rounded-lg mb-4 
                   hover: hover:scale-105 transition text-center">
            Creează o clasă
        </a>

        <a href="viewClasses.php"
            class="w-full bg-[#444346] text-white text-lg font-bold py-3 rounded-lg mb-4 
                   hover: hover:scale-105 transition text-center">
            Vezi clasele tale
        </a>

        <a href="../auth/logout.php"
            class="w-full bg-[#D10000] text-white text-lg font-bold py-3 rounded-lg mt-6
                   hover: hover:scale-105 transition text-center">
            Logout
        </a>

    </div>

</body>
</html>