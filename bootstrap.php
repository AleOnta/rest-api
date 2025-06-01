<?php

require "vendor/autoload.php";

# initialize and read .env into memory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
