<?php

namespace Src\Exceptions;

class ValidationException extends \Exception
{

    protected array $errors;

    public function __construct($message, $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
