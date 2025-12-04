<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../models/User.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $user = new User([
            'name' => trim($data['name'] ?? ''),
            'username' => trim($data['username'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'role' => trim($data['role'] ?? '')
        ]);

        $password = trim($data['password'] ?? '');
        $confirmPassword = trim($data['confirm_password'] ?? '');

        $user->create($password, $confirmPassword);

        $response['success'] = true;
        $response['message'] = 'Cont creat cu succes!';
        $response['redirect'] = 'login.html';

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);