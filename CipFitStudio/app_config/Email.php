<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    
    private static function configure() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USER'] ?? '';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($_ENV['MAIL_USER'] ?? '', 'CipFit Studio');
        return $mail;
    }
    
    public static function sendVerificationEmail($email, $name, $token) {
        try {
            $mail = self::configure();
            
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Activare cont CipFit Studio';
            
            $base_url = $_ENV['BASE_URL'];
            $activation_link = "$base_url/auth/verify-email.php?token=$token";
            
            $mail->Body = "
                <h1>Salut, $name!</h1>
                <p>Click pe butonul de mai jos pentru a-ți activa contul:</p>
                <p><a href='$activation_link' style='background: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Activează contul</a></p>
            ";
            $mail->AltBody = "Salut $name! Copiază acest link în browser pentru activare: $activation_link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function sendPasswordResetEmail($email, $name, $token) {
        try {
            $mail = self::configure();
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Resetare parola CipFit Studio';
            $base_url = $_ENV['BASE_URL'] ?? 'http://localhost/CipFitStudio';
            $reset_link = "$base_url/auth/resetPassword.php?token=$token";
            $mail->Body = "<h1>Salut, $name!</h1><p>Click pe butonul de mai jos pentru a-ți reseta parola:</p><p><a href='$reset_link' style='background: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Resetează parola</a></p>";
            $mail->AltBody = "Salut $name! Copiază acest link în browser pentru resetare: $reset_link";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendContactEmail($nume, $email, $subiect, $mesaj) {
        try {
            $mail = self::configure();
            // Adresa la care ajung mesajele de contact
            $mail->addAddress($_ENV['MAIL_USER'], 'CipFit Studio');
            $mail->isHTML(true);
            $mail->Subject = 'Contact CipFit Studio: ' . $subiect;
            $mail->Body = '<h2>Mesaj de contact de la: ' . htmlspecialchars($nume) . ' (' . htmlspecialchars($email) . ')</h2>' .
                '<p><b>Subiect:</b> ' . htmlspecialchars($subiect) . '</p>' .
                '<p><b>Mesaj:</b><br>' . nl2br(htmlspecialchars($mesaj)) . '</p>';
            $mail->AltBody = "Mesaj de contact de la $nume ($email)\nSubiect: $subiect\nMesaj: $mesaj";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email contact error: " . $e->getMessage());
            return false;
        }
    }
}
