<?php

namespace Src\Controllers;

use Src\Gateways\UserGateway;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;

class UserController
{
    use AuthenticatesRequest;
    private UserGateway $userGateway;

    public function __construct()
    {
        $db = new DB()->getConnection();
        $this->userGateway = new UserGateway($db);
    }

    public function index()
    {
        # auth
        $user = $this->authenticate();
        # directly return response
        $this->response(['success' => true, 'data' => ['user' => $user->toArray()]]);
    }

    public function show(int $id)
    {
        # auth
        $auth = $this->authenticate();
        # proceed with request
        $user = $this->userGateway->findById($id);
        if ($user) {
            $user->getId() === $auth->getId()
                ? $this->response(['success' => true, 'data' => ['user' => $user->toArray()]])
                : $this->unauthorized();
        }
        $this->response(['success' => true, 'data' => []]);
    }

    public function response($data, $code = 200)
    {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
