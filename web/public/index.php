<?php

session_start();

use pavel\users\Users;
use pavel\users\Routes;
use pavel\connect\Database;
use pavel\users\ActivationEmail;

include "../app/vendor/autoload.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

try {
    Routes::init();
    $parameters = Routes::getParameters();

    // подключение к базе данных
    $db = Database::connect();

    switch ($parameters['_route']) {
        case 'users_signin_route':
            Users::getUser($db, $_POST);
            return;
        case 'users_signup_route':
            Users::setUser($db, $_POST);
            return;
        case 'users_confirm_route':
            ActivationEmail::activationEmail($db, $_GET['token']);
            return;
    }
} catch (Exception $e) {
    Users::jsonAnswer(['error' => $e->getMessage()], 404);
    die();
}