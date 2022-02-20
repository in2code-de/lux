<?php
namespace In2code\Lux\Hooks;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinExternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotoutInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class PageLayoutHeader
 * to show analysis or leads in the page module in backend
 */
class PageOverview
{
    /**
     * @var string
     */
    protected $templatePathAndFile = 'EXT:lux/Resources/Private/Templates/Backend/PageOverview.html';

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * @var PagevisitRepository|null
     */
    protected $pagevisitRepository = null;

    /**
     * @var LinkclickRepository|null
     */
    protected $linkclickRepository = null;

    /**
     * @var DownloadRepository|null
     */
    protected $downloadRepository = null;

    /**
     * @var LogRepository|null
     */
    protected $logRepository = null;

    /**
     * PageOverview constructor.
     * @param VisitorRepository|null $visitorRepository
     * @param PagevisitRepository|null $pagevisitRepository
     * @param LinkclickRepository|null $linkclickRepository
     * @param DownloadRepository|null $downloadRepository
     * @param LogRepository|null $logRepository
     * @param RenderingTimeService|null $renderingTimeService to initialize renderingTimes
     * @param CacheLayer|null $cacheLayer
     * @throws Exception
     */
    public function __construct(
        VisitorRepository $visitorRepository = null,
        PagevisitRepository $pagevisitRepository = null,
        LinkclickRepository $linkclickRepository = null,
        DownloadRepository $downloadRepository = null,
        LogRepository $logRepository = null,
        RenderingTimeService $renderingTimeService = null,
        CacheLayer $cacheLayer = null
    ) {
        $this->visitorRepository
            = $visitorRepository ?: ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $this->pagevisitRepository
            = $pagevisitRepository ?: ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $this->linkclickRepository
            = $linkclickRepository ?: ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        $this->downloadRepository
            = $downloadRepository ?: ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $this->logRepository = $logRepository ?: ObjectUtility::getObjectManager()->get(LogRepository::class);
        $this->renderingTimeService
            = $renderingTimeService ?: ObjectUtility::getObjectManager()->get(RenderingTimeService::class);
        $this->cacheLayer = $cacheLayer ?: ObjectUtility::getObjectManager()->get(CacheLayer::class);
    }

    /**
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     * @throws ConfigurationException
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    public function render(array $parameters, PageLayoutController $plController): string
    {
        unset($parameters);
        $this->cacheLayer->initialize(__CLASS__, __FUNCTION__);
        $content = '';
        if ($this->isPageOverviewEnabled($plController)) {
            $pageIdentifier = $plController->id;
            $session = BackendUtility::getSessionValue('toggle', 'PageOverview', 'General');
            $arguments = $this->getArguments(ConfigurationUtility::getPageOverviewView(), $pageIdentifier, $session);
            return $this->getContent($arguments);
        }
        return $content;
    }

    /**
     * @param string $view
     * @param int $pageIdentifier
     * @param array $session
     * @return array
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    protected function getArguments(string $view, int $pageIdentifier, array $session): array
    {
        $arguments = [
            'pageIdentifier' => $pageIdentifier,
            'cacheLayer' => $this->cacheLayer,
            'status' => $session['status'] ?? 'show',
            'view' => $view,
            'visitors' => $this->visitorRepository->findByVisitedPageIdentifier($pageIdentifier),
        ];

        if ($this->cacheLayer->isCacheAvailable('PageOverviewTitle' . $pageIdentifier) === false) {
            $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_LAST7DAYS)->setSearchterm($pageIdentifier);
            $delta = $this->pagevisitRepository->compareAmountPerPage(
                $pageIdentifier,
                $filter,
                ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
            );
            $arguments += [
                'abandons' => $this->pagevisitRepository->findAbandonsForPage($pageIdentifier, $filter),
                'delta' => $delta,
                'deltaIconPath' => $delta >= 0 ? 'Icons/increase.svg' : 'Icons/decrease.svg',
                'visits' => $this->pagevisitRepository->findAmountPerPage($pageIdentifier, $filter),
                'visitsLastWeek' => $this->pagevisitRepository->findAmountPerPage(
                    $pageIdentifier,
                    ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
                ),
                'gotinInternal' => GeneralUtility::makeInstance(
                    GotinInternalDataProvider::class,
                    $filter
                )->get(),
                'gotinExternal' => GeneralUtility::makeInstance(
                    GotinExternalDataProvider::class,
                    $filter
                )->get(),
                'gotoutInternal' => GeneralUtility::makeInstance(GotoutInternalDataProvider::class, $filter)->get(),
                'gotout' => '',
                'numberOfVisitorsData' => GeneralUtility::makeInstance(
                    PagevisistsDataProvider::class,
                    ObjectUtility::getFilterDto()->setSearchterm((string)$pageIdentifier)
                ),
                'downloadAmount' => $this->downloadRepository->findAmountByPageIdentifierAndTimeFrame(
                    $pageIdentifier,
                    $filter
                ),
                'conversionAmount' => $this->logRepository->findAmountOfIdentifiedLogsByPageIdentifierAndTimeFrame(
                    $pageIdentifier,
                    $filter
                ),
                'linkclickAmount' => $this->linkclickRepository->getAmountOfLinkclicksByPageIdentifierAndTimeframe(
                    $pageIdentifier,
                    $filter
                ),
            ];
        }
        return $arguments;
    }

    /**
     * @param array $arguments
     * @return string
     * @throws Exception
     */
    protected function getContent(array $arguments): string
    {
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->templatePathAndFile));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple($arguments);
        return $standaloneView->render();
    }

    /**
     * @param PageLayoutController $plController
     * @return bool
     */
    protected function isPageOverviewEnabled(PageLayoutController $plController): bool
    {
        $row = BackendUtilityCore::getRecord('pages', $plController->id, 'hidden');
        return $row['hidden'] !== 1;
    }
}
