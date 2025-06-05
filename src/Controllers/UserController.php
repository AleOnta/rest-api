<?php

namespace Src\Controllers;

use Src\Gateways\UserGateway;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;
use Src\Traits\AuthorizeRequest;

class UserController extends Controller
{
    use AuthorizeRequest;
    use AuthenticatesRequest;
    private UserGateway $userGateway;

    public function __construct()
    {
        $db = new DB()->getConnection();
        $this->userGateway = new UserGateway($db);
    }

    /**
     * Retrieve and returns data the authenticated user. </br>
     * This endpoint requires authentication.
     * @return json <p>a json response containing the authenticated user data</p>
     * @throws AuthenticationException
     */
    public function index()
    {
        # auth
        $user = $this->authenticate();
        # directly return response
        $this->response(['success' => true, 'data' => ['user' => $user->toArray()]]);
    }

    /**
     * Retrieve and returns data of a user by its id. </br>
     * This endpoint requires admin authorization.
     * @param int $id <p>the id of the user to retrieve</p>
     * @return json <p>a json response containing the required resource or an empty array</p>
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function show(int $id)
    {
        # authentication
        $auth = $this->authenticate();
        # authorization
        $userId = $this->isAdmin($auth->getId());
        # retrieve data
        $user = $this->userGateway->findById($id);
        # response
        $user
            ? $this->response(['success' => true, 'data' => ['user' => $user->toArray()]])
            : $this->response(['success' => true, 'data' => []]);
    }
}
