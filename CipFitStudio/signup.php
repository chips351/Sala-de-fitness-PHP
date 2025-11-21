<?php
require_once 'connectDB.php';
require_once 'operatiiDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if ($password !== $confirm_password) {
        $errors[] = "Parolele nu coincid!";
    }

    // username unic
    $existingUser = OperatiiDB::read('users', "WHERE username = '$username'");
    if ($existingUser) {
        $errors[] = "Username-ul '$username' există deja!";
    }

    if (empty($errors)) {
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

        header("Location: login.html");
        exit;
    } else {
        foreach ($errors as $err) {
            echo "$err<br>";
        }
    }
}