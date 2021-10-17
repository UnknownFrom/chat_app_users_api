<?php

session_start();

use pavel\users\Users;
use pavel\users\Routes;
use pavel\connect\Database;
use pavel\users\ActivationEmail;

include "../app/vendor/autoload.php";

try {
    Routes::init();
    $parameters = Routes::getParameters();

    // подключение к базе данных
    $db = Database::connect();

    switch ($parameters['_route']) {
        case 'css_route':
            require_once '../assets/css/main.css';
            return;
        case 'choice_route':
            if (isset($_SESSION['user'])) {
                header('Location: ./profile');
            }
            require_once '../Templates/index-template.php';
            return;
        case 'users_auth':
            if (isset($_SESSION['user'])) {
                header('Location: ./profile');
            }
            require_once '../Templates/auth-template.php';
            return;
        case 'jquery_route':
            require_once '../assets/js/http_code.jquery.com_jquery-3.6.0.js';
            return;
        case 'main_js_route':
            require_once '../assets/js/main.js';
            return;
        case 'users_signin_route':
            Users::getUser($db, $_POST);
            return;
        case 'users_signup_route':
            Users::setUser($db, $_POST);
            return;
        case 'users_register_route':
            if (isset($_SESSION['user'])) {
                header('Location: ./profile');
            }
            require_once '../Templates/register-template.php';
            return;
        case 'users_profile_route':
            require_once '../Templates/profile-template.php';
            return;
        case 'users_logout_route':
            unset($_SESSION['user']);
            header('Location: ./auth');
            return;
        case 'users_confirm_route':
            ActivationEmail::activationEmail($db, $_GET['token']);
            return;
    }
} catch (Exception $e) {
    Users::jsonAnswer(['error' => $e->getMessage()], 404);
    die();
}