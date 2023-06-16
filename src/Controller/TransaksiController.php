<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Transaksi;
use App\Model\Mitra;
use App\Model\TransaksiItem;
use App\Utility\JsonParser;
use Dinta\Framework\Http\Response;

class TransaksiController
{
    private Transaksi $transaksi;
    public function __construct()
    {
        $this->transaksi = new Transaksi();
    }
    public function index(...$args): Response
    {
        $result = json_encode([
            'success' => true,
            'result' => $this->transaksi->findAll()
        ]);

        return new Response($result);
    }

    public function find($id, ...$args): Response
    {
        // dd($id, gettype($this->transaksi->findById("")));
        $result = $this->transaksi->findById($id);
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function custom($query, ...$args): Response
    {
        // dd(urldecode($query));
        $result = $this->transaksi->allQuery(urldecode($query));
        for ($i = 0; $i < count($result); $i++) {
            if (isset($result[$i]['diskon'])) {
                $result[$i]['diskon'] = (int) $result[$i]['diskon'];
            }
            if (isset($result[$i]['total'])) {
                $result[$i]['total'] = (int) $result[$i]['total'];
            }
            if (isset($result[$i]['ongkir'])) {
                $result[$i]['ongkir'] = (int) $result[$i]['ongkir'];
            }
            if (isset($result[$i]['fee'])) {
                $result[$i]['fee'] = (int) $result[$i]['fee'];
            }
            if (isset($result[$i]['isMenu'])) {
                $result[$i]['isMenu'] = (int) $result[$i]['isMenu'] == 0 ? false : true;
            }
            $mitra = (new Mitra())->findById($result[$i]['mitra_id']);
            $result[$i]['mitra'] = $mitra;

            $items = (new TransaksiItem())->findAllByTransaction($result[$i]['id']);
            $result[$i]['items'] = $items;
        }
        $response = json_encode([
            'success' => true,
            'result' => $result
        ]);

        return new Response($response);
    }

    public function store($postBody, ...$args): Response
    {
        try {
            $this->transaksi = new Transaksi();
            new JsonParser($this->transaksi, $postBody);

            if (!$this->transaksi->validate()) {
                $content = json_encode([
                    'success' => false,
                    'message' => $this->transaksi->errors
                ]);
                return new Response($content, 400);
            }

            $result = $this->transaksi->create();

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
            $this->transaksi = new Transaksi();
            new JsonParser($this->transaksi, $postBody);

            // dd($postBody);

            if (!$this->transaksi->id) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Id tidak terdefinisi!"
                ]);
                return new Response($content, 400);
            }


            if ($this->transaksi->baseQuery("SELECT * FROM `{$this->transaksi->table_name}` WHERE `id` = '{$this->transaksi->id}'")->rowCount() <= 0) {
                $content = json_encode([
                    'success' => false,
                    'message' => "Transaksi yang dimaksud tidak terdaftar"
                ]);
                return new Response($content, 400);
            }

            $result = $this->transaksi->update();

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
