<?php

namespace Src\Traits;

use Src\Gateways\UserGateway;
use Src\System\DB;

trait AuthenticatesRequest
{

    public function authenticate()
    {
        # reject request if header is not set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->unauthorized();
        }

        # extract the header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Basic\s(\S+)/', $authHeader, $matches)) {
            $decoded = base64_decode($matches[1]);
            [$username, $password] = explode(':', $decoded, 2);

            # validate against records in db
            $db = new DB()->getConnection();
            $userGateway = new UserGateway($db);
            $user = $userGateway->findByUsername($username);
            # match passwords
            if ($user && password_verify($password, $user->getPassword())) {
                return $user;
            }
        }

        # reject request due to invalid credentials
        $this->unauthorized('Invalid credentials');
    }

    private function unauthorized(string $message = 'Unauthorized')
    {
        http_response_code(401);
        echo json_encode(['error' => $message]);
        exit;
    }
}
