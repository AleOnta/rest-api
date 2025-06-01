<?php

namespace Src\Models;

class User
{

    protected ?int $id;
    protected string $email;
    protected string $username;
    protected string $password;

    protected function __construct(?int $id, string $email, string $username, string $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
    }

    public static function new(string $email, string $username, string $password)
    {
        return new self(
            null,
            $email,
            $username,
            $password
        );
    }

    public static function fromDB(array $data)
    {
        return new self(
            $data['id'],
            $data['email'],
            $data['username'],
            $data['password']
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
}
