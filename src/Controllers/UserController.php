<?php

namespace Src\Controllers;

use Src\System\DB;
use Src\Models\User;
use Src\Gateways\UserGateway;
use Src\Traits\AuthorizeRequest;
use Src\Traits\AuthenticatesRequest;
use Src\Exceptions\AuthorizationException;
use Src\Validation\Requests\Users\EditRequest;
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

    /**
     * Handles client create requests for user instances creation.
     * @return json <p>a json response confirming the creation or the errors encountered in the process</p>
     * @throws InvalidParameterException
     */
    public function register()
    {
        # extract the body of the request
        $body = $this->validateBody(RegisterRequest::rules());
        # create the new entity
        $user = User::new($body['email'], $body['username'], $body['password']);
        # persist it in the db
        if ($this->userGateway->insert($user)) {
            $this->response([
                'success' => true,
                'message' => 'Registration successful, to authenticate in your next requests, include your credentials in a HTTP Authentication Header.',
            ], 201);
        }
        $this->badRequest();
    }

    /**
     * Handles client edit requests for their own user instance.
     * @return json <p>a json response confirming the update or the errors encountered in the process</p>
     * @throws InvalidParameterException
     */
    public function edit(int $id)
    {
        # check user authentication
        $auth = $this->authenticate();
        # check user authorization
        $this->isOwner($id, $auth);
        # extract the body of the request
        $body = $this->validateBody(EditRequest::rules());
        # retrieve the user instance
        $user = $this->userGateway->findById($id);
        # set new values into the user instance
        if (isset($body['email']))
            $user->setEmail($body['email']);
        if (isset($body['username']))
            $user->setUsername($body['username']);
        # persist the updates
        $update = $this->userGateway->update($user);
        # return response to the client
        if ($update) {
            $this->response([
                'success' => true,
                'message' => 'User correctly updated.',
                'data' => $this->userGateway->findById($id)->toArray()
            ]);
        }
        # return error response
        $this->badRequest('No user was updated, check the request body.');
    }

    /**
     * Handles client delete requests for their own user instance.
     * @param int <p>The id of the instance to delete.</p>
     * @return json <p>a json response confirming the deletion</p>
     */
    public function delete(int $id)
    {
        # check authentication
        $auth = $this->authenticate();
        # check authorization
        $this->isOwner($id, $auth);
        # delete the instance
        if ($this->userGateway->delete($auth)) {
            # return response to the client
            $this->response([
                'success' => true,
                'message' => 'Resource correctly deleted.'
            ]);
        }
        $this->badRequest();
    }
}
