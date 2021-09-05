<?php
namespace In2code\Lux\Hooks;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
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
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class PageLayoutHeader
 * to show leads in the page module in backend
 */
class PageOverview
{
    const CACHE_KEY = 'lux_pagemodule_view';

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
     * @var FrontendInterface
     */
    protected $cacheInstance = null;

    /**
     * PageOverview constructor.
     * @param VisitorRepository|null $visitorRepository
     * @param PagevisitRepository|null $pagevisitRepository
     * @param LinkclickRepository|null $linkclickRepository
     * @throws NoSuchCacheException
     */
    public function __construct(
        VisitorRepository $visitorRepository = null,
        PagevisitRepository $pagevisitRepository = null,
        LinkclickRepository $linkclickRepository = null,
        DownloadRepository $downloadRepository = null,
        LogRepository $logRepository = null
    ) {
        $this->visitorRepository = $visitorRepository ?: GeneralUtility::makeInstance(VisitorRepository::class);
        $this->pagevisitRepository = $pagevisitRepository ?: GeneralUtility::makeInstance(PagevisitRepository::class);
        $this->linkclickRepository = $linkclickRepository ?: GeneralUtility::makeInstance(LinkclickRepository::class);
        $this->downloadRepository = $downloadRepository ?: GeneralUtility::makeInstance(DownloadRepository::class);
        $this->logRepository = $logRepository ?: GeneralUtility::makeInstance(LogRepository::class);
        $this->cacheInstance = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_KEY);
    }

    /**
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(array $parameters, PageLayoutController $plController): string
    {
        unset($parameters);
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
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getArguments(string $view, int $pageIdentifier, array $session): array
    {
        $arguments = [];
        if ($this->getCacheLifeTime() > 0) {
            $arguments = $this->cacheInstance->get($this->getCacheIdentifier($pageIdentifier));
        }
        if (empty($arguments)) {
            $arguments = [
                'visitors' => $this->visitorRepository->findByVisitedPageIdentifier($pageIdentifier),
                'pageIdentifier' => $pageIdentifier,
                'view' => $view,
                'status' => $session['status'] ?: 'show'
            ];
            if ($view === 'analysis') {
                $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_LAST7DAYS)->setSearchterm($pageIdentifier);
                $arguments += [
                    'visits' => $this->pagevisitRepository->findAmountPerPage($pageIdentifier, $filter),
                    'visitsLastWeek' => $this->pagevisitRepository->findAmountPerPage(
                        $pageIdentifier,
                        ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
                    ),
                    'abandons' => $this->pagevisitRepository->findAbandonsForPage($pageIdentifier, $filter),
                    'delta' => $this->pagevisitRepository->compareAmountPerPage(
                        $pageIdentifier,
                        $filter,
                        ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
                    ),
                    'gotinInternal' => ObjectUtility::getObjectManager()->get(
                        GotinInternalDataProvider::class,
                        $filter
                    )->get(),
                    'gotinExternal' => ObjectUtility::getObjectManager()->get(
                        GotinExternalDataProvider::class,
                        $filter
                    )->get(),
                    'gotoutInternal'
                    => ObjectUtility::getObjectManager()->get(GotoutInternalDataProvider::class, $filter)->get(),
                    'gotout' => '',
                    'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(
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
            if ($this->getCacheLifeTime() > 0) {
                $this->cacheInstance->set(
                    $this->getCacheIdentifier($pageIdentifier),
                    $arguments,
                    [self::CACHE_KEY],
                    $this->getCacheLifeTime()
                );
            }
        }

        return $arguments;
    }

    /**
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getCacheLifeTime(): int
    {
        return ConfigurationUtility::getPageOverviewCacheLifeTime();
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

    /**
     * @param int $pageIdentifier
     * @return string
     */
    protected function getCacheIdentifier(int $pageIdentifier): string
    {
        return md5($pageIdentifier . self::CACHE_KEY);
    }
}
