<?php
require "vendor/autoload.php";

# set up global exception handler for application
$exceptionHandler = include 'src/Exceptions/Handler.php';
set_exception_handler($exceptionHandler);

# initialize and read .env into memory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
