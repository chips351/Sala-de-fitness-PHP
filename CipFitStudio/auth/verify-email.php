<?php
session_start();
require_once '../app_config/connectDB.php';
require_once '../app_config/operatiiDB.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Activare cont</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        echo "<h1>Token lipsă!</h1>";
        echo "<p>Link-ul de activare este invalid.</p>";
        exit;
    }
    
    $hash = hash('sha256', $token);
    
    // cauta user cu acest hash
    $users = OperatiiDB::read('users', 
        'WHERE account_activation_hash = :hash',
        [':hash' => $hash]
    );
    
    if (empty($users)) {
        echo "<h1>Token invalid sau expirat!</h1>";
        echo "<p>Link-ul de activare nu mai este valabil sau a fost deja folosit.</p>";
        echo "<p><a href='login.html'>Înapoi la login</a></p>";
        exit;
    }
    
    // activeaza contul (hash-ul NULL)
    OperatiiDB::update('users', 
        ['account_activation_hash' => NULL], 
        'id = :id', 
        [':id' => $users[0]['id']]
    );
    
    $_SESSION['account_activated'] = true;
    
    echo "<h1>Cont activat cu succes!</h1>";
    echo "<p>Contul tău a fost activat. Poți să te loghezi acum.</p>";
    echo "<p>Redirecționare către login...</p>";
    header("refresh:4;url=login.html");
    exit;
}
?>
</body>
</html>
