<?php
session_start();
require_once 'connectDB.php';
require_once 'operatiiDB.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true); // citim datele JSON
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

$response = ['success' => false];

if (!$username || !$password) {
    $response['message'] = 'Trebuie completate username și parola!';
    echo json_encode($response);
}

try {
    $users = OperatiiDB::read('users', "WHERE username = :username", [':username' => $username]);

    if (!$users) {
        $response['message'] = 'Username inexistent.';
    } else {
        $user = $users[0];
        if (!password_verify($password, $user['password'])) {
            $response['message'] = 'Parolă incorectă.';
        } else {
            // Login reusit, deci salvam in sesiune
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            $response['success'] = true;
            $response['message'] = 'Login reușit.';

            if ($user['role'] === 'trainer') {
                $response['redirect'] = 'trainerDashboard.php';
            } else {
                $response['redirect'] = 'clientDashboard.php';
            }
        }
    }

} catch (PDOException $e) {
    $response['message'] = "Eroare DB: " . $e->getMessage();
}

echo json_encode($response); //trimite inapoi la frontend