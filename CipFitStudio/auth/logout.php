<?php
session_start();

//sterge toate datele din sesiune
$_SESSION = [];

session_destroy();

header("Location: ../index.php");
exit;
?>
