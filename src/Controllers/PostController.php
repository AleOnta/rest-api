<?php

namespace Src\Controllers;

use Src\Gateways\PostGateway;
use Src\Models\Post;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;
use Src\Traits\AuthorizeRequest;

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
}
