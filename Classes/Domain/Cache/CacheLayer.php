<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;

/**
 * CacheLayer
 */
final class CacheLayer
{
    const CACHE_KEY = 'luxcachelayer';

    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheName = '';

    /**
     * Constructor
     *
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $class
     * @param string $function
     * @return void
     */
    public function initialize(string $class, string $function): void
    {
        $this->cacheName = $class . '->' . $function;
    }

    /**
     * @param string $html
     * @param string $identifier
     * @return void
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public function setHtml(string $html, string $identifier): void
    {
        if ($this->getCacheLifetime() > 0) {
            $this->cache->set(
                $this->getCacheIdentifier($identifier),
                ['html' => $html],
                [self::CACHE_KEY],
                $this->getCacheLifetime()
            );
        }
    }

    /**
     * @param string $identifier
     * @return string
     * @throws ConfigurationException
     */
    public function getHtml(string $identifier): string
    {
        $cache = $this->cache->get($this->getCacheIdentifier($identifier));
        return $cache['html'];
    }

    /**
     * @param string $identifier
     * @return bool
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function isCacheAvailable(string $identifier): bool
    {
        return ConfigurationUtility::isUseCacheLayerEnabled()
            && $this->getCacheLifetime() > 0
            && $this->cache->has($this->getCacheIdentifier($identifier));
    }

    /**
     * @return void
     */
    public function flushCaches(): void
    {
        $this->cache->flush();
    }

    /**
     * @param string $identifier
     * @return string
     * @throws ConfigurationException
     */
    protected function getCacheIdentifier(string $identifier): string
    {
        if ($this->cacheName === '') {
            throw new ConfigurationException('CacheName must not be empty', 1645039379);
        }
        return md5($this->cacheName . $identifier . self::CACHE_KEY);
    }

    /**
     * @return int
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public function getCacheLifetime(): int
    {
        if ($this->cacheName === '') {
            throw new ConfigurationException('CacheName must not be empty', 1636364317);
        }
        return CacheLayerUtility::getCachelayerLifetimeByCacheName($this->cacheName);
    }
}
