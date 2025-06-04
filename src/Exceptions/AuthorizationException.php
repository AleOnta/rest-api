<?php

namespace Src\Exceptions;

class AuthorizationException extends \Exception
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
