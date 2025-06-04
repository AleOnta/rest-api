<?php

namespace Src\Traits;

use Src\Exceptions\AuthenticationException;
use Src\Gateways\UserGateway;
use Src\System\DB;

trait AuthenticatesRequest
{

    public function authenticate()
    {
        # reject request if header is not set
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            throw new AuthenticationException('Unauthorized');
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
        throw new AuthenticationException('Invalid credentials');
    }
}
