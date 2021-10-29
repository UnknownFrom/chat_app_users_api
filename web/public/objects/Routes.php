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
            $users_confirm_route = new Route('/activation');
            $users_signin_route = new Route('/signin');
            $users_signup_route = new Route('/signup');

            self::$routeCollection = new RouteCollection();
            self::$routeCollection->add('users_confirm_route', $users_confirm_route);
            self::$routeCollection->add('users_signin_route', $users_signin_route);
            self::$routeCollection->add('users_signup_route', $users_signup_route);

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
