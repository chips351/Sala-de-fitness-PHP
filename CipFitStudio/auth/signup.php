<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $role = trim($data['role'] ?? '');
    $password = trim($data['password'] ?? '');
    $confirm_password = trim($data['confirm_password'] ?? '');

    //validare campuri goale
    if (!$name || !$username || !$email || !$role || !$password || !$confirm_password) {
        $response['message'] = 'Toate câmpurile obligatorii trebuie completate!';
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response['message'] = 'Parolele nu coincid!';
        echo json_encode($response);
        exit;
    }

    //verifica username unic
    $existingUser = OperatiiDB::read('users', "WHERE username = :username", [':username' => $username]);
    if ($existingUser && count($existingUser) > 0) {
        $response['message'] = "Username-ul '$username' există deja!";
        echo json_encode($response);
        exit;
    }

    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userId = OperatiiDB::create('users', [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
            'phone' => $phone,
            'status' => 'active'
        ]);

        $response['success'] = true;
        $response['message'] = 'Cont creat cu succes!';
        $response['redirect'] = 'login.html';

    } catch (Exception $e) {
        error_log('Signup error: ' . $e->getMessage());
        $response['message'] = 'Eroare la creare cont. Încercați din nou.';
    }
}

echo json_encode($response);