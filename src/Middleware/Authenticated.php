<?php

namespace App\Middleware;

use App\Controller\AuthController;
use Dinta\Framework\Http\Response;
use Exception;
use Firebase\JWT\JWT;

class Authenticated
{
    public function only($vars, $args, $handler, ...$rest): Response
    {
        // dd($vars['request']['server']);
        try {
            $http = $vars['request']['server']["HTTP_AUTHORIZATION"];

            if (!isset($http)) {
                throw new Exception("Harap login terlebih dahulu");
            }

            $bearer = preg_replace('/\s+/', '', str_replace("Bearer", "", $http));
            $auth = new AuthController();
            $payload = $auth->validateTokenAccess($bearer)->payload;

            $payload = $auth->auth->findById($payload->id);


            $matches = 0;
            if (isset($args)) {

                if (!isset($payload['roles'])) {
                    throw new Exception("Anda tidak punya akses");
                }


                foreach ($args as $role) {
                    // dd(in_array($role, $payload->roles));
                    if (in_array($role, $payload['roles'])) {
                        $matches += 1;
                    }
                }
            } else {
                $matches += 1;
            }

            if ($matches == 0) {
                throw new Exception("Anda tidak punya akses");
            }

            [$controller, $method] = $handler;

            return call_user_func_array([new $controller, $method], $vars);
        } catch (\Throwable $th) {
            $content = json_encode([
                'success' => false,
                'message' => $th->getMessage()
            ]);
            return new Response($content, 403);
        }
    }
}
