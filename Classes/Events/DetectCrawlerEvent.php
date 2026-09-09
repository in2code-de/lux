<?php

declare(strict_types=1);

namespace In2code\Lux\Events;

final class DetectCrawlerEvent
{
    protected string $userAgent;
    protected bool $crawler;

    public function __construct(string $userAgent, bool $crawler)
    {
        $this->userAgent = $userAgent;
        $this->crawler = $crawler;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function isCrawler(): bool
    {
        return $this->crawler;
    }

    public function setCrawler(bool $crawler): self
    {
        $this->crawler = $crawler;
        return $this;
    }
}
