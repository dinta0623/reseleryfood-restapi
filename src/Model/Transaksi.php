<?php

declare(strict_types=1);

namespace App\Model;

use App\Config\Database;
use App\Utility\Random;
use App\Model\TransaksiItem;
use App\Model\Mitra;
use Exception;
use PDO;
use PDOStatement;

class Transaksi extends Database
{
    public string $table_name = "transaksi";
    public ?string $id;
    public ?string $mitra_id;
    public ?string $user_id;
    public ?string $kurir_id;
    public ?string $no;
    public ?string $date;
    public ?string $duedate;
    public ?string $status;
    public ?string $description;
    public ?string $address;
    public ?string $lat;
    public ?string $lng;
    public ?string $diskon;
    public ?int $isMenu;
    public ?string $total;
    public ?string $ongkir;
    public ?string $fee;
    public ?string $phonenum;
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
        $this->mitra_id = null;
        $this->user_id = null;
        $this->kurir_id = null; // optional
        $this->no = null;
        $this->date = null;
        $this->duedate = null;
        $this->status = null;
        $this->description = null; // optional
        $this->address = null;
        $this->lat = null;
        $this->lng = null;
        $this->diskon = null; //optional
        $this->isMenu = null; // auto fill
        $this->total = null;
        $this->ongkir = null;
        $this->fee = null;
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

        if (!isset($at) || (isset($at) && $at === "user_id")) {
            if (!isset($this->user_id)  || isset($this->user_id) && strlen($this->user_id) <= 0) {
                $this->errors['user_id'] = "Harap isi user_id";
            }
        }

        if (!isset($at) || (isset($at) && $at === "no")) {
            if (!isset($this->no)  || isset($this->no) && strlen($this->no) <= 0) {
                $this->errors['no'] = "Harap isi no transaksi";
            }
        }

        if (!isset($at) || (isset($at) && $at === "date")) {
            if (!isset($this->date)  || isset($this->date) && strlen($this->date) <= 0) {
                $this->errors['date'] = "Harap isi date";
            }
        }

        if (!isset($at) || (isset($at) && $at === "duedate")) {
            if (!isset($this->duedate)  || isset($this->duedate) && strlen($this->duedate) <= 0) {
                $this->errors['duedate'] = "Harap isi duedate";
            }
        }

        if (!isset($at) || (isset($at) && $at === "status")) {
            if (!isset($this->status)  || isset($this->status) && strlen($this->status) <= 0) {
                $this->errors['status'] = "Harap isi status";
            }
        }

        // if (!isset($at) || (isset($at) && $at === "address")) {
        //     if (!isset($this->address)  || isset($this->address) && strlen($this->address) <= 0) {
        //         $this->errors['address'] = "Harap isi address";
        //     }
        // }

        // if (!isset($at) || (isset($at) && $at === "lat")) {
        //     if (!isset($this->lat)  || isset($this->lat) && strlen($this->lat) <= 0) {
        //         $this->errors['lat'] = "Harap isi lat";
        //     }
        // }

        // if (!isset($at) || (isset($at) && $at === "lng")) {
        //     if (!isset($this->lng)  || isset($this->lng) && strlen($this->lng) <= 0) {
        //         $this->errors['lng'] = "Harap isi lng";
        //     }
        // }

        // if (!isset($at) || (isset($at) && $at === "total")) {
        //     if (!isset($this->total)  || isset($this->total) && strlen($this->total) <= 0) {
        //         $this->errors['total'] = "Harap isi total";
        //     }
        // }
        // if (!isset($at) || (isset($at) && $at === "ongkir")) {
        //     if (!isset($this->ongkir)  || isset($this->ongkir) && strlen($this->ongkir) <= 0) {
        //         $this->errors['ongkir'] = "Harap isi ongkir";
        //     }
        // }
        // if (!isset($at) || (isset($at) && $at === "fee")) {
        //     if (!isset($this->fee)  || isset($this->fee) && strlen($this->fee) <= 0) {
        //         $this->errors['fee'] = "Harap isi fee";
        //     }
        // }

        if (isset($this->errors) && count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    public function findById(string $id): null | array | Exception
    {
        try {
            $result = $this->singleQuery("SELECT * FROM {$this->table_name} WHERE id = '{$id}'");
            if (isset($result['diskon'])) {
                $result['diskon'] = (int) $result['diskon'];
            }
            if (isset($result['total'])) {
                $result['total'] = (int) $result['total'];
            }
            if (isset($result['ongkir'])) {
                $result['ongkir'] = (int) $result['ongkir'];
            }
            if (isset($result['fee'])) {
                $result['fee'] = (int) $result['fee'];
            }
            if (isset($result['isMenu'])) {
                $result['isMenu'] = (int) $result['isMenu'] == 0 ? false : true;
            }
            $mitra = (new Mitra())->findById($result['mitra_id']);
            $result['mitra'] = $mitra;

            $items = (new TransaksiItem())->findAllByTransaction($result['id']);
            $result['items'] = $items;

            $user = (new User())->findById($result['user_id']);
            $result['user'] = $user;
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

                $user = (new User())->findById($result[$i]['user_id']);
                $result[$i]['user'] = $user;
            }
            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(): Exception | array | null | string
    {
        try {
            if (!$this->validate()) {
                throw new Exception("Tidak Valid");
            }

            $uuid = (new Random())->uuidv4();

            $this->isMenu = isset($this->isMenu) ? $this->isMenu : 0;
            $this->diskon = isset($this->diskon) ? $this->diskon : '0';
            $this->phonenum = isset($this->phonenum) ? $this->phonenum : '';
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = $this->created_at;

            $sql = "INSERT INTO {$this->table_name} VALUES ('{$uuid}', '{$this->no}', '{$this->date}', '{$this->duedate}', '{$this->mitra_id}', '{$this->user_id}', '{$this->kurir_id}', '{$this->status}', '{$this->description}', '{$this->address}', '{$this->lat}', '{$this->lng}', '{$this->diskon}', {$this->isMenu}, '{$this->total}', '{$this->ongkir}', '{$this->fee}', '{$this->phonenum}', '{$this->created_at}', '{$this->updated_at}')";

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
            if (isset($this->kurir_id)) {
                $columns .= "`kurir_id` = '{$this->kurir_id}', ";
            }
            if (isset($this->status)) {
                $columns .= "`status` = '{$this->status}', ";
            }
            if (isset($this->description)) {
                $columns .= "`description` = '{$this->description}', ";
            }
            if (isset($this->diskon)) {
                $columns .= "`diskon` = '{$this->diskon}', ";
            }
            if (isset($this->total)) {
                $columns .= "`total` = '{$this->total}', ";
            }
            if (isset($this->ongkir)) {
                $columns .= "`ongkir` = '{$this->ongkir}', ";
            }
            if (isset($this->fee)) {
                $columns .= "`fee` = '{$this->fee}', ";
            }
            if (isset($this->phonenum)) {
                $columns .= "`phonenum` = '{$this->phonenum}', ";
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
