<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Mitra;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class MitraController
{
    private Mitra $mitra;
    public function __construct()
    {
        $this->mitra = new Mitra();
    }
    public function index(...$args): Response
    {
        $result = json_encode([
            'success' => true,
            'result' => $this->mitra->findAll()
        ]);

        return new Response($result);
    }

    public function custom($query, ...$args): Response
    {
        // dd(urldecode($query));
        $result = $this->mitra->allQuery(urldecode($query));
        for ($i = 0; $i < count($result); $i++) {
            if (isset($result[$i]['disable'])) {
                $result[$i]['disable'] = $result[$i]['disable'] == 0 ? false : true;
            }
            if (isset($result[$i]['is_open'])) {
                $result[$i]['is_open'] = $result[$i]['is_open'] == 0 ? false : true;
            }
        }
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }
    public function find($id, ...$args): Response
    {
        $result = $this->mitra->findById($id);
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function store($postBody, ...$args): Response
    {
        try {
            $this->mitra = new Mitra();
            new JsonParser($this->mitra, $postBody);

            if (!$this->mitra->validate()) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->mitra->errors
                ]);
                return new Response($content, 400);
            }

            if ($this->mitra->baseQuery("SELECT * FROM {$this->mitra->table_name} WHERE name = '{$this->mitra->name}'")->rowCount() > 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => ([
                        "name" => 'Nama sudah dipakai!'
                    ]),
                ]);
                return new Response($content, 400);
            }

            $result = $this->mitra->create();

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
            $this->mitra = new Mitra();
            new JsonParser($this->mitra, $postBody);

            // dd($postBody);

            if (!$this->mitra->id) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Id tidak terdefinisi!"
                ]);
                return new Response($content, 400);
            }


            if ($this->mitra->baseQuery("SELECT * FROM {$this->mitra->table_name} WHERE id = '{$this->mitra->id}'")->rowCount() <= 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Mitra yang dimaksud tidak terdaftar"
                ]);
                return new Response($content, 400);
            }

            $result = $this->mitra->update();

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
