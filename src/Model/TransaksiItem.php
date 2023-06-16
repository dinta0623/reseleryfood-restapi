<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use App\Utility\Random;
use App\Model\Menu;
use Exception;
use PDO;
use PDOStatement;

class TransaksiItem extends Database
{
    public string $table_name = "transaksi_item";
    public ?string $id;
    public ?string $transaksi_id;
    public ?string $menu_id;
    public ?int $qty;
    public ?string $total;
    public ?string $description;
    public ?string $created_at;
    public ?string $updated_at;
    public array $errors = [];

    public function __construct()
    {
        $this->clean();
        // $this->getConnection()->query("CREATE TABLE IF NOT EXISTS {$this->table_name} ")->execute();
    }

    public function resetValidation(): void
    {
        $this->errors = [];
    }

    public function clean(): void
    {
        $this->id = null;
        $this->transaksi_id = null;
        $this->menu_id = null;
        $this->qty = null;
        $this->total = null;
        $this->description = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->resetValidation();
    }

    public function validate($at = null): bool
    {
        $this->resetValidation();
        if (!isset($at) || (isset($at) && $at === "transaksi_id")) {
            if (!isset($this->transaksi_id)  || isset($this->transaksi_id) && strlen($this->transaksi_id) <= 0) {
                $this->errors['transaksi_id'] = "Harap isi transaksi_id";
            }
        }

        if (!isset($at) || (isset($at) && $at === "menu_id")) {
            if (!isset($this->menu_id)  || isset($this->menu_id) && strlen($this->menu_id) <= 0) {
                $this->errors['menu_id'] = "Harap isi menu_id";
            }
        }

        if (!isset($at) || (isset($at) && $at === "qty")) {
            if (!isset($this->qty)  || isset($this->qty) && $this->qty <= 0) {
                $this->errors['qty'] = "Harap isi qty";
            }
        }
        if (!isset($at) || (isset($at) && $at === "total")) {
            if (!isset($this->total)  || isset($this->total) && strlen($this->total) <= 0) {
                $this->errors['total'] = "Harap isi total";
            }
        }

        if (isset($this->errors) && count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    public function findById(string $id): null | array | Exception
    {
        try {
            $result = $this->singleQuery("SELECT * FROM {$this->table_name} WHERE id = '{$id}'");
            if (isset($result['total'])) {
                $result['total'] = (int) $result['total'];
            }
            if (isset($result['qty'])) {
                $result['qty'] = (int) $result['qty'];
            }
            $menu = (new Menu())->findById($result['menu_id']);
            $result['menu'] = $menu;
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function findAll(): array | Exception
    {
        try {
            $result =  $this->allQuery("SELECT * FROM {$this->table_name}");
            for ($i = 0; $i < count($result); $i++) {
                if (isset($result[$i]['total'])) {
                    $result[$i]['total'] = (int) $result[$i]['total'];
                }
                if (isset($result[$i]['qty'])) {
                    $result[$i]['qty'] = (int) $result[$i]['qty'];
                }
                $menu = (new Menu())->findById($result[$i]['menu_id']);
                $result[$i]['menu'] = $menu;
            }
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function findAllByTransaction(string $transaction_id): array | Exception | null
    {
        try {
            $result =  $this->allQuery("SELECT * FROM {$this->table_name} WHERE `transaksi_id` = '{$transaction_id}'");
            for ($i = 0; $i < count($result); $i++) {
                if (isset($result[$i]['total'])) {
                    $result[$i]['total'] = (int) $result[$i]['total'];
                }
                if (isset($result[$i]['qty'])) {
                    $result[$i]['qty'] = (int) $result[$i]['qty'];
                }
                $menu = (new Menu())->findById($result[$i]['menu_id']);
                $result[$i]['menu'] = $menu;
            }
            return $result;
        } catch (\Throwable $th) {
            // throw $th;
            return null;
        }
    }

    public function create(): Exception | array | null | string
    {
        try {
            if (!$this->validate()) {
                throw new Exception("Tidak Valid");
            }

            $uuid = (new Random())->uuidv4();

            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = $this->created_at;

            $sql = "INSERT INTO {$this->table_name} VALUES ('{$uuid}', '{$this->transaksi_id}', '{$this->menu_id}', {$this->qty}, '{$this->total}', '{$this->description}', '{$this->created_at}', '{$this->updated_at}')";

            // // dd($sql);
            $this->clean();

            if ($this->baseQuery($sql)) {
                return $this->findById($uuid);
            }
            return null;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(): Exception | array | null
    {
        $id = $this->id;
        $columns = null;
        try {

            if (!isset($id)) {
                throw new Exception("Id tidak Valid");
            }
            if (isset($this->qty)) {
                $columns .= "`qty` = {$this->qty}, ";
            }
            if (isset($this->total)) {
                $columns .= "`total` = '{$this->total}', ";
            }
            if (isset($this->description)) {
                $columns .= "`description` = '{$this->description}', ";
            }

            $this->updated_at = date('Y-m-d H:i:s');
            $columns .=  "`updated_at` = '{$this->updated_at}'";

            $sql = "UPDATE {$this->table_name} SET {$columns} WHERE id = '{$id}'";

            // dd($sql);
            $this->clean();

            if ($this->baseQuery($sql)) {
                return $this->findById($id);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        // throw new Exception("Tidak Valid");
    }
}
