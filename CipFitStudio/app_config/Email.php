<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    
    private static function configure() {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ciprianvlad02@gmail.com';
        $mail->Password = 'empx jhmg dcxw tcrz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('ciprianvlad02@gmail.com', 'CipFit Studio');
        
        return $mail;
    }
    
    public static function sendVerificationEmail($email, $name, $token) {
        try {
            $mail = self::configure();
            
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Activare cont CipFit Studio';
            
            $activation_link = "http://localhost/CipFitStudio/auth/verify-email.php?token=$token";
            
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
            $reset_link = "http://localhost/CipFitStudio/auth/resetPassword.php?token=$token";
            $mail->Body = "<h1>Salut, $name!</h1><p>Click pe butonul de mai jos pentru a-ți reseta parola:</p><p><a href='$reset_link' style='background: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Resetează parola</a></p>";
            $mail->AltBody = "Salut $name! Copiază acest link în browser pentru resetare: $reset_link";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}
