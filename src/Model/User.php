<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use App\Utility\Random;
use Exception;
use PDO;

class User extends Database
{
    public string $table_name = "users";
    public ?string $id;
    public ?string $name;
    public ?string $avatar;
    public ?string $email;
    public ?string $password;
    public ?array $roles;
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
        $this->avatar = null;
        $this->email = null;
        $this->password = null;
        $this->roles = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->resetValidation();
    }

    public function setValues($name, $avatar, $email, $password, $roles, $created_at, $updated_at): void
    {
        $this->name = $name;
        $this->avatar = $avatar;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function validate($at = null): bool
    {
        $this->resetValidation();

        if (!isset($at) || (isset($at) && $at === "name")) {
            if (!isset($this->name)) {
                $this->errors['name'] = "Harap isi nama";
            } else if (strlen($this->name) < 3) {
                $this->errors['name'] = "Nama minimal terdiri dari 3 karakter";
            }
        }

        if (!isset($at) || (isset($at) && $at === "password")) {
            if (!isset($this->password)) {
                $this->errors['password'] = "Harap isi password";
            } else if (strlen($this->password) < 8) {
                $this->errors['password'] = "Password minimal terdiri dari 8 karakter";
            }
        }

        if (!isset($at) || (isset($at) && $at === "email")) {
            if (!isset($this->email)) {
                $this->errors['email'] = "Harap isi email";
            } else if (strlen($this->email) < 1) {
                $this->errors['email'] = "Harap isi email";
            } else if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
                $this->errors['email'] = "Email yang Anda masukkan tidak valid";
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

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function findAll(): array | Exception
    {
        try {
            $result =  $this->allQuery("SELECT * FROM {$this->table_name}");
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

            $password = password_hash($this->password, PASSWORD_BCRYPT,  ['cost' => 10]);

            $this->avatar = isset($this->avatar) ? "'{$this->avatar}'" : 'NULL';
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = $this->created_at;

            $roles = 'NULL';
            if (isset($this->roles) && count($this->roles) > 0) {
                $roles = implode(",", $this->roles);
                $roles = "'{$roles}'";
            }

            $sql = "INSERT INTO {$this->table_name} VALUES ('{$uuid}', '{$this->name}', '{$this->email}', {$this->avatar}, '{$password}', {$roles}, NULL, NULL, '{$this->created_at}', '{$this->updated_at}')";

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
            if (isset($this->avatar)) {
                $columns .= "`avatar` = '{$this->avatar}', ";
            }
            if (isset($this->email)) {
                if (!$this->validate("email")) {
                    throw new Exception("Tidak Valid");
                }
                $columns .= "`email` = '{$this->email}', ";
            }
            if (isset($this->password)) {
                $this->password = password_hash($this->password, PASSWORD_BCRYPT,  ['cost' => 10]);
                $columns .= "`password` = '{$this->password}', ";
            }
            if (isset($this->roles)) {
                if (count($this->roles) > 0) {
                    $roles = implode(",", $this->roles);
                    $roles = "'{$roles}'";
                    $columns .= "`roles` = {$roles}, ";
                } else {
                    $roles = 'NULL';
                    $columns .= "`roles` = {$roles}, ";
                }
            }
            $this->updated_at = date('Y-m-d H:i:s');

            // $this->avatar = isset($this->avatar) ? "'{$this->avatar}'" : 'NULL';


            // $columns .= "avatar = {$this->avatar}, ";
            $columns .=  "`updated_at` = '{$this->updated_at}'";


            $sql = "UPDATE {$this->table_name} SET {$columns} WHERE id = '{$id}'";

            // dd($sql);

            $this->clean();

            if ($this->baseQuery($sql)) {
                return $this->findById($id); //("SELECT * FROM {$this->table_name} WHERE id = '{$id}'");
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        // throw new Exception("Tidak Valid");
    }
}
