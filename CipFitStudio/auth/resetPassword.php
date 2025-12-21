<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';
require_once '../models/User.php';

// Verificare token la accesarea paginii (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = trim($_GET['token'] ?? '');
    $hash = hash('sha256', $token);
    $users = OperatiiDB::read('users', 'WHERE forgotPasswordHash = ? AND forgotPasswordExpires > NOW()', [$hash]);
    $user = $users ? $users[0] : null;
    if (!$user) {
        die('Token invalid sau expirat!');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $token = trim($data['token'] ?? '');
    $password = trim($data['password'] ?? '');
    $confirm = trim($data['confirm_password'] ?? '');
    $response = ['success' => false];

    if (!$token || !$password || !$confirm) {
        $response['message'] = 'Toate câmpurile sunt obligatorii!';
        echo json_encode($response);
        exit;
    }

    // Validare parola minim 6 caractere
    if (strlen($password) < 6) {
        $response['message'] = 'Parola trebuie să aibă cel puțin 6 caractere!';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm) {
        $response['message'] = 'Parolele nu coincid!';
        echo json_encode($response);
        exit;
    }

    $hash = hash('sha256', $token);
    $users = OperatiiDB::read('users', 'WHERE forgotPasswordHash = ? AND forgotPasswordExpires > NOW()', [$hash]);
    $user = $users ? $users[0] : null;
    if (!$user) {
        $response['message'] = 'Token invalid sau expirat!';
        echo json_encode($response);
        exit;
    }
    // Update password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    OperatiiDB::update('users', [
        'password' => $passwordHash,
        'forgotPasswordHash' => null,
        'forgotPasswordExpires' => null
    ], 'id = :id', ['id' => $user['id']]);
    $response['success'] = true;
    $response['message'] = 'Parola a fost resetată cu succes!';
    echo json_encode($response);
    exit;
}

?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Resetare parolă</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
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
        <h1 class="text-3xl font-bold text-black mb-4">Resetare parolă</h1>
        <p class="text-gray-800 mb-6">Introdu noua parolă pentru contul tău:</p>
        <form id="resetForm" method="POST">
            <input type="hidden" name="token" id="token" />
            <div class="w-[350px] mb-5 relative flex justify-center">
                <div class="w-full">
                    <label class="block text-left text-gray-700 font-medium mb-1">Parolă nouă</label>
                    <input type="password" name="password" id="password" required class="w-full h-12 px-4 pr-12 bg-white rounded-lg border-2 focus:outline-none focus:border-red-500" placeholder="Parolă nouă" />
                </div>
                <button type="button" class="absolute right-3 top-[42px] w-6 h-6 opacity-90" tabindex="-1" onclick="const i=this.parentElement.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password';">
                    <img src="../imagini/see_password.png" alt="See password" class="w-full h-full object-contain" />
                </button>
            </div>
            <div class="w-[350px] mb-5 relative flex justify-center">
                <div class="w-full">
                    <label class="block text-left text-gray-700 font-medium mb-1">Confirmă parola</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="w-full h-12 px-4 pr-12 bg-white rounded-lg border-2 focus:outline-none focus:border-red-500" placeholder="Confirmă parola" />
                </div>
                <button type="button" class="absolute right-3 top-[42px] w-6 h-6 opacity-90" tabindex="-1" onclick="const i=this.parentElement.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password';">
                    <img src="../imagini/see_password.png" alt="See password" class="w-full h-full object-contain" />
                </button>
            </div>
            <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-lg hover:scale-105 transition">Resetează parola</button>
            <div id="message" class="text-red-600 font-medium mt-4 mb-2 hidden text-center max-w-xs mx-auto"></div>
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
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.querySelector('svg').classList.add('text-red-500');
            } else {
                input.type = 'password';
                btn.querySelector('svg').classList.remove('text-red-500');
            }
        }
        // Preia tokenul din URL
        function getTokenFromUrl() {
            const params = new URLSearchParams(window.location.search);
            return params.get('token') || '';
        }
        document.getElementById('token').value = getTokenFromUrl();
        document.getElementById('resetForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const token = document.getElementById('token').value;
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const messageDiv = document.getElementById('message');
            messageDiv.classList.add('hidden');
            try {
                const response = await fetch('resetPassword.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        password,
                        confirm_password: confirm
                    })
                });
                const data = await response.json();
                if (data.success) {
                    messageDiv.textContent = 'Parola a fost resetată cu succes! Vei fi redirecționat către login...';
                    messageDiv.classList.remove('text-red-600');
                    messageDiv.classList.add('text-green-600');
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    messageDiv.textContent = data.message || 'Eroare!';
                    messageDiv.classList.remove('text-green-600');
                    messageDiv.classList.add('text-red-600');
                }
                messageDiv.classList.remove('hidden');
            } catch (err) {
                messageDiv.textContent = 'Eroare de server!';
                messageDiv.classList.remove('hidden');
            }
        });
    </script>
</body>

</html>
