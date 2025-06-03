<?php

namespace Src\Gateways;

use Src\Exceptions\AlreadyExistsException;
use Src\Exceptions\ValidationException;
use Src\Models\User;

class UserGateway extends TableGateway
{
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection);
        $this->table = 'users';
    }

    /**
     * Create a new User entity in the db, based on the one received by its parameters.
     * Before inserting the new entity, it validates it and check for its existence.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function insert($user)
    {
        # basic validation of the instance
        $this->validateUser($user);

        # evaluate if the user is new / unique
        $this->alreadyExists($user);

        # proceed with insert
        try {
            $stmt = $this->connection->prepare("INSERT INTO {$this->table} (email, username, password) VALUES (:email, :username, :password);");
            $stmt->execute([
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword()
            ]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update the passed User entity with the new values set in the local instance.
     * If no update is registered, no action is performed.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function update($user)
    {
        # validate the instance
        $this->validateUser($user);

        # validate if the instance requires updates
        if (!$user->hasUpdates()) return 0;

        # proceed with the statement
        $query = "UPDATE {$this->table} SET ";
        foreach ($user->getUpdates() as $key => $val) {
            $query .= "{$key} = :{$key}, ";
        }

        # remove last comma and attach condition
        $query = substr($query, 0, strlen($query) - 2) . " WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($query);
            $params = $user->getUpdates();
            $params['id'] = $user->getId();
            return $stmt->execute($params)
                ? $stmt->rowCount()
                : 0;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete from the db the passed User instance, by its id.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function delete($user)
    {
        $id = $user->getId();
        # proceed with the statement
        try {
            $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindParam('id', $id, \PDO::PARAM_INT);
            return $stmt->execute()
                ? $stmt->rowCount()
                : 0;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve User instances from the db based on conditions defined through array param.
     * If no param is specified, it invokes the findAll method and return all instances.
     * @throws Exception <p>in case of failed db connection</p>
     * @return array <p>returns an array of user instances</p>
     */
    public function find(array $params)
    {
        if (count($params) === 0) {
            return $this->findAll();
        }

        $params = array_filter($params, function ($key) {
            return in_array($key, ['id', 'email', 'username', 'password']);
        }, ARRAY_FILTER_USE_KEY);
        if (count($params) === 0) return false;

        # proceed with the statement
        $query = "SELECT * FROM {$this->table} WHERE ";
        foreach ($params as $key => $val) {
            $query .= "{$key} = :{$key} ";
        }

        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params)
                ? array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(\PDO::FETCH_ASSOC))
                : [];
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve and return a User model class by its Id.
     * @throws Exception <p>in case of failed db connection</p>
     * @return User <p>returns the users instance or false if doesn't exists</p>
     */
    public function findById(int $id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam('id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                return isset($user['id']) ? $this->hydrate($user) : false;
            }
            return false;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve and return a User model class by its username.
     * @throws Exception <p>in case of failed db connection</p>
     * @return User <p>returns the users instance or false if doesn't exists</p>
     */
    public function findByUsername(string $username)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE username = :username;");
            $stmt->bindParam('username', $username, \PDO::PARAM_STR);
            if ($stmt->execute()) {
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                return isset($user['id']) ? User::fromDB($user) : false;
            }
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Returns all entities in the user table.
     * @throws Exception <p>in case of failed db connection</p>
     * @return array <p>returns an array of User instances</p>
     */
    public function findAll()
    {
        try {
            $stmt = $this->connection->query("SELECT * FROM {$this->table};");
            return $stmt->execute()
                ? array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll(\PDO::FETCH_ASSOC))
                : [];
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Evaluates if a user entity already exists based on its 2 unique properties [email & username].
     * @param mixed $user <p>parameter on which the value must be checked</p>
     * @throws AlreadyExistsException <p>in case a row already exists</p>
     */
    public function alreadyExists(User $user)
    {
        # evaluate if the user is new
        if ($this->existsByEmail($user->getEmail())) {
            throw new AlreadyExistsException('email', $user->getEmail());
        }

        if ($this->existsByUsername($user->getUsername())) {
            throw new AlreadyExistsException('username', $user->getUsername());
        }
    }

    /**
     * checks if a row exist by the username column
     * @param string $email <p>the username to search</p>
     * @return int <p>returns 1 if an row is found, 0 if not</p>
     */
    public function existsByUsername(string $username)
    {
        return $this->find(['username' => $username])
            ? 1 : 0;
    }

    /**
     * checks if a row exist by the email column
     * @param string $email <p>the email to search</p>
     * @return int <p>returns 1 if an row is found, 0 if not</p>
     */
    public function existsByEmail(string $email)
    {
        return $this->find(['email' => $email])
            ? 1 : 0;
    }


    /**
     * Basic validation of user inputs
     * @param mixed $user <p>parameter on which the validation is executed</p>
     * @throws Exception <p>in case of bad parameter</p>
     * @throws ValidationException <p>in case of a invalid value received</p>
     */
    public function validateUser($user)
    {
        # 0. var type
        if (!($user instanceof User)) {
            throw new \Exception('Invalid parameter - required instance of User.');
        }
        # 1. email
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Some of the provided values are invalid.', [
                'email' => 'the provided email address is invalid'
            ]);
        }
        # 2. username
        if (!ctype_alnum($user->getUsername())) {
            throw new ValidationException('Some of the provided values are invalid.', [
                'username' => 'Username field must contains only alphanumeric values'
            ]);
        }
        # 3. password
        if (strlen($user->getPassword()) < 8) {
            throw new ValidationException('Some of the provided values are invalid.', [
                'password' => 'The password length must be of at least 8 characters'
            ]);
        }
    }

    /**
     * Convert a User array extracted as a row from the db into a User model instance.
     * @return User <p>an instance of the User model class</p>
     */
    public function hydrate(array $row)
    {
        # convert the db row into a User instance
        return User::fromDB($row);
    }
}
