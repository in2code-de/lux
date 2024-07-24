<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Image;

use Buchin\GoogleImageGrabber\GoogleImageGrabber;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\FileUtility;
use Throwable;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

abstract class AbstractImageService implements ImageServiceInterface
{
    protected int $cacheLifeTime = DateUtility::SECONDS_DAY;
    protected string $defaultFile = 'EXT:lux/Resources/Public/Images/AvatarDefault.svg';
    protected ?FrontendInterface $cacheInstance = null;
    protected array $arguments = [];

    /**
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getUrl(array $arguments = []): string
    {
        $this->arguments = $arguments;
        $url = $this->getUrlFromCache();
        if ($url === '') {
            $url = $this->buildImageUrl();
            $this->cacheUrl($url);
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

    /**
     * @param string $url
     * @param string $searchTerm
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getImageFromGoogle(string $url, string $searchTerm): string
    {
        $configuration = [
            'all',
            'nogravatar',
        ];
        if (empty($url) && class_exists(GoogleImageGrabber::class)) {
            if (in_array(ConfigurationUtility::getLeadImageFromExternalSourcesConfiguration(), $configuration)) {
                try {
                    $images = GoogleImageGrabber::grab($searchTerm);
                    foreach ($images as $image) {
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

    protected function cacheUrl(string $url): void
    {
        if ($url !== '') {
            $this->cacheInstance->set(
                $this->getCacheIdentifier(),
                $url,
                [$this->getCacheKey()],
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
}
