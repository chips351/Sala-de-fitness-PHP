<?php
require_once 'connectDB.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "Connection successful!<br>";

    //creare baza de date si tabele
    $sql = "

    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'trainer', 'client') NOT NULL,
        phone VARCHAR(20),
        status ENUM('active', 'inactive') DEFAULT 'active'
    );

    CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        date DATE NOT NULL,
        time TIME NOT NULL,
        duration INT,
        max_clients INT DEFAULT 10,
        location VARCHAR(100),
        FOREIGN KEY (trainer_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    );

    CREATE TABLE IF NOT EXISTS class_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        client_id INT NOT NULL,
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (client_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    );

    CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('Basic', 'Premium', 'VIP') NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('active', 'expired') DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE
    );

    ";

    // executam toate comenzile pe rand(PDO nu permite multi-query direct)
    $commands = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($commands as $command) {
        if ($command !== '') {
            $pdo->exec($command);
        }
    }

    echo "Database and tables initialized successfully!";

} catch (PDOException $e) {
    die("Initialization failed: " . $e->getMessage());
}