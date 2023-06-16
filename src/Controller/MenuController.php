<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Menu;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class MenuController
{
    private Menu $menu;
    public function __construct()
    {
        $this->menu = new Menu();
    }
    public function index(...$args): Response
    {
        $result = json_encode([
            'success' => true,
            'result' => $this->menu->findAll()
        ]);

        return new Response($result);
    }

    public function custom($query, ...$args): Response
    {
        // dd(urldecode($query));
        $result = $this->menu->allQuery(urldecode($query));
        for ($i = 0; $i < count($result); $i++) {
            if (isset($result[$i]['qty'])) {
                $result[$i]['qty'] = (int) $result[$i]['qty'];
            }
            if (isset($result[$i]['price'])) {
                $result[$i]['price'] = (int) $result[$i]['price'];
            }
            if (isset($result[$i]['disable'])) {
                $result[$i]['disable'] = $result[$i]['disable'] == 0 ? false : true;
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
        $result = $this->menu->findById($id);
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function store($postBody, ...$args): Response
    {
        try {
            $this->menu = new Menu();
            new JsonParser($this->menu, $postBody);

            if (!$this->menu->validate()) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->menu->errors
                ]);
                return new Response($content, 400);
            }

            // if ($this->menu->baseQuery("SELECT * FROM {$this->menu->table_name} WHERE name = '{$this->menu->name}'")->rowCount() > 0) {
            //     $content = json_encode([
            //         'success' => false,
            //         'message' => ([
            //             "name" => 'Nama sudah dipakai!'
            //         ]),
            //     ]);
            //     return new Response($content, 400);
            // }

            $result = $this->menu->create();

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
            $this->menu = new Menu();
            new JsonParser($this->menu, $postBody);

            // dd($postBody);

            if (!$this->menu->id) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Id tidak terdefinisi!"
                ]);
                return new Response($content, 400);
            }


            if ($this->menu->baseQuery("SELECT * FROM {$this->menu->table_name} WHERE id = '{$this->menu->id}'")->rowCount() <= 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Menu yang dimaksud tidak ditemukan"
                ]);
                return new Response($content, 400);
            }

            $result = $this->menu->update();

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
