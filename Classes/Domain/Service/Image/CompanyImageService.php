<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Image;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CompanyImageService extends AbstractImageService
{
    public const CACHE_KEY = 'lux_visitor_imageurlcompany';

    public function __construct()
    {
        $this->cacheInstance = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_KEY);
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function buildImageUrl(): string
    {
        $url = '';
        $url = $this->getImageFromBing($url, $this->arguments['company']->getTitle());
        $url = $this->getDefaultUrl($url);
        return $url;
    }

    public function getCacheIdentifier(): string
    {
        return md5($this->arguments['company']->getUid() . $this->getCacheKey());
    }

    public function getCacheKey(): string
    {
        return self::CACHE_KEY;
    }
}
