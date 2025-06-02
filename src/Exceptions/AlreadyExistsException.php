<?php

namespace Src\Exceptions;

class AlreadyExistsException extends \Exception
{

    protected string $type;

    public function __construct($resource, $message)
    {
        $this->type = $resource;
        parent::__construct($message);
    }

    public function getType()
    {
        return $this->type;
    }

    public function buildMessage()
    {
        switch ($this->getType()) {

            case 'email':
                $addr = explode('@', $this->getMessage());
                $censored = str_pad('@' . $addr[1], strlen($this->getMessage()), '*', STR_PAD_LEFT);
                return "address [{$censored}] is already associated with an account.";

            case 'username':
                return "username is already in use.";

            default:
                return "resource with this [{$this->getType()}] already exists.";
        }
    }
}
