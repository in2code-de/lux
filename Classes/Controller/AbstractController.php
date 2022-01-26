<?php
declare(strict_types = 1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\FingerprintRepository;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\NewsRepository;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ActionController
{
    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * @var IpinformationRepository
     */
    protected $ipinformationRepository = null;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @var PagevisitRepository
     */
    protected $pagevisitsRepository = null;

    /**
     * @var PageRepository
     */
    protected $pageRepository = null;

    /**
     * @var DownloadRepository
     */
    protected $downloadRepository = null;

    /**
     * @var NewsvisitRepository
     */
    protected $newsvisitRepository = null;

    /**
     * @var NewsRepository
     */
    protected $newsRepository = null;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * @var LinklistenerRepository
     */
    protected $linklistenerRepository = null;

    /**
     * @var FingerprintRepository
     */
    protected $fingerprintRepository = null;

    /**
     * @var SearchRepository
     */
    protected $searchRepository = null;

    /**
     * @var RenderingTimeService
     */
    protected $renderingTimeService;

    /**
     * @var CacheLayer
     */
    protected $cacheLayer = null;

    /**
     * AbstractController constructor.
     * @param VisitorRepository|null $visitorRepository
     * @param IpinformationRepository|null $ipinformationRepository
     * @param LogRepository|null $logRepository
     * @param PagevisitRepository|null $pagevisitsRepository
     * @param PageRepository|null $pageRepository
     * @param DownloadRepository|null $downloadRepository
     * @param NewsvisitRepository|null $newsvisitRepository
     * @param NewsRepository|null $newsRepository
     * @param CategoryRepository|null $categoryRepository
     * @param LinkclickRepository|null $linkclickRepository
     * @param LinklistenerRepository|null $linklistenerRepository
     * @param FingerprintRepository|null $fingerprintRepository
     * @param SearchRepository|null $searchRepository
     * @param RenderingTimeService $renderingTimeService to initialize renderingTimes
     * @param CacheLayer $cacheLayer
     * @throws Exception
     */
    public function __construct(
        VisitorRepository $visitorRepository = null,
        IpinformationRepository $ipinformationRepository = null,
        LogRepository $logRepository = null,
        PagevisitRepository $pagevisitsRepository = null,
        PageRepository $pageRepository = null,
        DownloadRepository $downloadRepository = null,
        NewsvisitRepository $newsvisitRepository = null,
        NewsRepository $newsRepository = null,
        CategoryRepository $categoryRepository = null,
        LinkclickRepository $linkclickRepository = null,
        LinklistenerRepository $linklistenerRepository = null,
        FingerprintRepository $fingerprintRepository = null,
        SearchRepository $searchRepository = null,
        RenderingTimeService $renderingTimeService,
        CacheLayer $cacheLayer
    ) {
        if ($visitorRepository === null) {
            // Todo: Fallback for TYPO3 9 without symfony DI
            $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
            $ipinformationRepository = ObjectUtility::getObjectManager()->get(IpinformationRepository::class);
            $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
            $pagevisitsRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
            $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
            $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
            $newsvisitRepository = ObjectUtility::getObjectManager()->get(NewsvisitRepository::class);
            $newsRepository = ObjectUtility::getObjectManager()->get(NewsRepository::class);
            $categoryRepository = ObjectUtility::getObjectManager()->get(CategoryRepository::class);
            $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
            $linklistenerRepository = ObjectUtility::getObjectManager()->get(LinklistenerRepository::class);
            $fingerprintRepository = ObjectUtility::getObjectManager()->get(FingerprintRepository::class);
            $searchRepository = ObjectUtility::getObjectManager()->get(SearchRepository::class);
            $renderingTimeService = ObjectUtility::getObjectManager()->get(RenderingTimeService::class);
            $cacheLayer = ObjectUtility::getObjectManager()->get(CacheLayer::class);
        }
        $this->visitorRepository = $visitorRepository;
        $this->ipinformationRepository = $ipinformationRepository;
        $this->logRepository = $logRepository;
        $this->pagevisitsRepository = $pagevisitsRepository;
        $this->pageRepository = $pageRepository;
        $this->downloadRepository = $downloadRepository;
        $this->newsvisitRepository = $newsvisitRepository;
        $this->newsRepository = $newsRepository;
        $this->categoryRepository = $categoryRepository;
        $this->linkclickRepository = $linkclickRepository;
        $this->linklistenerRepository = $linklistenerRepository;
        $this->fingerprintRepository = $fingerprintRepository;
        $this->searchRepository = $searchRepository;
        $this->renderingTimeService = $renderingTimeService;
        $this->cacheLayer = $cacheLayer;
    }

    /**
     * Pass some important variables to all views
     *
     * @param ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
                'actionUpper' => ucfirst($this->getActionName())
            ]
        ]);
    }

    /**
     * Set a filter for the last 12 month
     *
     * @return void
     * @throws InvalidArgumentNameException
     * @throws Exception
     */
    protected function setFilter(): void
    {
        $this->request->setArgument('filter', ObjectUtility::getFilterDto());
    }

    /**
     * Always set a default FilterDto even if there are no filter params. In addition remove categoryScoring with 0 to
     * avoid propertymapping exceptions
     *
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    protected function setFilterExtended(): void
    {
        $filterArgument = $this->arguments->getArgument('filter');
        $filterPropMapping = $filterArgument->getPropertyMappingConfiguration();
        $filterPropMapping->allowAllProperties();

        if ($this->request->hasArgument('filter') === false) {
            $filter = BackendUtility::getSessionValue('filter', $this->getActionName(), $this->getControllerName());
        } else {
            $filter = (array)$this->request->getArgument('filter');
            BackendUtility::saveValueToSession('filter', $this->getActionName(), $this->getControllerName(), $filter);
        }

        if (array_key_exists('categoryScoring', $filter)
            && (is_array($filter['categoryScoring']) || $filter['categoryScoring'] === '')) {
            $filter['categoryScoring'] = 0;
        }
        if (isset($filter['identified']) && $filter['identified'] === '') {
            $filter['identified'] = FilterDto::IDENTIFIED_ALL;
        }
        $this->request->setArgument('filter', $filter);
    }

    /**
     * @param string $action
     * @param string $searchterm
     * @return FilterDto
     * @throws Exception
     */
    protected function getFilterFromSessionForAjaxRequests(string $action, string $searchterm = ''): FilterDto
    {
        $filterValues = BackendUtility::getSessionValue('filter', $action, $this->getControllerName());
        $filter = ObjectUtility::getFilterDto();
        if (!empty($searchterm)) {
            $filter->setSearchterm($searchterm);
        }
        if (!empty($filterValues['timeFrom'])) {
            $filter->setTimeFrom((string)$filterValues['timeFrom']);
        }
        if (!empty($filterValues['timeTo'])) {
            $filter->setTimeTo((string)$filterValues['timeTo']);
        }
        if (!empty($filterValues['scoring'])) {
            $filter->setScoring((int)$filterValues['scoring']);
        }
        if (!empty($filterValues['categoryscoring'])) {
            $filter->setCategoryScoring((int)$filterValues['categoryscoring']);
        }
        return $filter;
    }

    /**
     * @param string $redirectAction
     * @return void
     * @throws StopActionException
     */
    public function resetFilterAction(string $redirectAction): void
    {
        BackendUtility::saveValueToSession('filter', $redirectAction, $this->getControllerName(), []);
        $this->redirect($redirectAction);
    }

    /**
     * @return string like "Analysis" or "Lead"
     */
    protected function getControllerName(): string
    {
        $name = end(explode('\\', get_called_class()));
        return StringUtility::removeStringPostfix($name, 'Controller');
    }

    /**
     * @return string like "list" or "detail"
     */
    protected function getActionName(): string
    {
        return StringUtility::removeStringPostfix($this->actionMethodName, 'Action');
    }
}
