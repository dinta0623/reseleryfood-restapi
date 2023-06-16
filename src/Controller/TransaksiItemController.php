<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\TransaksiItem;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class TransaksiItemController
{
    private TransaksiItem $transaksi_item;
    public function __construct()
    {
        $this->transaksi_item = new TransaksiItem();
    }
    public function index(...$args): Response
    {
        $result = json_encode([
            'success' => true,
            'result' => $this->transaksi_item->findAll()
        ]);

        return new Response($result);
    }

    public function find($id, ...$args): Response
    {
        // dd($id, gettype($this->transaksi_item->findById("")));
        $result = $this->transaksi_item->findById($id);
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function store($postBody, ...$args): Response
    {
        try {
            $this->transaksi_item = new TransaksiItem();
            new JsonParser($this->transaksi_item, $postBody);

            if (!$this->transaksi_item->validate()) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->transaksi_item->errors
                ]);
                return new Response($content, 400);
            }

            $result = $this->transaksi_item->create();

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
            $this->transaksi_item = new TransaksiItem();
            new JsonParser($this->transaksi_item, $postBody);

            // dd($postBody);

            if (!$this->transaksi_item->id) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Id tidak terdefinisi!"
                ]);
                return new Response($content, 400);
            }


            if ($this->transaksi_item->baseQuery("SELECT * FROM `{$this->transaksi_item->table_name}` WHERE `id` = '{$this->transaksi_item->id}'")->rowCount() <= 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Transaksi item yang dimaksud tidak terdaftar"
                ]);
                return new Response($content, 400);
            }

            $result = $this->transaksi_item->update();

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
