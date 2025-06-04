<?php

use Src\Exceptions\AlreadyExistsException;
use Src\Exceptions\AuthenticationException;
use Src\Exceptions\ValidationException;

return function (Throwable $e) {

    $data = [
        'code' => 500,
        'message' => '...'
    ];

    switch ($e) {

        # ALREADY EXISTS
        case $e instanceof AlreadyExistsException:
            $data['code'] = 409;
            $data['message'] = $e->buildMessage();
            break;

        # VALIDATION ERROR
        case $e instanceof ValidationException:
            $data['code'] = 422;
            $data['message'] = $e->getMessage();
            $data['errors'] = $e->getErrors();
            break;


        # INVALID AUTHENTICATION
        case $e instanceof AuthenticationException:
            $data['code'] = 401;
            $data['message'] = $e->getMessage();
            break;

        # DEF FALLBACK
        default:
            $data['message'] = $_ENV['APP_ENV'] === 'dev'
                ? $e->getMessage()
                : 'An error has occurred, try again later...';
            break;
    }

    $response = [
        'error' => true,
        'message' => $data['message']
    ];

    if (isset($data['errors'])) {
        $response['errors'] = $data['errors'];
    }

    http_response_code($data['code']);
    echo json_encode($response);
    exit;
};
