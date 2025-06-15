<?php

namespace Src\Models;

use DateTime;
use DateTimeZone;
use HTMLPurifier;
use HTMLPurifier_Config;

class Post
{
    protected ?int $id;
    protected int $userId;
    protected string $title;
    protected string $content;
    protected string $createdAt;
    protected string $updatedAt;
    protected array $updates;

    protected function __construct(?int $id, int $userId, string $title, string $content, string $createdAt, string $updatedAt, $updates = [])
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->updates = [];
    }

    public static function new(int $userId, string $title, string $content)
    {
        return new self(
            null,
            $userId,
            strip_tags($title),
            self::purifyContent($content),
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
        if (!in_array('user_id', $this->updates)) $this->updates[] = 'user_id';
    }

    public function setTitle(string $title)
    {
        $this->title = strip_tags($title);
        if (!in_array('title', $this->updates)) $this->updates[] = 'title';
    }

    public function setContent(string $content)
    {

        # clear the HTML content received
        $this->content = self::purifyContent($content);
        if (!in_array('content', $this->updates)) $this->updates[] = 'content';
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
                'user_id' => $this->getUserId(),
                'title' => $this->getTitle(),
                'content' => $this->getContent(),
                default => null
            };
        }
        return $updates;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }

    public static function purifyContent(string $content)
    {
        # create default configuration for the HTML purifier instance
        $config = HTMLPurifier_Config::createDefault();
        # set allowed HTML tags in the configuration
        $config->set('HTML.Allowed', 'div,h3,h4,h5,h6,p,strong,em,ul,ol,li,a[href],br');
        # create the HTML purifier instance
        $purifier = new HTMLPurifier($config);
        # return the purified HTML
        return $purifier->purify($content ?? '');
    }
}
