
<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';
require_once '../models/User.php';

// Verifică dacă userul este logat
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesare alegere abonament
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tip = $_POST['tip'] ?? '';
    $preturi = [
        'Basic' => 100,
        'Premium' => 180,
        'VIP' => 250
    ];
    $durata = 30; // zile
    if (isset($preturi[$tip])) {
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime("+$durata days"));
        // Verifică dacă există deja un abonament activ
        $abonamente = OperatiiDB::read('subscriptions', 'WHERE user_id = ? AND status = "active"', [$user_id]);
        if (!empty($abonamente)) {
            // Update abonament existent
            OperatiiDB::update('subscriptions', [
                'type' => $tip,
                'price' => $preturi[$tip],
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active'
            ], 'id = :id', ['id' => $abonamente[0]['id']]);
        } else {
            // Creează abonament nou
            OperatiiDB::create('subscriptions', [
                'user_id' => $user_id,
                'type' => $tip,
                'price' => $preturi[$tip],
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active'
            ]);
        }

        $user = User::findById($user_id);
        $max_classes = $user->getMaxAllowedClasses();
        // Preia toate înscrierile la clase, ordonate descrescător după data înscrierii (cele mai recente primele)
        $inscrieri = OperatiiDB::read('class_registrations', 'WHERE client_id = ? ORDER BY id DESC', [$user_id]);
        if (count($inscrieri) > $max_classes) {
            // Păstrează doar cele mai recente N înscrieri, restul se șterg
            $to_delete = array_slice($inscrieri, $max_classes);
            foreach ($to_delete as $row) {
                OperatiiDB::delete('class_registrations', 'id = :id', [':id' => $row['id']]);
            }
        }

        header('Location: ../client/clientDashboard.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Alege Abonament</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-[url('../imagini/dumbells.webp')] bg-cover bg-center py-10">
    <a href="../index.php" class="flex items-center gap-6 no-underline cursor-pointer hover:opacity-90">
        <img src="../imagini/logo.webp" alt="CipFit logo" class="h-20 w-auto object-contain drop-shadow-lg" />
        <div class="w-[1.3px] h-16 bg-black drop-shadow-lg"></div>
        <div class="text-left drop-shadow-lg">
            <p class="text-4xl font-extrabold text-black drop-shadow-lg">CipFit</p>
            <p class="text-xl font-semibold text-black drop-shadow-lg">Studio</p>
        </div>
    </a>
    <h1 class="text-3xl font-bold text-black mt-10 mb-10 text-center">Alege tipul de abonament</h1>
    <form method="POST" class="flex flex-col items-center w-full mb-10">
        <div class="flex flex-row gap-12 w-full justify-center items-center mb-8 px-8 md:px-24">
            <label class="bg-gradient-to-t from-white/60 to-black/60 rounded-3xl p-12 flex-1 min-w-0 h-[420px] flex flex-col items-center cursor-pointer transition shadow-2xl hover:shadow-[0_8px_32px_0_rgba(0,0,0,0.45)] text-xl">
                <input type="radio" name="tip" value="Basic" required class="mb-3 accent-black scale-125"> 
                <span class="font-bold text-2xl text-black mb-2">Basic</span>
                <ul class="text-black text-lg mb-1 list-disc list-inside space-y-1 text-left w-full pl-2">
                    <li>1 clasă activă simultan</li>
                    <li>Acces la clase de grup</li>
                    <li>Suport email</li>
                </ul>
                <span class="flex-1"></span>
                <span class="text-3xl font-extrabold text-black mb-2 mt-4 drop-shadow-lg">100 lei</span>
                <span class="text-black text-sm">Valabil 30 zile</span>
            </label>
            <label class="bg-gradient-to-t from-white/60 to-black/60 rounded-3xl p-12 flex-1 min-w-0 h-[420px] flex flex-col items-center cursor-pointer transition shadow-2xl hover:shadow-[0_8px_32px_0_rgba(0,0,0,0.45)] text-xl">
                <input type="radio" name="tip" value="Premium" class="mb-3 accent-black scale-125"> 
                <span class="font-bold text-2xl text-black mb-2">Premium</span>
                <ul class="text-black text-lg mb-1 list-disc list-inside space-y-1 text-left w-full pl-2">
                    <li>2 clase active simultan</li>
                    <li>Acces la clase de grup și individuale</li>
                    <li>Suport prioritar email</li>
                </ul>
                <span class="flex-1"></span>
                <span class="text-3xl font-extrabold text-black mb-2 mt-4 drop-shadow-lg">180 lei</span>
                <span class="text-black text-sm">Valabil 30 zile</span>
            </label>
            <label class="bg-gradient-to-t from-white/60 to-black/60 rounded-3xl p-12 flex-1 min-w-0 h-[420px] flex flex-col items-center cursor-pointer transition shadow-2xl hover:shadow-[0_8px_32px_0_rgba(0,0,0,0.45)] text-xl">
                <input type="radio" name="tip" value="VIP" class="mb-3 accent-black scale-125"> 
                <span class="font-bold text-2xl text-black mb-2">VIP</span>
                <ul class="text-black text-lg mb-1 list-disc list-inside space-y-1 text-left w-full pl-2">
                    <li>3 clase active simultan</li>
                    <li>Acces complet la toate clasele</li>
                    <li>Suport telefonic & email</li>
                </ul>
                <span class="flex-1"></span>
                <span class="text-3xl font-extrabold text-black mb-2 mt-4 drop-shadow-lg">250 lei</span>
                <span class="text-black text-sm">Valabil 30 zile</span>
            </label>
        </div>
        <button type="submit" class="w-[320px] bg-red-600 text-white font-bold py-3 rounded-lg hover:scale-105 transition mt-6">Alege abonament</button>
    </form>
</body>
</html>
