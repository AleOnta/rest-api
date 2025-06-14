<?php

require "../bootstrap.php";

use Src\System\Router;
use Src\Controllers\UserController;

# create the router class
$router = new Router();

# register routes
# 1. /users...
$router->group('/users', function ($router) {
    $router->get('/me', UserController::class, 'index');
    $router->get('/{id}', UserController::class, 'show');
    $router->post('/', UserController::class, 'register');
    $router->patch('/{id}', UserController::class, 'edit');
});

$router->match();
