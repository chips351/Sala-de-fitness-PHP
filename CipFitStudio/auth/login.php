<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../models/User.php';
require_once '../app_config/operatiiDB.php';


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

    if (!$user || !$user->verifyPassword($password)) {
        $response['message'] = 'Username sau parolă incorectă.';
        echo json_encode($response);
        exit;
    }

    // doar daca credentials sunt corecte, verifică activarea
    if ($user->getAccountActivationHash() !== null) {
        $response['message'] = 'Contul nu este activat! Verifică emailul pentru link-ul de activare.';
        echo json_encode($response);
        exit;
    }

    //login reusit
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['username'] = $user->getUsername();
    $_SESSION['role'] = $user->getRole();
    $_SESSION['name'] = $user->getName();
    $_SESSION['email'] = $user->getEmail();


    $response['success'] = true;
    $response['message'] = 'Login reușit.';
    if ($user->getRole() === 'trainer') {
        $response['redirect'] = '../trainer/trainerDashboard.php';
    } else {
        // verifica daca userul are abonament activ
        $abonamente = OperatiiDB::read('subscriptions', 'WHERE user_id = ? AND status = "active" AND end_date >= CURDATE()', [$user->getId()]);
        if ($abonamente) {
            $response['redirect'] = '../client/clientDashboard.php';
        } else {
            $response['redirect'] = '../client/chooseSubscription.php';
        }
    }

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    $response['message'] = 'Eroare: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
