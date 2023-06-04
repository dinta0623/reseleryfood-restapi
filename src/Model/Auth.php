<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class Auth extends Database
{
    public string $table_name = "users";
    public $secretKeyAccess;
    public $secretKeyRefresh;
    public $algo;
    private $issuedAt;
    private $expire;
    public array $errors = [];


    public function __construct()
    {
        $this->secretKeyAccess  = getenv('SECRET_ACCESS');
        $this->secretKeyRefresh  = getenv('SECRET_REFRESH');
        $this->algo  = getenv('SECRET_ALGO');
        // $this->getConnection()->query("CREATE TABLE IF NOT EXISTS {$this->table_name} ")->execute();
    }

    public function resetValidation(): void
    {
        $this->errors = [];
    }

    public function validate(string $email, string $password): bool
    {
        $this->resetValidation();

        if (!isset($email)) {
            $this->errors['email'] = "Harap isi email";
        } else if (strlen($email) < 3) {
            $this->errors['email'] = "Email minimal 3 karakter";
        } else if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors['email'] = "Email yang Anda masukkan tidak valid";
        }

        if (!isset($password)) {
            $this->errors['password'] = "Harap isi password";
        } else if (strlen($password) < 8) {
            $this->errors['password'] = "Password minimal 8 karakter";
        }


        if (isset($this->errors) && count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    public function findById(string $id): null| array | Exception
    {
        try {
            $result = $this->singleQuery("SELECT * FROM {$this->table_name} WHERE id = '{$id}'");
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function generateRefreshToken($payload): string | Exception
    {
        try {
            if (isset($payload["avatar"])) {
                unset($payload["avatar"]);
            }


            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+1 month')->getTimestamp();

            $payload = [
                'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
                'iss'  => "reseleryfood.com",                       // Issuer
                'nbf'  => $issuedAt->getTimestamp(),         // Not before
                'exp'  => $expire,                           // Expire
                'userId' => $payload["id"],                     // User data
            ];


            return JWT::encode($payload, $this->secretKeyRefresh, $this->algo);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function generateAccessToken($payload): string | Exception
    {
        try {

            if (isset($payload["avatar"])) {
                unset($payload["avatar"]);
            }

            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+50 minutes')->getTimestamp();


            $payload = [
                'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
                'iss'  => "reseleryfood.com",                       // Issuer
                'nbf'  => $issuedAt->getTimestamp(),         // Not before
                'exp'  => $expire,                           // Expire
                'payload' => $payload,                     // User data
            ];


            return JWT::encode($payload, $this->secretKeyAccess, $this->algo);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function login(string $email): array | Exception | null
    {
        try {
            $result = $this->singleQuery("SELECT * FROM {$this->table_name} WHERE email = '{$email}'");

            $payload = array(
                "access_token" =>  $this->generateAccessToken($result),
                "refresh_token" =>  $this->generateRefreshToken($result),
                "user" =>  $result,
            );

            $this->resetValidation();

            return $payload;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
