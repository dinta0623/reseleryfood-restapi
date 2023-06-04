<?php

declare(strict_types=1);

namespace App\Route;

use App\Controller\HomeController;
use App\Controller\UserController;
use App\Controller\AuthController;
use App\Controller\MitraController;
use App\Middleware\Authenticated;
use App\Model\Auth;
use FastRoute\RouteCollector;

class MainRoute
{
    private RouteCollector $route;

    public function __construct($routeController)
    {
        $this->route = $routeController;
        $this->unauthenticated();
        $this->authenticated();

        $this->route->addRoute('*', '[/{any:.*}]', [HomeController::class, 'error']);
    }

    private function addRoute(string $http, string $path, array $handler): void
    {
    }

    private function unauthenticated(): void
    {


        // $this->addRoute()->
        // home
        $this->route->addRoute('GET', '/', [HomeController::class, 'index']);

        // users
        $this->route->addRoute('POST', '/users', [UserController::class, 'store']);

        // login
        $this->route->addRoute('POST', '/login', [AuthController::class, 'login']);
        $this->route->addRoute('POST', '/token', [AuthController::class, 'generateAccessToken']);
    }

    private function authenticated(): void
    {
        $this->route->addRoute('GET', '/auth', [HomeController::class, 'index', [Authenticated::class, 'only']]);

        // users
        $this->route->addRoute('GET', '/users', [UserController::class, 'index', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/users/{id}', [UserController::class, 'find', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/users', [UserController::class, 'update', [Authenticated::class, 'only']]);

        //mitra
        $this->route->addRoute('GET', '/mitra', [MitraController::class, 'index', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/mitra/q/{query}', [MitraController::class, 'custom', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/mitra/{id}', [MitraController::class, 'find', [Authenticated::class, 'only']]);
        $this->route->addRoute('POST', '/mitra', [MitraController::class, 'store', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/mitra', [MitraController::class, 'update', [Authenticated::class, 'only']]);
    }
}
