<?php

declare(strict_types=1);

$dirname = dirname(__DIR__);
require_once $dirname . "/vendor/autoload.php";

use App\Utility\Cors;
use Dinta\Framework\Http\Kernel;
use Dinta\Framework\Http\Request;
use Dinta\Framework\Http\Response;

define('BASE_PATH', $dirname);


$dotenv = Dotenv\Dotenv::createUnsafeImmutable($dirname);
$dotenv->load();

new Cors();


//request received
$request = Request::createFromGlobals();


//send response (string of content)
$kernel = new Kernel();

$response = $kernel->handle($request);

$response->send();
