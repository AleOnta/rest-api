<?php

namespace Src\Models;

use DateTime;
use DateTimeZone;

class Post
{
    protected ?int $id;
    protected int $userId;
    protected string $title;
    protected string $content;
    protected string $createdAt;
    protected string $updatedAt;

    protected function __construct(?int $id, int $userId, string $title, string $content, string $createdAt, string $updatedAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function new(int $userId, string $title, string $content)
    {
        return new self(
            null,
            $userId,
            $title,
            $content,
            new DateTime('now', new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            new DateTime('now', new DateTimeZone('UTC'))->format('Y-m-d H:i:s')
        );
    }

    public static function fromDB(array $data)
    {
        return new self(
            $data['id'],
            $data['user_id'],
            $data['title'],
            $data['content'],
            $data['created_at'],
            $data['updated_at'],
        );
    }

    public function getId()
    {
        return $this->id ?? null;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }
}
