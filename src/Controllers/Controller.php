<?php

namespace Src\Controllers;

use Src\Validation\Validator;
use Src\Exceptions\ValidationException;

class Controller
{
    /**
     * Helper function that return a response to the client.
     * @param array $data <p>the payload to return to the client</p>
     * @param int $code <p>the http response code attached to the response</p>
     * @return json <p>a json response containing the required resource or an empty array</p>
     */
    protected function response(array $data, int $code = 200)
    {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Helper function that return a Bad Request response to the client.
     * @param string $message <p>Defaults at 'Bad request' but allows more specific information to be shared.</p>
     * @return json <p>a json response</p>
     */
    protected function badRequest(string $message = 'Bad Request')
    {
        if ($message === 'Bad Request') {
            $data = ['error' => true, 'message' => $message];
        } else {
            $data = ['error' => true, 'status' => 'Bad Request', 'message' => $message];
        }
        $this->response($data, 400);
    }

    /**
     * Controller helper function that extract the body from the incoming request.
     * It checks for content from POST, PUT & PATCH requests.
     * @return array|json <p>the decoded body in an associative array or a json response indicating problems with the body of the request</p>
     * @throws ValidationException
     */
    protected function bodyJSON()
    {
        # read the request body
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if ($body === null && !empty($_POST)) {
            $body = $_POST;
        } elseif ($body === null) {
            parse_str($raw, $body);
        }
        # return bad request
        if (!$body) {
            throw new ValidationException('Invalid request body.', [0 => 'The request body cannot be empty']);
        }
        # return the decoded body
        return $body;
    }

    /**
     * Controller helper function that extract the request body from the incoming request and then validates it agains a 
     * specific set of rules.
     * @param rules <p>An associative array containing the rules that must be met foreach property expected to be present in the req. body</p>
     * @return array <p>The validate request body</p>
     * @throws ValidationException
     */
    protected function validateBody(array $rules)
    {
        # extract the request body
        $body = $this->bodyJSON();
        # validate the request body against the set of rules passed
        $errors = Validator::validate($body, $rules);
        if (count($errors) > 0) {
            throw new ValidationException("Invalid request body.", $errors);
        }
        # return the validated body
        return $body;
    }
}
