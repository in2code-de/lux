<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Buchin\GoogleImageGrabber\GoogleImageGrabber;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\FileUtility;
use Throwable;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

class VisitorImageService
{
    const CACHE_KEY = 'lux_visitor_imageurl';

    protected ?Visitor $visitor = null;

    protected string $defaultFile = 'EXT:lux/Resources/Public/Images/AvatarDefault.svg';

    /**
     * Size in px
     *
     * @var int
     */
    protected int $size = 150;

    /**
     * Default cache live time is 24h
     *
     * @var int
     */
    protected int $cacheLifeTime = 86400;

    protected ?FrontendInterface $cacheInstance = null;

    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->cacheInstance = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_KEY);
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getUrl(): string
    {
        $url = $this->getUrlFromCache();
        if ($url === '') {
            $url = $this->buildImageUrl();
            $this->cacheUrl($url);
        }
        return $url;
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
        $url = $this->getImageFromGoogle($url);
        $url = $this->getDefaultUrl($url);
        return $url;
    }

    protected function getImageUrlFromFrontenduser(string $url): string
    {
        if ($this->isVisitorWithFrontendUserImage()) {
            foreach ($this->visitor->getFrontenduser()->getImage() as $imageObject) {
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
            'nogoogle',
        ];
        if (empty($url) && $this->visitor->isIdentified()) {
            if (in_array(ConfigurationUtility::getLeadImageFromExternalSourcesConfiguration(), $configuration)) {
                $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->visitor->getEmail())))
                    . '?d=' . urlencode($this->getDefaultUrl($url)) . '&s=' . $this->size;
                $header = GeneralUtility::getUrl($gravatarUrl, 2);
                if (!empty($header)) {
                    $url = $gravatarUrl;
                }
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
    protected function getImageFromGoogle(string $url): string
    {
        $configuration = [
            'all',
            'nogravatar',
        ];
        if (empty($url) && $this->visitor->isIdentified() && class_exists(GoogleImageGrabber::class)) {
            if (in_array(ConfigurationUtility::getLeadImageFromExternalSourcesConfiguration(), $configuration)) {
                try {
                    $images = GoogleImageGrabber::grab($this->visitor->getEmail());
                    foreach ((array)$images as $image) {
                        if (!empty($image['url']) && FileUtility::isImageFile($image['url']) && $image['width'] > 25) {
                            $url = $image['url'];
                            break;
                        }
                    }
                } catch (Throwable $exception) {
                    // Catch exceptions from third party package like allow_url_fopen=0 on server
                }
            }
        }
        return $url;
    }

    protected function getDefaultUrl(string $url): string
    {
        if (empty($url)) {
            $url = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($this->defaultFile));
        }
        return $url;
    }

    protected function isVisitorWithFrontendUserImage(): bool
    {
        return $this->visitor->getFrontenduser() !== null
            && $this->visitor->getFrontenduser()->getImage() !== null
            && $this->visitor->getFrontenduser()->getImage()->count() > 0;
    }

    protected function cacheUrl(string $url): void
    {
        if ($url !== '') {
            $this->cacheInstance->set(
                $this->getCacheIdentifier(),
                $url,
                [self::CACHE_KEY],
                $this->cacheLifeTime
            );
        }
    }

    protected function getUrlFromCache(): string
    {
        $url = '';
        $urlCache = $this->cacheInstance->get($this->getCacheIdentifier());
        if (!empty($urlCache)) {
            $url = $urlCache;
        }
        return $url;
    }

    protected function getCacheIdentifier(): string
    {
        return md5($this->visitor->getEmail() . self::CACHE_KEY);
    }
}
