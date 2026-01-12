<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Cache\RateLimiterCache;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Exception\RateLimitException;
use In2code\Lux\Utility\ConfigurationUtility;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;

class RateLimiter
{
    protected RateLimiterCache $cache;
    protected LoggerInterface $logger;

    public function __construct(
        RateLimiterCache $cache,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function __invoke(StopAnyProcessBeforePersistenceEvent $event)
    {
        if ($this->isRateLimitingEnabled()) {
            $fingerprint = $event->getFingerprint();
            $fingerprintHash = $fingerprint->getValue();

            if ($fingerprintHash !== '') {
                $currentCount = $this->cache->incrementAndGet($fingerprintHash);
                if ($currentCount > $this->getRateLimit()) {
                    $this->logRateLimitExceeded($fingerprintHash, $currentCount, $this->getRateLimit());
                    throw new RateLimitException(
                        'Rate limit exceeded: ' . $currentCount . ' requests in last minute',
                        1768214806
                    );
                }
            }
        }
    }

    protected function isRateLimitingEnabled(): bool
    {
        try {
            return ConfigurationUtility::isRateLimitingEnabled();
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $exception) {
            return true;
        }
    }

    protected function getRateLimit(): int
    {
        try {
            return ConfigurationUtility::getRateLimitRequestsPerMinute();
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $exception) {
            return ConfigurationUtility::RATE_LIMIT;
        }
    }

    protected function logRateLimitExceeded(string $fingerprintHash, int $currentCount, int $limit): void
    {
        if (ConfigurationUtility::isExceptionLoggingActivated()) {
            $this->logger->warning('Rate limit exceeded', [
                'fingerprint' => $fingerprintHash,
                'count' => $currentCount,
                'limit' => $limit,
                'component' => 'RateLimiter',
            ]);
        }
    }
}
