<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use App\Utility\Random;
use Exception;
use PDO;

class Menu extends Database
{
    public string $table_name = "menu";
    public ?string $id;
    public ?string $mitra_id;
    public ?string $name;
    public ?string $picture;
    public ?int $qty;
    public ?int $price;
    public ?string $desc;
    public ?string $category;
    public ?int $disable;
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
        $this->name = null;
        $this->mitra_id = null;
        $this->name = null;
        $this->picture = null;
        $this->qty = null;
        $this->price = null;
        $this->desc = null;
        $this->category = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->resetValidation();
    }

    public function validate($at = null): bool
    {
        $this->resetValidation();
        if (!isset($at) || (isset($at) && $at === "mitra_id")) {
            if (!isset($this->mitra_id)  || isset($this->mitra_id) && strlen($this->mitra_id) <= 0) {
                $this->errors['mitra_id'] = "Harap isi mitra_id";
            }
        }


        if (!isset($at) || (isset($at) && $at === "name")) {
            if (!isset($this->name)) {
                $this->errors['name'] = "Harap isi nama";
            } else if (strlen($this->name) < 3) {
                $this->errors['name'] = "Nama minimal terdiri dari 3 karakter";
            }
        }

        if (!isset($at) || (isset($at) && $at === "qty")) {
            if (!isset($this->qty) || isset($this->qty) && $this->qty <= 0) {
                $this->errors['qty'] = "Harap isi kuantitas/stok";
            }
        }

        if (!isset($at) || (isset($at) && $at === "price")) {
            if (!isset($this->price) || isset($this->price) && $this->price <= 0) {
                $this->errors['price'] = "Harap isi harga";
            }
        }

        if (!isset($at) || (isset($at) && $at === "category")) {
            if (!isset($this->category)) {
                $this->errors['category'] = "Harap isi kategori";
            } else if (strlen($this->category) < 3) {
                $this->errors['category'] = "Kategori minimal terdiri dari 3 karakter";
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
            $result = $this->singleQuery("SELECT _.*, __.name as mitra FROM {$this->table_name} _ INNER JOIN mitra __ ON __.id = _.mitra_id WHERE _.id = '{$id}'");
            if (isset($result['price'])) {
                $result['price'] = (int) $result['price'];
            }
            if (isset($result['qty'])) {
                $result['qty'] = (int) $result['qty'];
            }
            if (isset($result['disable'])) {
                $result['disable'] = (int) $result['disable'] == 0 ? false : true;
            }
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function findAll(): array | Exception
    {
        try {
            $result =  $this->allQuery("SELECT _.*, __.name as mitra FROM {$this->table_name} _ INNER JOIN mitra __ ON __.id = _.mitra_id");
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
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(): Exception | array | null
    {
        try {
            if (!$this->validate()) {
                throw new Exception("Tidak Valid");
            }

            $uuid = (new Random())->uuidv4();

            $this->picture = isset($this->picture) ? "'{$this->picture}'" : 'NULL';
            $this->disable = isset($this->disable) ? $this->disable : 0;
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = $this->created_at;

            $sql = "INSERT INTO {$this->table_name} VALUES ('{$uuid}', '{$this->mitra_id}','{$this->name}', {$this->picture}, {$this->qty}, '{$this->price}', '{$this->desc}', '{$this->category}', {$this->disable}, '{$this->created_at}', '{$this->updated_at}')";

            // dd($sql);
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
            if (isset($this->mitra_id)) {
                $columns .= "`mitra_id` = '{$this->mitra_id}', ";
            }
            if (isset($this->name)) {
                $columns .= "`name` = '{$this->name}', ";
            }
            if (isset($this->picture)) {
                $columns .= "`picture` = '{$this->picture}', ";
            }
            if (isset($this->qty)) {
                $columns .= "`qty` = {$this->qty}, ";
            }
            if (isset($this->price)) {
                $columns .= "`price` = '{$this->price}', ";
            }
            if (isset($this->desc)) {
                $columns .= "`desc` = '{$this->desc}', ";
            }
            if (isset($this->category)) {
                $columns .= "`category` = '{$this->category}', ";
            }
            if (isset($this->disable)) {
                $columns .= "`disable` = {$this->disable}, ";
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
