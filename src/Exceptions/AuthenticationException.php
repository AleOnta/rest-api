<?php

namespace Src\Exceptions;

class AuthenticationException extends \Exception
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
