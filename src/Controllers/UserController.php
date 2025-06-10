<?php

namespace Src\Controllers;

use Src\Exceptions\ValidationException;
use Src\Gateways\UserGateway;
use Src\Models\User;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;
use Src\Traits\AuthorizeRequest;
use Src\Validation\Validator;
use Src\Validation\Requests\Users\RegisterRequest;

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

    public function register()
    {
        # extract the body of the request
        $body = $this->bodyJSON();
        # validate the request body based on the rules set for the user register request
        $errors = Validator::validate($body, RegisterRequest::rules());
        if (count($errors) > 0) {
            throw new ValidationException('Invalid Request', $errors);
        }
        # create the new entity
        $user = User::new($body['email'], $body['username'], $body['password']);
        # persist it in the db
        if ($this->userGateway->insert($user)) {
            $this->response([
                'success' => true,
                'message' => 'Registration successful, include your username and password in a HTTP Authentication header in next requests to authenticate correctly (such as username:password)',
            ], 201);
        }
    }
}
