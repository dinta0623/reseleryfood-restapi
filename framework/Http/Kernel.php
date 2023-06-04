<?php

declare(strict_types=1);

namespace Dinta\Framework\Http;

use function FastRoute\simpleDispatcher;
use App\Controller\AuthController;
use FastRoute\RouteCollector;
use App\Route\MainRoute;
use Exception;

class Kernel
{
    public function handle(Request $request): Response
    {
        try {
            // dispatcher
            $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
                new MainRoute($routeCollector);
            });

            $routeInfo = $dispatcher->dispatch($request->server['REQUEST_METHOD'], $request->getPathInfo());

            [$status, [$controller, $method, $middleware], $vars] = $routeInfo;

            // body parser (middleware)
            $json = file_get_contents('php://input');
            if (isset($json)) {
                $vars['postBody'] = json_decode($json, true);
            }
            if ($request->server['REQUEST_METHOD'] === "POST" && !isset($vars['postBody'])) {
                return new Response(json_encode([
                    'success' => false,
                    'message' => 'body invalid'
                ]), 400);
            }

            // request
            $vars['request'] = (array) $request;
            $response = null;

            if (isset($middleware)) {
                [$controllerMiddleware, $methodMiddleware, $argsMiddleware] = $middleware;

                $newArgs['args'] = $argsMiddleware;
                $newArgs['vars'] = $vars;
                $newArgs['handler'] = [$controller, $method];
                // dd($newArgs);

                $response = call_user_func_array([new $controllerMiddleware, $methodMiddleware], $newArgs);
            } else {
                $response = call_user_func_array([new $controller, $method], $vars);
            }

            return $response;
        } catch (\Throwable $th) {
            // dd($th);
            $content = json_encode([
                'success' => false,
                'message' => "Internal server error : {$th->getMessage()}"
            ]);
            return new Response($content, 500);
        }
    }
}


 // // check authenticated route (middleware)
            // if (str_contains((string) $request->server["REQUEST_URI"], 'auth')) {
            //     try {
            //         if (!isset($request->server["HTTP_AUTHORIZATION"])) {
            //             throw new Exception("Unauthorized");
            //         }

            //         $bearer = preg_replace('/\s+/', '', str_replace("Bearer", "", $request->server["HTTP_AUTHORIZATION"]));
            //         $auth = new AuthController();
            //         $auth->validateTokenAccess($bearer);
            //     } catch (\Throwable $th) {
            //         $content = json_encode([
            //             'success' => false,
            //             'message' => $th->getMessage()
            //         ]);
            //         return new Response($content, 403);
            //     }
            // }