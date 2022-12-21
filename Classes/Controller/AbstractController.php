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
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ActionController
{
    protected ?VisitorRepository $visitorRepository = null;
    protected ?IpinformationRepository $ipinformationRepository = null;
    protected ?LogRepository $logRepository = null;
    protected ?PagevisitRepository $pagevisitsRepository = null;
    protected ?PageRepository $pageRepository = null;
    protected ?DownloadRepository $downloadRepository = null;
    protected ?NewsvisitRepository $newsvisitRepository = null;
    protected ?NewsRepository $newsRepository = null;
    protected ?CategoryRepository $categoryRepository = null;
    protected ?LinkclickRepository $linkclickRepository = null;
    protected ?LinklistenerRepository $linklistenerRepository = null;
    protected ?FingerprintRepository $fingerprintRepository = null;
    protected ?SearchRepository $searchRepository = null;
    protected ?UtmRepository $utmRepository = null;
    protected ?RenderingTimeService $renderingTimeService = null;
    protected ?CacheLayer $cacheLayer = null;

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
     * @param UtmRepository $utmRepository
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
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView()
    {
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
        $this->request = $this->request->withArgument('filter', $filter);
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

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/x-csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withBody($this->streamFactory->createStream($csv ?? $this->view->render()));
    }
}
