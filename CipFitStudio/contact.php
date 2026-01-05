<?php
require_once __DIR__ . '/app_config/Email.php';
require_once __DIR__ . '/app_config/env.php';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $nume = trim($data['nume'] ?? '');
    $email = trim($data['email'] ?? '');
    $subiect = trim($data['subiect'] ?? '');
    $mesaj = trim($data['mesaj'] ?? '');
    $recaptcha = $data['recaptcha'] ?? '';
    $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';

    // Verificare reCAPTCHA
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $recaptcha);
    $captcha_success = json_decode($verify);
    if (!$nume || !$email || !$subiect || !$mesaj) {
        echo json_encode(['success' => false, 'message' => 'Toate câmpurile sunt obligatorii!']);
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalid!']);
        exit;
    }
    if (!$captcha_success || !$captcha_success->success) {
        echo json_encode(['success' => false, 'message' => 'Verificare reCAPTCHA eșuată!']);
        exit;
    } else {
        $sent = Email::sendContactEmail($nume, $email, $subiect, $mesaj);
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Mesajul a fost trimis cu succes!', 'redirect' => 'index.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'A apărut o eroare la trimiterea mesajului. Încercați din nou mai târziu!']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex justify-center items-center bg-[url('imagini/dumbells.webp')] bg-cover bg-center py-20">
    <div class="bg-gradient-to-b from-black/50 to-white/50
            backdrop-blur-sm shadow-xl rounded-3xl 
            p-10 w-[450px] flex flex-col items-center">
        <!-- Logo + Name -->
        <a href="index.php" class="flex items-center gap-6 mb-10 no-underline cursor-pointer hover:opacity-90">
            <img src="imagini/logo.webp" alt="CipFit logo" class="h-20 w-auto object-contain drop-shadow-lg" />
            <div class="w-[1.3px] h-16 bg-black drop-shadow-lg"></div>
            <div class="text-left drop-shadow-lg">
                <p class="text-4xl font-extrabold text-black drop-shadow-lg">CipFit</p>
                <p class="text-xl font-semibold text-black drop-shadow-lg">Studio</p>
            </div>
        </a>
        <!-- Title -->
        <p class="text-2xl font-bold text-black mb-8">Contact</p>
        <!-- Contact Form -->
        <form id="contactForm" action="contact.php" method="POST" class="w-full flex flex-col items-center">
            <div class="w-full mb-5">
                <label class="block text-left text-gray-700 font-medium mb-1">Nume</label>
                <input type="text" name="nume" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Introdu numele tău" />
            </div>
            <div class="w-full mb-5">
                <label class="block text-left text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Introdu email-ul tău" />
            </div>
            <div class="w-full mb-5">
                <label class="block text-left text-gray-700 font-medium mb-1">Subiect</label>
                <input type="text" name="subiect" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Subiectul mesajului" />
            </div>
            <div class="w-full mb-5">
                <label class="block text-left text-gray-700 font-medium mb-1">Mesaj</label>
                <textarea name="mesaj" rows="4" class="w-full px-4 py-2 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Scrie mesajul tău aici..."></textarea>
            </div>
            <div class="mb-5 w-full flex justify-center">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITEKEY'] ?? ''); ?>"></div>
            </div>
            <button type="submit" class="w-full h-12 bg-black text-white font-bold rounded-lg shadow py-3 hover:scale-105 transition">Trimite</button>
            <div id="message" class="w-full text-center"></div>
        </form>
        <script>
            const form = document.getElementById('contactForm');
            const messageDiv = document.getElementById('message');
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                messageDiv.textContent = '';
                const formData = {
                    nume: this.nume.value,
                    email: this.email.value,
                    subiect: this.subiect.value,
                    mesaj: this.mesaj.value,
                    recaptcha: grecaptcha.getResponse()
                };
                if (!formData.nume || !formData.email || !formData.subiect || !formData.mesaj) {
                    messageDiv.className = 'text-red-600 font-medium mt-2';
                    messageDiv.textContent = 'Toate câmpurile sunt obligatorii!';
                    grecaptcha.reset();
                    return;
                }
                if (!formData.recaptcha) {
                    messageDiv.className = 'text-red-600 font-medium mt-2';
                    messageDiv.textContent = 'Te rugăm să bifezi reCAPTCHA!';
                    grecaptcha.reset();
                    return;
                }
                try {
                    const response = await fetch('contact.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    });
                    const data = await response.json();
                    messageDiv.textContent = data.message;
                    if (data.success) {
                        messageDiv.className = 'text-green-600 font-bold text-lg mt-2';
                        setTimeout(() => {
                            window.location.href = data.redirect || 'index.php';
                        }, 1500);
                    } else {
                        messageDiv.className = 'text-red-600 font-medium mt-2';
                    }
                } catch (err) {
                    messageDiv.className = 'text-red-600 font-medium mt-2';
                    messageDiv.textContent = err.message || 'Eroare de rețea';
                }
            });
        </script>
    </div>
</body>

</html>