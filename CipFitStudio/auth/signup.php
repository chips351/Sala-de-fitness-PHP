<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';
require_once '../app_config/Email.php';
require_once '../models/User.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

// Verificare daca vine de la CAPTCHA cu flag de creare cont
if (isset($_GET['create']) && $_GET['create'] == '1') {
    try {
        // Verificare flag CAPTCHA
        if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
            throw new Exception('CAPTCHA neverificat. Te rog completează CAPTCHA-ul.');
        }
        
        if (!isset($_SESSION['signup_data'])) {
            throw new Exception('Sesiunea a expirat. Te rog reîncearcă înregistrarea.');
        }
        
        $signupData = $_SESSION['signup_data'];
        
        $user = new User([
            'name' => $signupData['name'],
            'username' => $signupData['username'],
            'email' => $signupData['email'],
            'phone' => $signupData['phone'],
            'role' => $signupData['role']
        ]);
        
        // Generare token si hash pentru activare email
        $token = bin2hex(random_bytes(16));
        $hash = hash('sha256', $token);
        $user->setAccountActivationHash($hash);
        
        $user->create($signupData['password'], $signupData['confirm_password']);
        
        Email::sendVerificationEmail($user->getEmail(), $user->getName(), $token);
        
        // Curatare sesiune
        unset($_SESSION['signup_data']);
        unset($_SESSION['captcha_verified']);
        $_SESSION['just_registered'] = true;
        
        // Redirect direct la login
        header('Location: login.html');
        exit;
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        echo json_encode($response);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $user = new User([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role']
        ]);
        
        $errors = $user->validate($data['password'], $data['confirm_password']);
        
        if (!empty($errors)) {
            throw new Exception($errors[0]);
        }
                $_SESSION['signup_data'] = $data;
        
        $response['success'] = true;
        $response['message'] = 'Cont creat cu succes!';
        $response['redirect'] = 'verify-captcha.php';

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
