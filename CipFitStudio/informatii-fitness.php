<?php
session_start();
require_once __DIR__ . '/app_config/env.php';
$api_key = $_ENV['API_KEY'];
$url = 'https://api.api-ninjas.com/v1/exercises?limit=20';
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "X-Api-Key: $api_key\r\n"
    ]
];
$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);
$exercitii = [];
if ($response !== false) {
    $exercitii = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CipFit Studio - Info Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0ff;
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen w-full">


    <!-- Top bar -->
    <div class="flex justify-between items-center px-10 mt-6">
        <!-- Brand left -->
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="text-left cursor-pointer" onclick="window.location.href='index.php'">
                    <p class="text-4xl font-extrabold text-black drop-shadow">CipFit</p>
                    <p class="text-xl font-semibold text-black drop-shadow">Studio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fitness Info Section -->
    <div class="flex flex-col justify-center items-center mt-16 mb-16 gap-6">
        <div class="container max-w-[900px] mx-auto bg-white rounded-2xl shadow-lg p-12">
            <h2 class="text-3xl font-bold mb-16 text-center">5 Random recommended exercises</h2>
            <?php if ($exercitii && is_array($exercitii)) : ?>
                <ol class="list-decimal ml-8">
                    <?php
                    shuffle($exercitii);
                    $count = 0;
                    foreach ($exercitii as $ex) : if ($count >= 5) break; ?>
                        <li class="mb-8">
                            <span class="font-bold text-xl"><?= htmlspecialchars($ex['name']) ?></span>
                            <?php if (!empty($ex['equipment'])) : ?>
                                <span class="text-gray-500">(<?= htmlspecialchars($ex['equipment']) ?>)</span>
                            <?php endif; ?>
                            <?php if (!empty($ex['instructions'])) : ?>
                                <div class="text-gray-700 mt-2 text-base leading-relaxed"><?= nl2br(htmlspecialchars($ex['instructions'])) ?></div>
                            <?php endif; ?>
                        </li>
                    <?php $count++;
                    endforeach; ?>
                </ol>
            <?php else : ?>
                <p class="text-red-600 text-center">Nu s-au putut prelua exercițiile de la API.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>