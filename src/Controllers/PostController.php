<?php

namespace Src\Controllers;

use Src\Gateways\PostGateway;
use Src\Models\Post;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;
use Src\Traits\AuthorizeRequest;
use Src\Validation\Requests\Posts\CreateRequest;

class PostController extends Controller
{

    use AuthorizeRequest;
    use AuthenticatesRequest;
    private PostGateway $postGateway;

    public function __construct()
    {
        $db = new DB()->getConnection();
        $this->postGateway = new PostGateway($db);
    }

    /**
     * Retrieve and return authenticated user posts or an empty array if user doesn't have any post.
     * @return json
     */
    public function index()
    {
        # check authentication
        $auth = $this->authenticate();
        # retrieve all user posts
        $posts = $this->postGateway->find(['user_id' => $auth->getId()]);
        # return response to the client
        $this->response([
            'success' => true,
            'data' => [
                'posts' => count($posts) > 0
                    ? array_map(fn(Post $post) => $post->toArray(), $posts)
                    : []
            ]
        ], 200);
    }


    public function create()
    {
        # check authentication
        $auth = $this->authenticate();
        # validate the request body
        $body = $this->validateBody(CreateRequest::rules());
        # create the post entity
        $post = Post::new($auth->getId(), $body['title'], $body['content']);
        # persist the post in the db
        if ($this->postGateway->insert($post)) {
            $this->response([
                'success' => true,
                'message' => 'Post created successfully.'
            ], 201);
        }
    }
}
