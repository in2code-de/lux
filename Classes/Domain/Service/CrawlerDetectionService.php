<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use In2code\Lux\Events\DetectCrawlerEvent;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Psr\EventDispatcher\EventDispatcherInterface;

class CrawlerDetectionService
{
    public function __construct(
        protected CrawlerDetect $crawlerDetect,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function isCrawler(string $userAgent, array $additionalUserAgentParts = []): bool
    {
        $crawler = false;
        if ($userAgent !== '') {
            $crawler = $this->isCrawlerByUserAgentParts($userAgent, $additionalUserAgentParts) ||
                $this->crawlerDetect->isCrawler($userAgent);
        }

        /** @var DetectCrawlerEvent $event */
        $event = $this->eventDispatcher->dispatch(new DetectCrawlerEvent($userAgent, $crawler));
        return $event->isCrawler();
    }

    protected function isCrawlerByUserAgentParts(string $userAgent, array $additionalUserAgentParts): bool
    {
        foreach ($additionalUserAgentParts as $userAgentPart) {
            $userAgentPart = trim((string)$userAgentPart);
            if ($userAgentPart !== '' && stristr($userAgent, $userAgentPart) !== false) {
                return true;
            }
        }
        return false;
    }
}
