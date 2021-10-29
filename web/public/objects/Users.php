<?php

namespace pavel\users;

const ERROR_CORRECT_FIELDS = 1;
const ERROR_LOAD_AVATAR = 2;

use PDO;
use \Firebase\JWT\JWT;
use PHPMailer\PHPMailer\Exception;

class Users
{
    // получение пользователя из базы
    static function getUser($connect, $data)
    {
        // проверка на пустые поля
        $error_fields = [];
        if ($data['login'] === '') {
            $error_fields[] = 'login';
        }
        if ($data['password'] === '') {
            $error_fields[] = 'password';
        }
        if (!empty($error_fields)) {
            $response = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Проверьте правильность полей',
                'fields' => $error_fields
            ];
            echo json_encode($response);
            die();
        }

        // проверка подтверждения почты
        $data['password'] = md5($data['password']);
        self::checkEmail($connect, $data);

        // Вытаскиваем пользователя из базы
        $sth = $connect->prepare("SELECT * FROM `users` WHERE `login` = :login AND `password` = :password");
        $sth->execute($data);
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        // если пользователь не найден
        if (!$res) {
            $error_fields[] = 'login';
            $error_fields[] = 'password';
            $response = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Неверный логин или пароль',
                'fields' => $error_fields
            ];
            echo json_encode($response);
            die();
        }

        // создание токена для отправки в ссылке
        $token = array(
            'id' => $res['id'],
            'fullName' => $res['login']
        );
        $jwt = JWT::encode($token, $_ENV['TOKEN_KEY'], 'HS256');

        $response = [
            'status' => true,
            'token' => $jwt
        ];
        echo json_encode($response);
    }

    static function setUser($connect, $data)
    {
        $fullName = $data['fullName'];
        $login = $data['login'];
        $email = $data['email'];
        $password = $data['password'];
        $passwordConfirm = $data['passwordConfirm'];

        self::checkLogin($connect, $login);

        // возвращает названия пустых полей
        $errorFields = self::checkField($data);

        if (!empty($errorFields)) {
            $response = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Проверьте правильность полей',
                'fields' => $errorFields
            ];
            echo json_encode($response);
            die();
        }

        // схожи ли пароли
        if ($password !== $passwordConfirm) {
            $errorFields[] = 'password';
            $errorFields[] = 'passwordConfirm';
            $response = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Пароли не совпадают',
                'fields' => $errorFields
            ];
            echo json_encode($response);
            die();
        }

        $password = md5($password);
        $data = [
            'fullName' => $fullName,
            'login' => $login,
            'email' => $email,
            'password' => $password,
        ];

        // Вставляем пользователя в базу
        $sth = $connect->prepare('INSERT INTO users (id, fullName, login, password, email, confirm) VALUES (NULL, :fullName, :login, :password, :email, FALSE)');
        $sth->execute($data);

        // создание токена
        $token = array(
            "id" => $connect->lastInsertId(),
            "time" => time() + 60
        );
        $jwt = JWT::encode($token, $_ENV['TOKEN_KEY'], 'HS256');

        // отправка письма подтверждения
        ActivationEmail::sendMail($data['email'], $jwt);

        $response = [
            'status' => true,
            'message' => 'Регистрация прошла успешно'
        ];
        echo json_encode($response);
    }

    // удаление пользователя из базы
    static function deleteUser($connect, $id)
    {
        $sth = $connect->prepare("DELETE FROM `users` WHERE `id` = $id");
        $sth->execute();
        // Выводим ответ клиенту
        self::jsonAnswer([], 204);
    }

    static function checkEmail($connect, $data)
    {
        // Вытаскиваем пользователя из базы
        $sth = $connect->prepare("SELECT * FROM `users` WHERE `login` = :login AND `password` = :password AND `confirm` = FALSE");
        $sth->execute($data);
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        // если нашёлся пользователь с неподтвержденной почтой
        if ($res) {
            $res = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Подтвердите почту',
                'fields' => []
            ];
            echo json_encode($res);
            die();
        }
    }

    // проверка на пустые поля
    static function checkField($data): array
    {
        $errorFields = [];

        if ($data['login'] === '') {
            $errorFields[] = 'login';
        }
        if ($data['password'] === '') {
            $errorFields[] = 'password';
        }
        if ($data['fullName'] === '') {
            $errorFields[] = 'fullName';
        }
        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errorFields[] = 'email';
        }
        if ($data['passwordConfirm'] === '') {
            $errorFields[] = 'passwordConfirm';
        }
        return $errorFields;
    }

    // проверка на то, зарегистрирован ли такой логин
    static function checkLogin($connect, $login)
    {
        // Вытаскиваем пользователя из базы
        $sth = $connect->prepare("SELECT * FROM `users` WHERE `login` = :login");
        $sth->execute(['login' => $login]);
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $response = [
                'status' => false,
                'type' => ERROR_CORRECT_FIELDS,
                'message' => 'Такой логин уже существует',
                'fields' => ['login']
            ];
            echo json_encode($response);
            die();
        }
    }

    // ответ с кодом
    static function jsonAnswer($res, $code)
    {
        http_response_code($code);
        if (count($res) > 0) {
            echo json_encode($res);
        }
    }
}
