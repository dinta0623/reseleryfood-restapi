<?php

namespace App\Utility;

use Dinta\Framework\Http\Response;

class JsonParser
{
    public function __construct($tampung, $postBody)
    {
        foreach (array_keys($postBody) as $key) {
            if (isset($postBody[$key])) {
                $tampung->$key = $postBody[$key];
            }
        }
    }
}
