<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../models/User.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

$response = ['success' => false];

if (!$username || !$password) {
    $response['message'] = 'Trebuie completate username și parola!';
    echo json_encode($response);
    exit;
}

try {
    $user = User::findByUsername($username);

    if (!$user) {
        $response['message'] = 'Username inexistent.';
        echo json_encode($response);
        exit;
    }

    if (!$user->verifyPassword($password)) {
        $response['message'] = 'Parolă incorectă.';
        echo json_encode($response);
        exit;
    }

    //login reusit
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['username'] = $user->getUsername();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['name'] = $user->getName();

    $response['success'] = true;
    $response['message'] = 'Login reușit.';
    $response['redirect'] = ($user->getRole() === 'trainer')
        ? '../trainer/trainerDashboard.php'
        : '../client/clientDashboard.php';

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    $response['message'] = 'Eroare: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
