<?php
session_start();
require_once 'connectDB.php';
require_once 'operatiiDB.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // validare campuri goale
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

    // Verifică username unic
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