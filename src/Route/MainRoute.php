<?php

declare(strict_types=1);

namespace App\Route;

use App\Controller\HomeController;
use App\Controller\UserController;
use App\Controller\AuthController;
use App\Controller\MenuController;
use App\Controller\MitraController;
use App\Controller\TransaksiController;
use App\Controller\TransaksiItemController;
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
        $this->route->addRoute('GET', '/users/q/{query}', [UserController::class, 'custom', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/users/{id}', [UserController::class, 'find', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/users', [UserController::class, 'update', [Authenticated::class, 'only']]);

        //mitra
        $this->route->addRoute('GET', '/mitra', [MitraController::class, 'index']);
        $this->route->addRoute('GET', '/mitra/q/{query}', [MitraController::class, 'custom']);
        $this->route->addRoute('GET', '/mitra/{id}', [MitraController::class, 'find']);
        $this->route->addRoute('POST', '/mitra', [MitraController::class, 'store', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/mitra', [MitraController::class, 'update', [Authenticated::class, 'only']]);

        //menu
        $this->route->addRoute('GET', '/menu', [MenuController::class, 'index']);
        $this->route->addRoute('GET', '/menu/q/{query}', [MenuController::class, 'custom']);
        $this->route->addRoute('GET', '/menu/{id}', [MenuController::class, 'find']);
        $this->route->addRoute('POST', '/menu', [MenuController::class, 'store', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/menu', [MenuController::class, 'update', [Authenticated::class, 'only']]);

        //transaksi
        $this->route->addRoute('GET', '/transaksi', [TransaksiController::class, 'index', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/transaksi/{id}', [TransaksiController::class, 'find', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/transaksi/q/{query}', [TransaksiController::class, 'custom', [Authenticated::class, 'only']]);
        $this->route->addRoute('POST', '/transaksi', [TransaksiController::class, 'store', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/transaksi', [TransaksiController::class, 'update', [Authenticated::class, 'only']]);

        //transaksi
        $this->route->addRoute('GET', '/transaksi-item', [TransaksiItemController::class, 'index', [Authenticated::class, 'only']]);
        $this->route->addRoute('GET', '/transaksi-item/{id}', [TransaksiItemController::class, 'find', [Authenticated::class, 'only']]);
        $this->route->addRoute('POST', '/transaksi-item', [TransaksiItemController::class, 'store', [Authenticated::class, 'only']]);
        $this->route->addRoute('PUT', '/transaksi-item', [TransaksiItemController::class, 'update', [Authenticated::class, 'only']]);
    }
}
