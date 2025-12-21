<?php
session_start();
require_once 'captcha.php';

if (!isset($_SESSION['signup_data'])) {
    header('Location: signup.html');
    exit;
}

if (isset($_GET['image'])) {
    Captcha::generateImage();
    exit;
}

if (isset($_GET['questions'])) {
    header('Content-Type: application/json');
    echo json_encode(Captcha::getQuestions());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../app_config/connectDB.php';
    require_once '../app_config/operatiiDB.php';
    require_once '../app_config/Email.php';
    require_once '../models/User.php';
    
    header('Content-Type: application/json');
    
    $response = ['success' => false];
    $data = json_decode(file_get_contents('php://input'), true);
    
    $answer1 = intval($data['answer1'] ?? -1);
    $answer2 = intval($data['answer2'] ?? -1);
    
    // Limita de 3 greseli
    if (!isset($_SESSION['captcha_failures'])) {
        $_SESSION['captcha_failures'] = 0;
    }
    
    try {
        if (!Captcha::verify($answer1, $answer2)) {
            $_SESSION['captcha_failures']++;
            if ($_SESSION['captcha_failures'] >= 3) {
                unset($_SESSION['signup_data']);
                unset($_SESSION['captcha_failures']);
                unset($_SESSION['captcha_questions']);
                unset($_SESSION['captcha_answers']);
                
                $response['captcha_failed'] = true;
                $response['redirect'] = 'signup.html';
                $response['message'] = 'Ai gresit de 3 ori. Te rugam sa reincepi inregistrarea.';
                echo json_encode($response);
                exit;
            }

            $response['message'] = 'CAPTCHA incorect! Incearca din nou.';
            $response['regenerate'] = true;
            echo json_encode($response);
            exit;
        }
        
        // Reset counter la succes
        unset($_SESSION['captcha_failures']);
        $_SESSION['captcha_verified'] = true;
        $response['success'] = true;
        $response['message'] = 'CAPTCHA verificat!';
        $response['redirect'] = 'signup.php?create=1';
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Verificare CAPTCHA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex justify-center items-center bg-[url('../imagini/dumbells.webp')] bg-cover bg-center py-20">
    
    <div class="bg-gradient-to-b from-black/50 to-white/50 backdrop-blur-sm shadow-xl rounded-3xl p-10 w-[500px] flex flex-col items-center">
        
        <h1 class="text-3xl font-bold text-black mb-4">Verificare umană</h1>
        <p class="text-gray-800 mb-6">Rezolvă CAPTCHA-ul pentru a finaliza înregistrarea:</p>
        
        <form id="captchaForm">
            
            <div class="flex items-center gap-3 mb-6">
                <img src="verify-captcha.php?image" id="captchaImage" class="border-2 border-gray-300 rounded-lg w-full" />
                <button type="button" onclick="refreshCaptcha()" 
                    class="hover:scale-110 transition" title="Regenerează">
                    <svg fill="#000000" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8">
                        <path d="M19.146 4.854l-1.489 1.489A8 8 0 1 0 12 20a8.094 8.094 0 0 0 7.371-4.886 1 1 0 1 0-1.842-.779A6.071 6.071 0 0 1 12 18a6 6 0 1 1 4.243-10.243l-1.39 1.39a.5.5 0 0 0 .354.854H19.5A.5.5 0 0 0 20 9.5V5.207a.5.5 0 0 0-.854-.353z"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-3 mb-6">
                <div>
                    <label class="block text-sm text-gray-800 font-medium mb-1" id="question1Label"></label>
                    <input type="number" name="answer1" id="answer1" required
                        class="w-full h-12 px-4 bg-white rounded-lg border-2 focus:outline-none focus:border-red-500"
                        placeholder="Răspuns" min="0" />
                </div>
                <div>
                    <label class="block text-sm text-gray-800 font-medium mb-1" id="question2Label"></label>
                    <input type="number" name="answer2" id="answer2" required
                        class="w-full h-12 px-4 bg-white rounded-lg border-2 focus:outline-none focus:border-red-500"
                        placeholder="Răspuns" min="0" />
                </div>
            </div>
            
            <div id="message" class="text-red-600 font-medium mb-4 hidden"></div>
            
            <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-lg hover:scale-105 transition">
                Verifică și finalizează
            </button>
        </form>
    </div>
    
    <script>
        loadQuestions();
        
        function refreshCaptcha() {
            const img = document.getElementById('captchaImage');
            
            // Asteapta ca imaginea noua sa se incarce complet
            img.onload = function() {
                // Delay mic pentru a reseta corect intrebarile dupa schimbarea imaginii
                setTimeout(() => loadQuestions(), 150);
            };
            
            img.src = 'verify-captcha.php?image&' + Math.random(); //ma asigur ca nu e aceeasi imagine in cache
            document.getElementById('answer1').value = '';
            document.getElementById('answer2').value = '';
            document.getElementById('message').classList.add('hidden');
        }
        
        async function loadQuestions() {
            try {
                const response = await fetch('verify-captcha.php?questions&' + Math.random());
                const data = await response.json();
                document.getElementById('question1Label').textContent = data.q1;
                document.getElementById('question2Label').textContent = data.q2;
            } catch (err) {
                console.error('Eroare:', err);
            }
        }
        
        document.getElementById('captchaForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const formData = {
                answer1: this.answer1.value,
                answer2: this.answer2.value
            };
            
            try {
                const response = await fetch('verify-captcha.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                const messageDiv = document.getElementById('message');
                
                if (data.success) {
                    messageDiv.className = 'text-green-600 font-bold mb-4 text-center';
                    messageDiv.textContent = data.message;
                    messageDiv.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    messageDiv.className = 'text-red-600 font-medium mb-4';
                    messageDiv.textContent = data.message;
                    messageDiv.classList.remove('hidden');
                    if (data.regenerate) {
                        refreshCaptcha();
                    } else if (data.captcha_failed) {
                        messageDiv.className = 'text-red-600 font-bold mb-4 text-center';
                        messageDiv.textContent = data.message;
                        messageDiv.classList.remove('hidden');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                        return;
                    }
                }
            } catch (err) {
                console.error(err);
            }
        });
    </script>
</body>
</html>
