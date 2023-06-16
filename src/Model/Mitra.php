<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use App\Utility\Random;
use Exception;
use PDO;

class Mitra extends Database
{
    public string $table_name = "mitra";
    public ?string $id;
    public ?string $user_id;
    public ?string $name;
    public ?string $address;
    public ?string $phone;
    public ?string $logo;
    public ?string $lat;
    public ?string $lng;
    public mixed $is_open;
    public mixed $disable;
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
        $this->address = null;
        $this->phone = null;
        $this->logo = null;
        $this->lat = null;
        $this->lng = null;
        $this->is_open = null;
        $this->disable = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->resetValidation();
    }

    public function validate($at = null): bool
    {
        $this->resetValidation();
        if (!isset($at) || (isset($at) && $at === "user_id")) {
            if (!isset($this->user_id)  || isset($this->user_id) && strlen($this->user_id) <= 0) {
                $this->errors['user_id'] = "Harap user_id pic";
            }
        }


        if (!isset($at) || (isset($at) && $at === "name")) {
            if (!isset($this->name)) {
                $this->errors['name'] = "Harap isi nama";
            } else if (strlen($this->name) < 3) {
                $this->errors['name'] = "Nama minimal terdiri dari 3 karakter";
            }
        }

        if (!isset($at) || (isset($at) && $at === "address")) {
            if (!isset($this->address) || isset($this->address) && strlen($this->address) <= 0) {
                $this->errors['address'] = "Harap isi alamat";
            }
        }

        if (!isset($at) || (isset($at) && $at === "phone")) {
            if (!isset($this->phone) || isset($this->phone) && strlen($this->phone) <= 0) {
                $this->errors['phone'] = "Harap isi nomor telepon";
            }
        }

        if (!isset($at) || (isset($at) && $at === "lat")) {
            if (!isset($this->lat) || isset($this->lat) && strlen($this->lat) <= 0) {
                $this->errors['lat'] = "Harap isi latitude";
            }
        }

        if (!isset($at) || (isset($at) && $at === "lng")) {
            if (!isset($this->lng) || isset($this->lng) && strlen($this->lng) <= 0) {
                $this->errors['lng'] = "Harap isi longtitude";
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
            $result = $this->singleQuery("SELECT _.*, __.name as pic FROM {$this->table_name} _ INNER JOIN users __ ON __.id = _.user_id WHERE _.id = '{$id}'");
            if (isset($result['disable'])) {
                $result['disable'] = (int) $result['disable'] == 0 ? false : true;
            }
            if (isset($result['is_open'])) {
                $result['is_open'] = (int) $result['is_open'] == 0 ? false : true;
            }
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
                if (isset($result[$i]['disable'])) {
                    $result[$i]['disable'] = $result[$i]['disable'] == 0 ? false : true;
                }
                if (isset($result[$i]['is_open'])) {
                    $result[$i]['is_open'] = $result[$i]['is_open'] == 0 ? false : true;
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

            $this->baseQuery("UPDATE users SET `roles` = 'mitra' WHERE `id`= '{$this->user_id}'");

            $uuid = (new Random())->uuidv4();

            $this->logo = isset($this->logo) ? "'{$this->logo}'" : 'NULL';
            $this->is_open = isset($this->is_open) ? ($this->is_open == True ? 1 : 0) : 1;
            $this->disable = isset($this->disable) ? ($this->disable == True ? 1 : 0) : 0;
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = $this->created_at;

            $sql = "INSERT INTO {$this->table_name} VALUES ('{$uuid}', '{$this->user_id}','{$this->name}', '{$this->address}', '{$this->phone}', {$this->logo}, '{$this->lat}', '{$this->lng}', {$this->is_open}, {$this->disable}, '{$this->created_at}', '{$this->updated_at}')";

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
            if (isset($this->name)) {
                $columns .= "`name` = '{$this->name}', ";
            }
            if (isset($this->user_id)) {
                $columns .= "`user_id` = '{$this->user_id}', ";
                $this->baseQuery("UPDATE users SET `roles` = 'mitra' WHERE `id`= '{$this->user_id}'");
            }
            if (isset($this->address)) {
                $columns .= "`address` = '{$this->address}', ";
            }
            if (isset($this->phone)) {
                $columns .= "`phone` = '{$this->phone}', ";
            }
            if (isset($this->logo)) {
                $columns .= "`logo` = '{$this->logo}', ";
            }
            if (isset($this->lat)) {
                $columns .= "`lat` = '{$this->lat}', ";
            }
            if (isset($this->lng)) {
                $columns .= "`lng` = '{$this->lng}', ";
            }
            if (isset($this->disable)) {
                $this->disable = $this->disable == True ? 1 : 0;
                $columns .= "`disable` = {$this->disable}, ";
            }
            if (isset($this->is_open)) {
                $this->is_open = $this->is_open == True ? 1 : 0;
                $columns .= "`is_open` = {$this->is_open}, ";
            }

            $this->updated_at = date('Y-m-d H:i:s');
            $columns .=  "updated_at = '{$this->updated_at}'";

            $sql = "UPDATE {$this->table_name} SET {$columns} WHERE id = '{$id}'";

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
