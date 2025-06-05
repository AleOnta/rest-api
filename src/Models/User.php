<?php

namespace Src\Models;

use DateTime;
use DateTimeZone;

class User
{

    protected ?int $id;
    protected string $email;
    protected string $username;
    protected string $password;
    protected string $createdAt;
    protected string $updatedAt;
    protected array $updates;

    protected function __construct(?int $id, string $email, string $username, string $password, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->updates = [];
    }

    public static function new(string $email, string $username, string $password)
    {
        return new self(
            null,
            $email,
            $username,
            $password,
            new DateTime('now', new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            new DateTime('now', new DateTimeZone('UTC'))->format('Y-m-d H:i:s')
        );
    }

    public static function fromDB(array $data)
    {
        return new self(
            $data['id'],
            $data['email'],
            $data['username'],
            $data['password'],
            $data['created_at'],
            $data['updated_at']
        );
    }

    public function getId()
    {
        return $this->id ?? null;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
        if (!in_array('email', $this->updates)) $this->updates[] = 'email';
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
        if (!in_array('username', $this->updates)) $this->updates[] = 'username';
    }

    public function setPassword(string $password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        if (!in_array('password', $this->updates)) $this->updates[] = 'password';
    }

    public function hasUpdates()
    {
        return count($this->updates);
    }

    public function getUpdates()
    {
        $updates = [];
        foreach ($this->updates as $key) {
            $updates[$key] = match ($key) {
                'email' => $this->getEmail(),
                'username' => $this->getUsername(),
                'password' => $this->getPassword(),
                default => false
            };
        }
        return $updates;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'username' => $this->getUsername(),
            'created' => $this->getCreatedAt()
        ];
    }
}
