<?php

declare(strict_types=1);

namespace App\Config;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database
{

    private $host = "127.0.0.1";
    private $database_name = "reseleryfood";
    private $username = "root";
    private $password = "";
    private $charset = "utf8";
    protected $conn;

    public function __construct()
    {
        // echo "hello database";
    }

    protected function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->database_name};charset={$this->charset}", $this->username, $this->password);
        } catch (PDOException $exception) {
            throw new Exception("Database could not be connected: " . $exception->getMessage());
        }
        return $this->conn;
    }

    private function manipulate($payload)
    {

        if (isset($payload['roles'])) {
            $payload['roles'] = explode(",", $payload['roles']);
        }

        if (isset($payload['password'])) {
            unset($payload['password']);
        }

        return $payload;
    }

    public function baseQuery(string $sql): PDOStatement | Exception
    {
        try {
            $result = $this->getConnection()->query($sql);
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function query(string $sql): null| array | Exception
    {
        try {
            $result = $this->baseQuery($sql)->fetch(PDO::FETCH_ASSOC);
            if ($result == false) {
                return null;
            }
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function singleQuery(string $sql): null | array | Exception
    {
        try {
            $result = $this->query($sql);

            $result = $this->manipulate($result);

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function allQuery(string $sql): array | Exception
    {
        try {
            $result = $this->baseQuery($sql)->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 0; $i < count($result); $i++) {
                $result[$i] = $this->manipulate($result[$i]);
            }
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
