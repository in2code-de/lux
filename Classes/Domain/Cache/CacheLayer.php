<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
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
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
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
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws InvalidConfigurationTypeException
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
     */
    public function getArguments(string $class, string $function, string $identifier = ''): array
    {
        $this->initialize($class, $function, $identifier);

        if ($this->isCacheAvailable()) {
            return array_merge($this->getFromCache(), $this->cacheLayer->getUncachableArguments());
        }

        $arguments = $this->cacheLayer->getAllArguments();
        $this->cacheArguments($arguments);
        return $arguments;
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
     */
    public function isCacheAvailable(): bool
    {
        return $this->cacheLayer->getCacheLifetime() > 0 && $this->cache->has($this->getCacheIdentifier());
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier(): string
    {
        return md5($this->cacheName . $this->identifier . self::CACHE_KEY);
    }
}
