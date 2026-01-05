<?php
date_default_timezone_set('Europe/Bucharest'); // Setează timezone-ul la București
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';
require_once '../models/User.php';
require_once '../app_config/Email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    $email = trim($data['email'] ?? '');
    $response = ['success' => false];

    if (!$email) {
        $response['message'] = 'Emailul este obligatoriu!';
        echo json_encode($response);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email-ul nu este valid!';
        echo json_encode($response);
        exit;
    }

    // Select user by email
    $users = OperatiiDB::read('users', 'WHERE email = ?', [$email]);
    $user = $users ? $users[0] : null;

    if (!$user) {
        $response['message'] = 'Nu există cont cu acest email!';
        echo json_encode($response);
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

    OperatiiDB::update('users', [
        'forgotPasswordHash' => $hash,
        'forgotPasswordExpires' => $expires
    ], 'id = :id', ['id' => $user['id']]);

    $sent = Email::sendPasswordResetEmail($email, $user['name'], $token);
    if ($sent) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Eroare la trimiterea emailului!';
    }
    echo json_encode($response);
    exit;
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Recuperare parolă</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex justify-center items-center bg-[url('../imagini/dumbells.webp')] bg-cover bg-center py-20">
    <div class="bg-gradient-to-b from-black/50 to-white/50 backdrop-blur-sm shadow-xl rounded-3xl p-10 w-[500px] flex flex-col items-center">
        <a href="../index.php" class="flex items-center gap-6 mb-10 no-underline cursor-pointer hover:opacity-90">
            <img src="../imagini/logo.webp" alt="CipFit logo" class="h-20 w-auto object-contain drop-shadow-lg" />
            <div class="w-[1.3px] h-16 bg-black drop-shadow-lg"></div>
            <div class="text-left drop-shadow-lg">
                <p class="text-4xl font-extrabold text-black drop-shadow-lg">CipFit</p>
                <p class="text-xl font-semibold text-black drop-shadow-lg">Studio</p>
            </div>
        </a>
        <h1 class="text-3xl font-bold text-black mb-4">Recuperare parolă</h1>
        <p class="text-gray-800 mb-6">Introdu adresa de email pentru a reseta parola:</p>
        <form id="forgotForm" method="POST">
            <div class="mb-6 w-[350px] flex justify-center">
                <input type="email" name="email" id="email" required class="w-full h-12 px-4 bg-white rounded-lg border-2 focus:outline-none focus:border-red-500" placeholder="Email" />
            </div>
            <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-lg hover:scale-105 transition">Trimite link de resetare</button>
            <div id="message" class="text-red-600 font-medium mt-2 mb-4 hidden text-center"></div>
        </form>
        <p class="text-sm text-black mt-4">
            Ai deja un cont?
            <a href="login.html" class="font-semibold hover:underline">Loghează-te aici</a>
            <br />
            Nu ai cont?
            <a href="signup.html" class="font-semibold hover:underline">Creează unul aici</a>
        </p>
    </div>
    <script>
        document.getElementById('forgotForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const email = document.getElementById('email').value;
            const messageDiv = document.getElementById('message');
            messageDiv.classList.add('hidden');
            try {
                const response = await fetch('forgotPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await response.json();
                if (data.success) {
                    messageDiv.textContent = 'Verifică emailul pentru linkul de resetare!';
                    messageDiv.classList.remove('text-red-600');
                    messageDiv.classList.add('text-green-600');
                    messageDiv.classList.remove('hidden');
                } else {
                    messageDiv.textContent = data.message || 'Eroare!';
                    messageDiv.classList.remove('text-green-600');
                    messageDiv.classList.add('text-red-600');
                    messageDiv.classList.remove('hidden');
                }
            } catch (err) {
                messageDiv.textContent = 'Eroare de server!';
                messageDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
