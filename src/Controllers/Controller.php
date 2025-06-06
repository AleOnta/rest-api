<?php

namespace Src\Controllers;

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

    protected function bodyJSON()
    {
        # read the request body
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if ($body === null && !empty($_POST)) {
            $body = $_POST;
        } else {
            parse_str($raw, $body);
        }
        # return bad request
        if (!$body) {
            $this->response([
                'error' => true,
                'message' => 'Invalid request body'
            ], 400);
        }
        # return the decoded body
        return $body;
    }
}
