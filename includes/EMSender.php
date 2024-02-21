<?php 

namespace PdfSender;

/**
 * Для отправки вложений используем PHPmailer
 * установка: composer require phpmailer/phpmailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

class EMSender {


    function sendFile($email, $subject, $body, $file, $file_realname) : array{

        global $mailUsername, $mailPassword,$mailSenderEmail, $mailSenderName;
        
        try {
            $mail = new PHPMailer();
            $mail->CharSet = "utf-8";
            $mail->isSMTP();                   // Отправка через SMTP
            $mail->Host   = 'smtp.mail.ru';  // Адрес SMTP сервера
            $mail->SMTPAuth   = true;          // Enable SMTP authentication
            $mail->Username   = $mailUsername;       // ваше имя пользователя (без домена и @)
            $mail->Password   = $mailPassword;    // ваш пароль
            $mail->SMTPSecure = 'ssl';         // шифрование ssl
            $mail->Port   = 465;               // порт подключения
             
            $mail->setFrom($mailSenderEmail, $mailSenderName);    // от кого
            
            $mail->addAddress($email, $email);            // Добавить получателя
        
            // Вложения
            $mail->addAttachment($file, $file_realname);   // Добавить вложения
        
            // Содержимое
            $mail->isHTML(true);                                        // Установить формат письма в HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            //$mail->AltBody = 'Это тело письма для клиентов, не поддерживающих HTML';
        
            $mail->send();
            $result = [ 'result'=> true, 'error' => '' ];
        } catch (Exception $e) {
            $result = [ 'result'=> false, 'error' => "Сообщение не было отправлено. Ошибка Mailer: {$mail->ErrorInfo}" ]; 
        }

        return $result;
    }

}

?>