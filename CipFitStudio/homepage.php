<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CipFit Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('imagini/background_img.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("userBtn");
        const dropdown = document.getElementById("userDropdown");
        const arrow = btn.querySelector("svg");

        btn.addEventListener("click", (event) => {
            event.stopPropagation();
            dropdown.classList.toggle("hidden");
            arrow.classList.toggle("rotate-180"); //roteste sageata
        });

        document.addEventListener("click", () => {
            dropdown.classList.add("hidden");
            arrow.classList.remove("rotate-180");
        });
    });
</script>

<body class="min-h-screen w-full">

    <!-- Top bar -->
    <div class="flex justify-between items-center px-10 py-6">

        <!-- Brand left -->
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="text-left cursor-pointer" onclick="window.location.href='homepage.php'">
                    <p class="text-4xl font-extrabold text-black drop-shadow">CipFit</p>
                    <p class="text-xl font-semibold text-black drop-shadow">Studio</p>
                </div>
            </div>
        </div>

        <!-- Buttons right -->
        <div class="flex gap-4 items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Vezi Clasele -->
                <?php
                $linkClasa = ($_SESSION['role'] === 'trainer') ? 'trainerDashboard.php' : 'clientDashboard.php';
                ?>
                <a href="<?= $linkClasa ?>"
                    class="text-white bg-gray-600 px-5 py-2 rounded-md text-sm font-medium transform transition-all duration-200 hover:scale-110 text-center">
                    Vezi Clasele
                </a>

                <!-- Username cu dropdown -->

                <div class="relative">
                    <!-- Username button -->
                    <button id="userBtn"
                        class="text-white bg-[#D10000] px-5 py-2 rounded-md text-sm font-medium transform transition-all duration-200 hover:scale-110 text-center flex items-center gap-2">
                        <?php echo $_SESSION['username']; ?>
                        <!-- sageata -->
                        <svg class="w-4 h-4 mt-0.5 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- dropdown -->
                    <div id="userDropdown"
                        class="hidden absolute right-0 mt-2 bg-[#D10000] text-white rounded-md shadow-lg w-full z-20">
                        <a href="logout.php"
                            class="block px-5 py-2 rounded-md text-sm font-medium hover:bg-[#B80000] transition text-center">
                            Logout
                        </a>
                    </div>
                </div>

        </div>

    <?php else: ?>
        <!-- Login / Signup -->
        <a href="login.html"
            class="text-white bg-gray-600 px-5 py-2 rounded-md text-sm font-medium transform transition-all duration-200 hover:scale-110 text-center">
            Login
        </a>
        <a href="signup.html"
            class="text-white bg-[#D10000] px-5 py-2 rounded-md text-sm font-medium transform transition-all duration-200 hover:scale-110 text-center">
            Signup
        </a>
    <?php endif; ?>
    </div>
    </div>

    <!-- Center text -->
    <div class="flex justify-center items-center mt-32">
        <h1 class="text-red-600 text-2xl md:text-4xl font-extrabold drop-shadow-[0_0_3px_black]">
            Become the best version of yourself.
        </h1>
    </div>

</body>

</html>