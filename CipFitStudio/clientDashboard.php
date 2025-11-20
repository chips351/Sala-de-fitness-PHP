<?php
session_start();

echo "Te-ai logat drept " . htmlspecialchars($_SESSION['name']) . "!";