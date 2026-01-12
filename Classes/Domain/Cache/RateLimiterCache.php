<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Cache;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * Cache layer for rate limiting
 *
 * Stores request counters per fingerprint with 60-second TTL
 * Uses TYPO3 cache framework for automatic expiration and backend flexibility
 */
class RateLimiterCache
{
    public const CACHE_KEY = 'luxratelimiter';
    public const TTL = 60; // 60 seconds = 1 minute window

    protected FrontendInterface $cache;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getCount(string $fingerprintHash): int
    {
        $cacheIdentifier = $this->getCacheIdentifier($fingerprintHash);
        $data = $this->cache->get($cacheIdentifier);
        return (int)($data['count'] ?? 0);
    }

    public function incrementAndGet(string $fingerprintHash): int
    {
        $cacheIdentifier = $this->getCacheIdentifier($fingerprintHash);
        $currentCount = $this->getCount($fingerprintHash);
        $newCount = $currentCount + 1;

        // Store with 60-second TTL
        $this->cache->set(
            $cacheIdentifier,
            ['count' => $newCount, 'timestamp' => time()],
            [self::CACHE_KEY],
            self::TTL
        );

        return $newCount;
    }

    /**
     * Generate cache identifier from fingerprint hash
     * Format: ratelimit_[first-16-chars-of-hash]
     *
     * @param string $fingerprintHash
     * @return string
     */
    protected function getCacheIdentifier(string $fingerprintHash): string
    {
        $shortHash = substr($fingerprintHash, 0, 16);
        return 'ratelimit_' . $shortHash;
    }
}
