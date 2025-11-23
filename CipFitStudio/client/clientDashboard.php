<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.html');
    exit;
}

echo "Te-ai logat drept " . htmlspecialchars($_SESSION['name']) . "!";