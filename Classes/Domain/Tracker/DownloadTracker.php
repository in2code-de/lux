<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\FileService;
use In2code\Lux\Events\Log\DownloadEvent;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class DownloadTracker
{
    protected ?Visitor $visitor = null;
    protected ?VisitorRepository $visitorRepository = null;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param string $href
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidConfigurationTypeException
     */
    public function addDownload(string $href, int $pageIdentifier): void
    {
        if ($this->isDownloadAddingEnabled($href)) {
            $download = $this->getAndPersistNewDownload($href, $pageIdentifier);
            $this->visitor->addDownload($download);
            $download->setVisitor($this->visitor);
            $this->visitorRepository->update($this->visitor);
            $this->visitorRepository->persistAll();
            $this->eventDispatcher->dispatch(
                GeneralUtility::makeInstance(DownloadEvent::class, $this->visitor, $download)
            );
        }
    }

    /**
     * @param string $href
     * @param int $pageIdentifier
     * @return Download
     * @throws IllegalObjectTypeException
     */
    protected function getAndPersistNewDownload(string $href, int $pageIdentifier): Download
    {
        $fileService = GeneralUtility::makeInstance(FileService::class);
        $file = $fileService->getFileFromHref($href);
        $downloadRepository = GeneralUtility::makeInstance(DownloadRepository::class);
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $page = $pageRepository->findByIdentifier($pageIdentifier);
        $download = GeneralUtility::makeInstance(Download::class)->setHref($href)->setPage($page)->setDomain();
        if ($file !== null) {
            $download->setFile($file);
        }
        $downloadRepository->add($download);
        $downloadRepository->persistAll();
        return $download;
    }

    /**
     * Check if
     * - href is not empty
     * - file extension is allowed to be tracked
     * - visitor ist not blacklisted
     * - is download tracking enabled in general
     *
     * @param string $href
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isDownloadAddingEnabled(string $href): bool
    {
        return !empty($href) && $this->isFileExtensionAllowedToBeTracked($href)
            && $this->visitor->isNotBlacklisted() && $this->isEnabledDownloadTrackingInSettings();
    }

    /**
     * @param string $href
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isFileExtensionAllowedToBeTracked(string $href): bool
    {
        $fileExtension = FileUtility::getFileExtensionFromFilename($href);
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        if (!empty($settings['tracking']['assetDownloads']['allowedFileExtensions'])) {
            $allowed = GeneralUtility::trimExplode(
                ',',
                strtolower($settings['tracking']['assetDownloads']['allowedFileExtensions']),
                true
            );
            return in_array(strtolower($fileExtension), $allowed);
        }
        return false;
    }

    /**
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isEnabledDownloadTrackingInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['tracking']['assetDownloads']['_enable'])
            && $settings['tracking']['assetDownloads']['_enable'] === '1';
    }
}
