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

    protected function __construct(?int $id, string $email, string $username, string $password, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }
}
