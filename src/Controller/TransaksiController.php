<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class TransaksiController
{
    private User $user;
    public function __construct()
    {
        $this->user = new User();
    }
    public function index(...$args): Response
    {
        $result = json_encode([
            'success' => true,
            'result' => $this->user->findAll()
        ]);

        return new Response($result);
    }

    public function find($id, ...$args): Response
    {
        // dd($id, gettype($this->user->findById("")));
        $result = $this->user->findById($id);
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function store($postBody, ...$args): Response
    {
        try {
            $this->user = new User();
            new JsonParser($this->user, $postBody);

            if (!$this->user->validate()) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->user->errors
                ]);
                return new Response($content, 400);
            }

            if ($this->user->baseQuery("SELECT * FROM users WHERE email = '{$this->user->email}'")->rowCount() > 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => ([
                        "email" => 'Email sudah terdaftar!'
                    ]),
                ]);
                return new Response($content, 400);
            }

            $result = $this->user->create();

            $content = json_encode([
                'success' => true,
                'result' => $result,
            ]);

            return new Response($content, 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update($postBody, ...$args): Response
    {
        try {
            $this->user = new User();
            new JsonParser($this->user, $postBody);

            // dd($postBody);

            if (!$this->user->id) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Id tidak terdefinisi!"
                ]);
                return new Response($content, 400);
            }


            if ($this->user->baseQuery("SELECT * FROM users WHERE id = '{$this->user->id}'")->rowCount() <= 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Akun yang dimaksud tidak terdaftar"
                ]);
                return new Response($content, 400);
            }

            $result = $this->user->update();

            // dd($result);

            $content = json_encode([
                'success' => true,
                'result' => $result,
            ]);

            return new Response($content, 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
