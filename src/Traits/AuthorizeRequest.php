<?php

namespace Src\Traits;

use Src\Models\Post;
use Src\Models\User;
use Src\Exceptions\AuthorizationException;

trait AuthorizeRequest
{

    # simple admin ids list
    private const ADMIN_IDS = [1];

    /**
     * Basic authorization implementation by matching the user id 
     * to the ids of the users considered as admin. 
     * @param int $userId <p>the id of the user asking for authorization</p>
     * @return int the user id
     */
    protected function isAdmin(int $userId)
    {
        if (!in_array($userId, self::ADMIN_IDS, true)) {
            # reject authorization
            throw new AuthorizationException();
        }
        return $userId;
    }

    /**
     * Evaluate if the user forwarding the request is the owner of the
     * resource that is trying to see/edit/delete.
     * @param int $userId <p>the id of the user asking for authorization</p>
     * @param mixed $resource <p>the actual instance to check ownership on</p>
     * @throws AuthorizationException
     * @return bool
     */
    protected function isOwner(int $userId, mixed $resource)
    {
        $own = false;
        switch ($resource) {

            case $resource instanceof User:
                $own = $resource->getId() === $userId;
                break;

            case $resource instanceof Post:
                $own = $resource->getUserId() === $userId;
                break;

            default:
                $own = false;
        }
        # bad ownership
        if (!$own) {
            throw new AuthorizationException("You are not authorized to access the requested resource");
        }
        # allow request
        return true;
    }
}
