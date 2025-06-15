<?php

namespace Src\Gateways;

use Src\Models\Post;
use Src\Exceptions\ValidationException;

class PostGateway extends TableGateway
{
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection);
        $this->table = 'posts';
    }

    /**
     * Create a new Post entity in the db, based on the one received by its parameters.
     * Before inserting the new entity.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function insert($post)
    {
        # basic validation of the instance
        $this->validatePost($post);

        # evaluate if the post is new / unique
        $this->existsByTitle($post->getTitle());

        # proceed with insert
        try {
            $stmt = $this->connection->prepare("INSERT INTO {$this->table} (user_id, title, content) VALUES (:user_id, :title, :content);");
            $stmt->execute([
                'user_id' => $post->getUserId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent()
            ]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update the passed Post entity with the new values set in the local instance.
     * If no update is registered, no action is performed.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function update($post)
    {
        # validate the instance
        $this->validatePost($post);

        # validate if the instance requires updates
        if (!$post->hasUpdates()) return 0;

        # proceed with the statement
        $query = "UPDATE {$this->table} SET ";
        foreach ($post->getUpdates() as $key => $val) {
            $query .= "{$key} = :{$key}, ";
        }

        # remove last comma and attach condition
        $query = substr($query, 0, strlen($query) - 2) . " WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($query);
            $params = $post->getUpdates();
            $params['id'] = $post->getId();
            return $stmt->execute($params)
                ? $stmt->rowCount()
                : 0;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete from the db the passed Post instance, by its id.
     * @throws Exception <p>in case of failed db connection</p>
     * @return int <p>returns 1 in case of success, 0 for failure</p>
     */
    public function delete($post)
    {
        $id = $post->getId();
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
     * Retrieve Post instances from the db based on conditions defined through array param.
     * If no param is specified, it invokes the findAll method and return all instances.
     * @throws Exception <p>in case of failed db connection</p>
     * @return array <p>returns an array of Post instances</p>
     */
    public function find(array $params = [])
    {
        if (count($params) === 0) {
            return $this->findAll();
        }

        $params = array_filter($params, function ($key) {
            return in_array($key, ['user_id', 'title', 'content']);
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
     * Retrieve and return a Post model class by its Id.
     * @throws Exception <p>in case of failed db connection</p>
     * @return Post <p>returns the posts instance or false if doesn't exists</p>
     */
    public function findById(int $id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam('id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $post = $stmt->fetch(\PDO::FETCH_ASSOC);
                return isset($post['id']) ? $this->hydrate($post) : false;
            }
            return false;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Returns all entities in the posts table.
     * @throws Exception <p>in case of failed db connection</p>
     * @return array <p>returns an array of Post instances</p>
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
     * checks if a row exist by the title column
     * @param string $title <p>the title to search</p>
     * @return int <p>returns 1 if an row is found, 0 if not</p>
     */
    public function existsByTitle(string $title)
    {
        return $this->find(['title' => $title])
            ? 1 : 0;
    }


    /**
     * Basic validation of user inputs
     * @param mixed $post <p>parameter on which the validation is executed</p>
     * @throws Exception <p>in case of bad parameter</p>
     * @throws ValidationException <p>in case of a invalid value received</p>
     */
    public function validatePost($post)
    {
        # 0. var type
        if (!($post instanceof Post)) {
            throw new \Exception('Invalid parameter - required instance of Post.');
        }
        # 1. title - not NULL
        if (!$post->getTitle() || trim($post->getTitle()) === '') {
            throw new ValidationException('Some of the provided values are invalid.', [
                'title' => 'the post title cannot be empty.'
            ]);
        }
        # 2. title - length
        if (strlen($post->getTitle()) < 12) {
            throw new ValidationException('Some of the provided values are invalid.', [
                'title' => 'the post title must be longer of 20 characters.'
            ]);
        }
        # 3. content
        if (!$post->getContent() || trim($post->getContent()) === '') {
            throw new ValidationException('Some of the provided values are invalid.', [
                'content' => 'the post content cannot be empty.'
            ]);
        }
        # 4. password
        if (strlen($post->getContent()) < 75) {
            throw new ValidationException('Some of the provided values are invalid.', [
                'content' => 'the post content must be longer of 75 characters.'
            ]);
        }
    }

    /**
     * Convert a Post array extracted as a row from the db into a Post model instance.
     * @return Post <p>an instance of the Post model class</p>
     */
    public function hydrate(array $row)
    {
        # convert the db row into a Post instance
        return Post::fromDB($row);
    }
}
