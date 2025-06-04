<?php

namespace Src\Traits;

use Src\Exceptions\AuthorizationException;

trait AuthorizeRequest
{

    # simple admin ids list
    private const ADMIN_IDS = [1];

    /**
     * Basic authorization implementation by matching the user id to the ids 
     * of the users considered as admin 
     * @param int $userId <p>the id of the user asking for authorization</p>
     * @return int the user id
     */
    public function authorize(int $userId)
    {
        if (!in_array($userId, self::ADMIN_IDS)) {
            throw new AuthorizationException('Unauthorized');
        }
        return $userId;
    }
}
