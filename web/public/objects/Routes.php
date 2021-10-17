<?php

namespace pavel\users;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Routes
{
    static RouteCollection $routeCollection;
    static RequestContext $context;
    static UrlMatcher $matcher;

    static function init()
    {
        try {
            // Инициализация маршрутов
            $users_route = new Route('/users');
            $css_route = new Route('/users/main.css');
            $choice_route = new Route('/users/choice');
            $users_id_route = new Route('/users/{id}', array(), array('id' => '[0-9]+'));
            $users_confirm_route = new Route('/users/activation');
            $users_chat_route = new Route('/users/chat');
            $jquery_route = new Route('/users/jquery');
            $main_js_route = new Route('/users/main.js');
            $users_auth_route = new Route('/users/auth');
            $users_signin_route = new Route('/users/signin');
            $users_signup_route = new Route('/users/signup');
            $users_register_route = new Route('/users/register');
            $users_profile_route = new Route('/users/profile');
            $users_logout_route = new Route('/users/logout');

            self::$routeCollection = new RouteCollection();
            self::$routeCollection->add('users_route', $users_route);
            self::$routeCollection->add('users_id_route', $users_id_route);
            self::$routeCollection->add('users_confirm_route', $users_confirm_route);
            self::$routeCollection->add('users_chat', $users_chat_route);
            self::$routeCollection->add('css_route', $css_route);
            self::$routeCollection->add('choice_route', $choice_route);
            self::$routeCollection->add('users_auth', $users_auth_route);
            self::$routeCollection->add('jquery_route', $jquery_route);
            self::$routeCollection->add('main_js_route', $main_js_route);
            self::$routeCollection->add('users_signin_route', $users_signin_route);
            self::$routeCollection->add('users_register_route', $users_register_route);
            self::$routeCollection->add('users_signup_route', $users_signup_route);
            self::$routeCollection->add('users_profile_route', $users_profile_route);
            self::$routeCollection->add('users_logout_route', $users_logout_route);

            self::$context = new RequestContext();
            self::$context->fromRequest(Request::createFromGlobals());

            self::$matcher = new UrlMatcher(self::$routeCollection, self::$context);
        } catch (ResourceNotFoundException $e) {
            Users::jsonAnswer(['error' => $e->getMessage()], 404);
            die();
        }
    }

    static function getParameters(): array
    {
        return self::$matcher->match(self::$context->getPathInfo());
    }
}
