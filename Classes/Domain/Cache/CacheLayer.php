<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

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
     * @var string
     */
    protected $identifier = '';

    /**
     * @var AbstractLayer|null
     */
    protected $cacheLayer = null;

    /**
     * Constructor
     *
     * @throws NoSuchCacheException
     */
    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(CacheLayer::CACHE_KEY);
    }

    /**
     * @param string $class
     * @param string $function
     * @param string $identifier
     * @return void
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    protected function initialize(string $class, string $function, string $identifier = ''): void
    {
        $this->cacheName = $class . '->' . $function;
        $this->identifier = $identifier;
        $layerClassName = CacheLayerUtility::getCachelayerClassByCacheName($this->cacheName);
        $this->cacheLayer = GeneralUtility::makeInstance($layerClassName);
        $this->cacheLayer->initialize($this->cacheName, $this->identifier);
    }

    /**
     * @param string $class
     * @param string $function
     * @param string $identifier
     * @return array
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getArguments(string $class, string $function, string $identifier = ''): array
    {
        $this->initialize($class, $function, $identifier);
        if (ConfigurationUtility::isUseCacheLayerEnabled()) {
            return $this->getArgumentsWithEnabledCacheLayer();
        }
        return $this->getArgumentsWithoutEnabledCacheLayer();
    }

    /**
     * @return array
     * @throws ConfigurationException
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws ExceptionExtbaseObject
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws UnexpectedValueException
     */
    protected function getArgumentsWithEnabledCacheLayer(): array
    {
        if ($this->isCacheAvailable()) {
            return array_merge($this->getFromCache(), $this->cacheLayer->getUncachableArguments());
        }

        $arguments = $this->cacheLayer->getAllArguments();
        $this->cacheArguments($arguments);
        return $arguments;
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws ExceptionExtbaseObject
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     */
    protected function getArgumentsWithoutEnabledCacheLayer(): array
    {
        return $this->cacheLayer->getAllArguments();
    }

    /**
     * @param string $class
     * @param string $function
     * @param string $identifier
     * @return void
     * @throws ConfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    public function warmupCaches(string $class, string $function, string $identifier = ''): void
    {
        $this->initialize($class, $function, $identifier);
        $this->getArguments($class, $function, $identifier);
    }

    /**
     * @return void
     */
    public function flushCaches(): void
    {
        $this->cache->flush();
    }

    /**
     * @return array
     */
    public function getFromCache(): array
    {
        return $this->cache->get($this->getCacheIdentifier());
    }

    /**
     * @param array $arguments value to cache
     * @return void
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public function cacheArguments(array $arguments): void
    {
        if ($this->cacheLayer->getCacheLifetime() > 0) {
            $this->cache->set(
                $this->getCacheIdentifier(),
                $arguments,
                [self::CACHE_KEY],
                $this->cacheLayer->getCacheLifetime()
            );
        }
    }

    /**
     * @return bool
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public function isCacheAvailable(): bool
    {
        return $this->cacheLayer->getCacheLifetime() > 0 && $this->cache->get($this->getCacheIdentifier()) !== false;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier(): string
    {
        return md5($this->cacheName . $this->identifier . self::CACHE_KEY);
    }
}
