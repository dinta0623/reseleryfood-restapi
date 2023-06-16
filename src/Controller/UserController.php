<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class UserController
{
    private User $user;
    public function __construct()
    {
        $this->user = new User();
    }
    public function index($request, ...$args): Response
    {
        if (isset($request["getParams"]) && count($request["getParams"]) > 0) {
            $query = "";
            foreach (array_keys($request["getParams"]) as $key) {
                if (isset($request["getParams"][$key])) {
                    if (strlen($query) > 0) {
                        $query .= " OR ";
                    }

                    $val = $request["getParams"][$key];

                    if (strlen($val) <= 0) {
                        continue;
                    }

                    if (str_contains($val, 'LIKE')) {
                        $query .= "{$key} {$val}";
                    } else {
                        $query .= "{$key} LIKE {$val}";
                    }
                    // dd($query, $request["getParams"]);
                }
            }

            if (strlen($query) > 0) {
                $query = urldecode($query);
                $query =  $this->user->allQuery("SELECT * FROM {$this->user->table_name} WHERE {$query}");
            } else {
                $query = $this->user->findAll();
            }
            // dd("SELECT * FROM {$this->user->table_name} WHERE {$query}");
            $result = json_encode([
                'success' => true,
                'result' => $query
            ]);
        } else {
            $result = json_encode([
                'success' => true,
                'result' => $this->user->findAll()
            ]);
        }



        return new Response($result);
    }

    public function custom($query, ...$args): Response
    {
        // dd(urldecode($query));
        $result = $this->user->allQuery(urldecode($query));
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
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
