<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Image;

use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

class VisitorImageService extends AbstractImageService
{
    public const CACHE_KEY = 'lux_visitor_imageurl';
    protected int $size = 150;

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
        $url = $this->getImageUrlFromFrontenduser($url);
        $url = $this->getImageUrlFromGravatar($url);
        if ($this->arguments['visitor']->isIdentified()) {
            $url = $this->getImageFromBing($url, $this->arguments['visitor']->getEmail());
        }
        $url = $this->getDefaultUrl($url);
        return $url;
    }

    protected function getImageUrlFromFrontenduser(string $url): string
    {
        if ($this->isVisitorWithFrontendUserImage()) {
            foreach ($this->arguments['visitor']->getFrontenduser()->getImage() as $imageObject) {
                /** @var File $file */
                $file = $imageObject->getOriginalResource()->getOriginalFile();
                $imageService = GeneralUtility::makeInstance(ImageService::class);
                $image = $imageService->getImage('', $file, false);
                $processConfiguration = [
                    'width' => (string)$this->size . 'c',
                    'height' => (string)$this->size . 'c',
                ];
                $processedImage = $imageService->applyProcessingInstructions($image, $processConfiguration);
                $url = $imageService->getImageUri($processedImage, true);
            }
        }
        return $url;
    }

    /**
     * @param string $url
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getImageUrlFromGravatar(string $url): string
    {
        $configuration = [
            'all',
            'nosearchengine',
        ];
        if (empty($url) && $this->arguments['visitor']->isIdentified()) {
            if (in_array(ConfigurationUtility::getLeadImageFromExternalSourcesConfiguration(), $configuration)) {
                $gravatarUrl = 'https://www.gravatar.com/avatar/'
                    . md5(strtolower(trim($this->arguments['visitor']->getEmail())))
                    . '?d=' . urlencode($this->getDefaultUrl($url)) . '&s=' . $this->size;
                $header = GeneralUtility::getUrl($gravatarUrl, 2);
                if (!empty($header)) {
                    $url = $gravatarUrl;
                }
            }
        }
        return $url;
    }

    protected function isVisitorWithFrontendUserImage(): bool
    {
        return $this->arguments['visitor']->getFrontenduser() !== null
            && $this->arguments['visitor']->getFrontenduser()->getImage() !== null
            && $this->arguments['visitor']->getFrontenduser()->getImage()->count() > 0;
    }

    public function getCacheIdentifier(): string
    {
        return md5($this->arguments['visitor']->getEmail() . $this->getCacheKey());
    }

    public function getCacheKey(): string
    {
        return self::CACHE_KEY;
    }
}
