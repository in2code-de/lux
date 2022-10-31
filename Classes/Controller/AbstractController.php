<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use DateTime;
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
use In2code\Lux\Domain\Repository\UtmRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

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
     * @var UtmRepository
     */
    protected $utmRepository = null;

    /**
     * @var RenderingTimeService
     */
    protected $renderingTimeService = null;

    /**
     * @var CacheLayer
     */
    protected $cacheLayer = null;

    /**
     * AbstractController constructor.
     * @param VisitorRepository $visitorRepository
     * @param IpinformationRepository $ipinformationRepository
     * @param LogRepository $logRepository
     * @param PagevisitRepository $pagevisitsRepository
     * @param PageRepository $pageRepository
     * @param DownloadRepository $downloadRepository
     * @param NewsvisitRepository $newsvisitRepository
     * @param NewsRepository $newsRepository
     * @param CategoryRepository $categoryRepository
     * @param LinkclickRepository $linkclickRepository
     * @param LinklistenerRepository $linklistenerRepository
     * @param FingerprintRepository $fingerprintRepository
     * @param SearchRepository $searchRepository
     * @param RenderingTimeService $renderingTimeService to initialize renderingTimes
     * @param CacheLayer $cacheLayer
     */
    public function __construct(
        VisitorRepository $visitorRepository,
        IpinformationRepository $ipinformationRepository,
        LogRepository $logRepository,
        PagevisitRepository $pagevisitsRepository,
        PageRepository $pageRepository,
        DownloadRepository $downloadRepository,
        NewsvisitRepository $newsvisitRepository,
        NewsRepository $newsRepository,
        CategoryRepository $categoryRepository,
        LinkclickRepository $linkclickRepository,
        LinklistenerRepository $linklistenerRepository,
        FingerprintRepository $fingerprintRepository,
        SearchRepository $searchRepository,
        UtmRepository $utmRepository,
        RenderingTimeService $renderingTimeService,
        CacheLayer $cacheLayer
    ) {
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
        $this->utmRepository = $utmRepository;
        $this->renderingTimeService = $renderingTimeService;
        $this->cacheLayer = $cacheLayer;
    }

    /**
     * Pass some important variables to all views
     *
     * @param ViewInterface $view
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
                'actionUpper' => ucfirst($this->getActionName()),
            ],
            'extensionConfiguration' => GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('lux'),
        ]);
    }

    /**
     * Always set a default FilterDto even if there are no filter params. In addition remove categoryScoring with 0 to
     * avoid propertymapping exceptions
     *
     * @param int $timePeriod
     * @return void
     * @throws NoSuchArgumentException
     */
    protected function setFilter(int $timePeriod = FilterDto::PERIOD_DEFAULT): void
    {
        $filterArgument = $this->arguments->getArgument('filter');
        $filterPropMapping = $filterArgument->getPropertyMappingConfiguration();
        $filterPropMapping->allowAllProperties();

        // Save to session
        if ($this->request->hasArgument('filter') === false) {
            $filter = BackendUtility::getSessionValue('filter', $this->getActionName(), $this->getControllerName());
            $filter = array_merge(['timePeriod' => $timePeriod], $filter);
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
        $classParts = explode('\\', static::class);
        $name = end($classParts);
        return StringUtility::removeStringPostfix($name, 'Controller');
    }

    /**
     * @return string like "list" or "detail"
     */
    protected function getActionName(): string
    {
        return StringUtility::removeStringPostfix($this->actionMethodName, 'Action');
    }

    /**
     * @param string|null $csv
     * @param string $filename
     * @return ResponseInterface
     */
    protected function csvResponse(string $csv = null, string $filename = ''): ResponseInterface
    {
        if ($filename === '') {
            $date = new DateTime();
            $filename = $this->getControllerName() . '_' . $this->getActionName()
                . '_' . $date->format('Y-m-d') . '.csv';
        }

        // Todo: Remove when TYPO3 10 is dropped
        if (ConfigurationUtility::isTypo3Version11() === false) {
            $this->response->setHeader('Content-Type', 'text/x-csv');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->sendHeaders();
            echo $this->view->render();
            exit;
        }

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/x-csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withBody($this->streamFactory->createStream($csv ?? $this->view->render()));
    }
}
