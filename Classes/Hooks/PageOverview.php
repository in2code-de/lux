<?php

namespace In2code\Lux\Hooks;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
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
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class PageLayoutHeader
 * to show analysis or leads in the page module in backend
 */
class PageOverview
{
    protected string $templatePathAndFile = 'EXT:lux/Resources/Private/Templates/Backend/PageOverview.html';

    protected VisitorRepository $visitorRepository;
    protected PagevisitRepository $pagevisitRepository;
    protected LinkclickRepository $linkclickRepository;
    protected DownloadRepository $downloadRepository;
    protected LogRepository $logRepository;
    protected RenderingTimeService $renderingTimeService;
    protected CacheLayer $cacheLayer;

    public function __construct(
        VisitorRepository $visitorRepository,
        PagevisitRepository $pagevisitRepository,
        LinkclickRepository $linkclickRepository,
        DownloadRepository $downloadRepository,
        LogRepository $logRepository,
        RenderingTimeService $renderingTimeService,
        CacheLayer $cacheLayer
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->pagevisitRepository = $pagevisitRepository;
        $this->linkclickRepository = $linkclickRepository;
        $this->downloadRepository = $downloadRepository;
        $this->logRepository = $logRepository;
        $this->renderingTimeService = $renderingTimeService; // initialize renderingTimes
        $this->cacheLayer = $cacheLayer;
    }

    /**
     * Called from PSR-14 for TYPO3 12
     *
     * @param ModifyPageLayoutContentEvent $event
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    public function eventRegistration(ModifyPageLayoutContentEvent $event): void
    {
        $queryParams = $event->getRequest()->getQueryParams();
        $pageIdentifier = (int)($queryParams['id'] ?? 0);
        $event->addHeaderContent($this->renderContent($pageIdentifier));
    }

    /**
     * Called from ext_localconf.php for TYPO3 11
     * Todo: Can be removed when TYPO3 11 support is dropped
     *
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     * @throws ExceptionDbalDriver
     */
    public function render(array $parameters, PageLayoutController $plController): string
    {
        unset($parameters);
        return $this->renderContent($plController->id);
    }

    /**
     * @param int $pageIdentifier
     * @return string
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    protected function renderContent(int $pageIdentifier): string
    {
        $this->cacheLayer->initialize(__CLASS__, 'render');
        $content = '';
        if ($this->isPageOverviewEnabled($pageIdentifier)) {
            $session = BackendUtility::getSessionValue('toggle', 'pageOverview', 'General');
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
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     * @throws ExceptionDbalDriver
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

    protected function getContent(array $arguments): string
    {
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->templatePathAndFile));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple($arguments);
        return $standaloneView->render();
    }

    /**
     * @param int $pageIdentifier
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function isPageOverviewEnabled(int $pageIdentifier): bool
    {
        $row = BackendUtilityCore::getRecord('pages', $pageIdentifier, 'hidden');
        return ConfigurationUtility::isPageOverviewDisabled() === false && $row['hidden'] !== 1;
    }
}
