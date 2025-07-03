<?php
define ('DB_HOST', 'localhost');
define ('DB_USER', 'root');
define ('DB_PASS', '');
define ('DB_NAME', 'bank_app');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_activation_email($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.poczta.onet.pl'; // SMTP serwer
        $mail->SMTPAuth = true;
        $mail->Username = 'your email';
        $mail->Password = 'passwd';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('krzysztof12.pl@onet.pl', 'Bank App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Aktywacja konta';
        $mail->Body = "Kliknij w link aby aktywowac konto: <a href='http://localhost/aplikacja_bankowa/activate.php?token=$token'>Aktywuj konto</a>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
    }
}
