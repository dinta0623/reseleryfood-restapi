<?php

namespace App\Controller;

use Dinta\Framework\Http\Response;

class HomeController

{
    public function index(...$args): Response
    {
        $content = json_encode([
            'success' => true,
            'message' => 'ping!',
        ]);

        return new Response($content);
    }

    public function error(...$args): Response
    {
        $content = json_encode([
            'success' => false,
            'message' => 'Not Found!',
        ]);
        return new Response($content, '404');
    }
}
