<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Auth;
use App\Model\User;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Exception;
use stdClass;

class AuthController
{
    public Auth $auth;
    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function validateTokenAccess($token, ...$args): stdClass | Exception
    {
        try {
            if (!isset($token)) {
                throw new Exception("Invalid token");
            }

            $token = JWT::decode($token, new Key($this->auth->secretKeyAccess, $this->auth->algo));
            $now = new DateTimeImmutable();

            if (
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp()
            ) {
                throw new Exception("Token Invalid");
            }

            return $token;
        } catch (\Throwable $th) {
            // throw $th;
            throw new Exception("Token Invalid");
        }
    }

    public function generateAccessToken($postBody, ...$args): Response
    {
        $invalid = json_encode([
            'success' => false,
            'message' => 'Refresh token invalid'
        ]);

        $bodyParser = (new class()
        {
            public string $token;
        });

        try {
            new JsonParser($bodyParser, $postBody);

            $token = JWT::decode($bodyParser->token, new Key($this->auth->secretKeyRefresh, $this->auth->algo));
            $now = new DateTimeImmutable();

            if (
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp()
            ) {
                return new Response($invalid, 401);
            }

            $payload = $this->auth->findById($token->userId);

            $response = json_encode([
                'success' => true,
                'result' => $this->auth->generateAccessToken($payload)
            ]);
            return new Response($response, 201);
        } catch (\Throwable $th) {
            return new Response($invalid, 403);
        }
    }

    public function login($postBody, ...$args): Response
    {
        try {
            $credentials = (new class()
            {
                public string $email;
                public string $password;
            });
            new JsonParser($credentials, $postBody);

            if (!$this->auth->validate($credentials->email, $credentials->password)) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->auth->errors
                ]);
                return new Response($content, 400);
            }


            $password_hash = $this->auth->query("SELECT password FROM {$this->auth->table_name} WHERE email = '{$credentials->email}'");

            if (!$password_hash) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Akun yang Anda maksud tidak terdaftar"
                ]);
                return new Response($content, 400);
            }

            #dd($password_hash['password']);

            if (!password_verify($credentials->password, $password_hash['password'])) {
                $content = json_encode([
                    'success' => false,
                    'message' => array("password" => "Password yang Anda masukkan salah")
                ]);
                return new Response($content, 400);
            }

            $result = $this->auth->login($credentials->email);

            $content = json_encode([
                'success' => true,
                'result' => $result
            ]);

            return new Response($content, 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
