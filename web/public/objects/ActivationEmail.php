<?php

namespace pavel\users;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use PDO;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ActivationEmail
{
    static function sendMail($email, $token)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';

            // Настройки SMTP
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPDebug = 0;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->SMTPSecure = $_ENV['EMAIL_SMTPSECURE'];
            $mail->Mailer = 'smtp';
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->Port = 465;
            $mail->Username = $_ENV['EMAIL_USERNAME'];
            $mail->Password = $_ENV['EMAIL_PASSWORD'];

            // От кого
            $mail->setFrom($_ENV['EMAIL_USERNAME']);

            // Кому
            $mail->addAddress($email);

            // Тема письма
            $mail->Subject = 'Подтвердите регистрацию';
            $base_url = $_ENV['NGINX_HOST'] . '/';

            // Тело письма
            $body = '<strong>Здравствуйте!</strong> <br/> <br/> Мы должны убедиться в том, что вы человек. Пожалуйста, подтвердите адрес вашей электронной почты, и можете начать использовать ваш аккаунт на сайте. <br/> <br/> <a href="http://' . $base_url . 'activation?token=' . $token . '">Ссылка для подтверждения</a>';
            $mail->msgHTML($body);
            $mail->send();
        } catch (Exception $e) {
            Users::jsonAnswer(['error' => $e->getMessage()], 404);
            die();
        }
    }

    static function activationEmail($connect, $token)
    {
        if ($token) {
            try {
                // декодирование токена
                $decoded = JWT::decode($token, $_ENV['TOKEN_KEY'], array('HS256'));

                $sth = $connect->prepare("SELECT * FROM `users` WHERE `id` = :id AND `confirm` = TRUE");
                $sth->execute(['id' => $decoded->id]);

                // если почта подтверждена
                if ($sth->fetch(PDO::FETCH_ASSOC)) {
                    Users::jsonAnswer(['Email already confirmed'], 200);
                    die();
                }

                // если время токена истекло
                if ($decoded->time < time()) {
                    Users::jsonAnswer(['Link expired'], 404);
                    Users::deleteUser($connect, $decoded->id);
                    die();
                }

                // подтверждение почты
                $sth = $connect->prepare("UPDATE `users` SET `confirm`= TRUE WHERE `id` = :id AND `confirm` = 0");
                $sth->execute(['id' => $decoded->id]);
                Users::jsonAnswer(['Email confirmed'], 200);
            } catch (ExpiredException $e) {
                Users::jsonAnswer(['error' => $e->getMessage()], 404);
                die();
            }
        }
    }
}
