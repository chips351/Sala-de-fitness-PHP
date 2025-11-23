<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

$response = ['success' => false];

//validare
if (!$username || !$password) {
    $response['message'] = 'Trebuie completate username și parola!';
    echo json_encode($response);
    exit;
}

try {
    $users = OperatiiDB::read('users', "WHERE username = :username", [':username' => $username]);

    if (!$users) {
        $response['message'] = 'Username inexistent.';
        echo json_encode($response);
        exit;
    }

    $user = $users[0];

    if (!password_verify($password, $user['password'])) {
        $response['message'] = 'Parolă incorectă.';
        echo json_encode($response);
        exit;
    }

    //login reusit
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];

    $response['success'] = true;
    $response['message'] = 'Login reușit.';
    $response['redirect'] = ($user['role'] === 'trainer')
        ? '../trainer/trainerDashboard.php'
        : '../client/clientDashboard.php';

    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    $response['message'] = "Eroare DB: " . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>