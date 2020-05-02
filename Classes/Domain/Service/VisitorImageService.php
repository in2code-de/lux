<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Class VisitorImageService
 */
class VisitorImageService
{
    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * @var string
     */
    protected $defaultFile = 'typo3conf/ext/lux/Resources/Public/Images/AvatarDefault.svg';

    /**
     * Size in px
     *
     * @var int
     */
    protected $size = 150;

    /**
     * VisitorImageService constructor.
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getImageUrl(): string
    {
        $url = '';
        $url = $this->getImageUrlFromFrontenduser($url);
        $url = $this->getImageUrlFromGravatar($url);
        $url = $this->getImageFromGoogle($url);
        $url = $this->getDefaultUrl($url);
        return $url;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception
     */
    protected function getImageUrlFromFrontenduser(string $url): string
    {
        if ($this->isVisitorWithFrontendUserImage()) {
            foreach ($this->visitor->getFrontenduser()->getImage() as $imageObject) {
                /** @var File $file */
                $file = $imageObject->getOriginalResource()->getOriginalFile();
                $imageService = ObjectUtility::getObjectManager()->get(ImageService::class);
                $image = $imageService->getImage('', $file, false);
                $processConfiguration = [
                    'width' => (string)$this->size . 'c',
                    'height' => (string)$this->size . 'c'
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
     */
    protected function getImageUrlFromGravatar(string $url): string
    {
        if (empty($url) && $this->visitor->isIdentified()) {
            $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->visitor->getEmail())))
                . '?d=' . urlencode($this->getDefaultUrl($url)) . '&s=' . $this->size;
            $header = GeneralUtility::getUrl($gravatarUrl, 2);
            if (!empty($header)) {
                $url = $gravatarUrl;
            }
        }
        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getImageFromGoogle(string $url): string
    {
        if (empty($url) && $this->visitor->isIdentified()
            && class_exists(\Buchin\GoogleImageGrabber\GoogleImageGrabber::class)) {
            $images = \Buchin\GoogleImageGrabber\GoogleImageGrabber::grab($this->visitor->getEmail());
            foreach ((array)$images as $image) {
                if (!empty($image['url']) && FileUtility::isImageFile($image['url'])) {
                    $url = $image['url'];
                    break;
                }
            }
        }
        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getDefaultUrl(string $url): string
    {
        if (empty($url)) {
            $url = StringUtility::getCurrentUri() . $this->defaultFile;
        }
        return $url;
    }

    /**
     * @return bool
     */
    protected function isVisitorWithFrontendUserImage(): bool
    {
        return $this->visitor->getFrontenduser() !== null
            && $this->visitor->getFrontenduser()->getImage() !== null
            && $this->visitor->getFrontenduser()->getImage()->count() > 0;
    }
}
