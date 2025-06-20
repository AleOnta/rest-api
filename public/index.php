<?php

require "../bootstrap.php";

use Src\Controllers\PostController;
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
    $router->delete('/{id}', UserController::class, 'delete');
});

# 2. /posts...
$router->group('/posts', function ($router) {
    $router->get('/', PostController::class, 'index');
    $router->post('/', PostController::class, 'create');
});

$router->match();
