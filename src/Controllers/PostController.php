<?php

namespace Src\Controllers;

use Src\Exceptions\NotFoundException;
use Src\Gateways\PostGateway;
use Src\Models\Post;
use Src\System\DB;
use Src\Traits\AuthenticatesRequest;
use Src\Traits\AuthorizeRequest;
use Src\Validation\Requests\Posts\CreateRequest;
use Src\Validation\Requests\Posts\EditRequest;

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

    /**
     * Handle the GET requests for the seeing a post entitiy owned by the authenticated user.
     * @param int $id <p>the id of the post entity to return.</p>
     * @return json
     */
    public function show(int $id)
    {
        # check authentication
        $auth = $this->authenticate();
        # retrieve the post by id
        $post = $this->postGateway->findById($id);
        if (!$post) {
            throw new NotFoundException("Not found");
        }
        # check authorization
        $this->isOwner($auth->getId(), $post);
        # return the post to the client
        $this->response([
            'status' => 'success',
            'data' => [
                'post' => $post->toArray()
            ]
        ]);
    }

    /**
     * Handle the POST requests for the creation of posts entities for the authenticated user.
     * @return json
     */
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

    /**
     * Handle the PATCH requests for the editing of posts entities for the authenticated user.
     * @param int $id <p>the id of the post entity to edit.</p>
     * @return json
     */
    public function edit(int $id)
    {
        # check authentication
        $auth = $this->authenticate();
        # validate the request body
        $body = $this->validateBody(EditRequest::rules());
        # retrieve the post by id
        $post = $this->postGateway->findById($id);
        if (!$post) {
            throw new NotFoundException("Not found");
        }
        # check authorization
        $this->isOwner($auth->getId(), $post);
        # perform optional updates on the instance
        if (isset($body['title']))
            $post->setTitle($body['title']);
        if (isset($body['content']))
            $post->setContent($body['content']);
        # persist the updates
        $this->postGateway->update($post)
            ? $this->response(['status' => true, 'message' => 'Post successfully updated.', 'data' => ['id' => $id]])
            : $this->badRequest('No post was updated. Check the body of the request...');
    }

    /**
     * Handle the DELETE requests for the deleting posts entities owned by the authenticated user.
     * @param int $id <p>the id of the post entity to delete.</p>
     * @return json
     */
    public function delete(int $id)
    {
        # check authentication
        $auth = $this->authenticate();
        # retrieve the post
        $post = $this->postGateway->findById($id);
        if (!$post) {
            throw new NotFoundException("Not found");
        }
        # check authorization
        $this->isOwner($auth->getId(), $post);
        # delete the post
        $this->postGateway->delete($post);
        # return response to the client
        $this->response([
            'success' => 'true',
            'message' => "Post with id {$id} successfully deleted."
        ]);
    }
}
