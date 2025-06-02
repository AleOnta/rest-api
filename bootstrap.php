<?php
require "vendor/autoload.php";

$exceptionHandler = include 'src/Exceptions/Handler.php';
set_exception_handler($exceptionHandler);

use Src\Gateways\UserGateway;
use Src\Models\User;
use Src\System\DB;

# initialize and read .env into memory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

# get db connection
$db = new DB()->getConnection();
