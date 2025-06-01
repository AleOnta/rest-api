<?php
require "vendor/autoload.php";

use Src\System\DB;

# initialize and read .env into memory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

# get db connection
$db = new DB()->getConnection();
