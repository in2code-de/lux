<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\FileService;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class DownloadTracker add a download record to a visitor
 */
class DownloadTracker
{
    use SignalTrait;

    /**
     * @var Visitor|null
     */
    protected $visitor = null;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * DownloadTracker constructor.
     *
     * @param Visitor $visitor
     * @throws Exception
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
    }

    /**
     * @param string $href
     * @param int $pageIdentifier
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function addDownload(string $href, int $pageIdentifier)
    {
        if ($this->isDownloadAddingEnabled($href)) {
            $download = $this->getAndPersistNewDownload($href, $pageIdentifier);
            $this->visitor->addDownload($download);
            $download->setVisitor($this->visitor);
            $this->visitorRepository->update($this->visitor);
            $this->visitorRepository->persistAll();
            $this->signalDispatch(__CLASS__, 'addDownload', [$download, $this->visitor]);
        }
    }

    /**
     * @param string $href
     * @param int $pageIdentifier
     * @return Download
     * @throws Exception
     * @throws IllegalObjectTypeException
     */
    protected function getAndPersistNewDownload(string $href, int $pageIdentifier): Download
    {
        $fileService = ObjectUtility::getObjectManager()->get(FileService::class);
        $file = $fileService->getFileFromHref($href);
        $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
        $page = $pageRepository->findByIdentifier($pageIdentifier);
        $download = ObjectUtility::getObjectManager()->get(Download::class)
            ->setHref($href)
            ->setPage($page)
            ->setDomain();
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
     * @throws Exception
     */
    protected function isDownloadAddingEnabled(string $href): bool
    {
        return !empty($href) && $this->isFileExtensionAllowedToBeTracked($href)
            && $this->visitor->isNotBlacklisted() && $this->isEnabledDownloadTrackingInSettings();
    }

    /**
     * @param string $href
     * @return bool
     * @throws Exception
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
     * @throws Exception
     */
    protected function isEnabledDownloadTrackingInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['tracking']['assetDownloads']['_enable'])
            && $settings['tracking']['assetDownloads']['_enable'] === '1';
    }
}
