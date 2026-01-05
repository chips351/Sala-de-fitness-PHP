<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.html');
    exit();
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #f0f0f0ff; }
        .container { max-width: 900px; margin: 40px auto; background: #ffffffff; border-radius: 16px; box-shadow: 0 2px 16px #0001; padding: 32px; }
        h1 { font-size: 2rem; font-weight: bold; margin-bottom: 2rem; }
    </style>
</head>
<body class="min-h-screen w-full">
    <!-- Top bar -->
    <div class="flex justify-between items-center px-10 py-6">
        <!-- Brand left -->
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="text-left cursor-pointer" onclick="window.location.href='../index.php'">
                    <p class="text-4xl font-extrabold text-black drop-shadow">CipFit</p>
                    <p class="text-xl font-semibold text-black drop-shadow">Studio</p>
                </div>
            </div>
        </div>
        <div></div>
    </div>
    <div class="container max-w-[900px] mx-auto bg-white rounded-2xl shadow-lg p-12 mt-12">
        <h1 class="text-3xl font-bold mb-8">Statistici abonamente cumpărate (date demo)</h1>
        <canvas id="abonamenteChart" height="100"></canvas>
        <!-- Grafic lunar abonamente -->
        <hr style="margin:40px 0;">
        <h1 class="text-3xl font-bold mb-8">Înscrieri noi (date reale)</h1>
        <canvas id="inscrieriChart" height="100"></canvas>
        <!-- Grafic pe zile -->

        <div class="mt-8 text-center py-2">
            <a href="export-csv.php" class="text-white px-6 py-2 rounded bg-[#D10000] hover:bg-[#B80000] font-semibold shadow">Exportă statistici în CSV</a>
            <a href="export-pdf.php" class="text-white px-6 py-2 rounded bg-blue-600 hover:bg-blue-700 font-semibold shadow ml-4">Exportă statistici în PDF</a>
        </div>
    </div>
    <script>
    // abonamente lunare (date demo)
    const months = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];
    const data = [1,3,5,2,4,4,8,4,2,3,1,11]; // data demo
    const ctx = document.getElementById('abonamenteChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Abonamente cumpărate',
                data: data,
                backgroundColor: 'rgba(220,38,38,0.7)',
                borderRadius: 8,
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, stepSize: 1 }
            }
        }
    });

        // inscrieri ultimele 7 zile (date reale)
        fetch('statistici-inscrieri.php')
            .then(r => r.json())
            .then(({labels, data}) => {
                const ctx2 = document.getElementById('inscrieriChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Înscrieri noi',
                            data: data,
                            backgroundColor: 'rgba(37,99,235,0.7)',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        scales: {
                            y: { beginAtZero: true, stepSize: 1 }
                        }
                    }
                });
            });
    </script>
</body>
</html>
