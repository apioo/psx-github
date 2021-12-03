<?php

namespace PSX\Github;

class Order
{
    private string $userName;
    private string $avatarUrl;
    private string $htmlUrl;
    private int $price;

    public function __construct(string $userName, string $avatarUrl, string $htmlUrl, int $price)
    {
        $this->userName = $userName;
        $this->avatarUrl = $avatarUrl;
        $this->htmlUrl = $htmlUrl;
        $this->price = $price;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    public function getHtmlUrl(): string
    {
        return $this->htmlUrl;
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}
