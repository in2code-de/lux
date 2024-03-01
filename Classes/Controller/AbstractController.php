<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use DateTime;
use In2code\Lux\Backend\Buttons\NavigationGroupButton;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\CompanyRepository;
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
use In2code\Lux\Domain\Repository\Remote\WiredmindsRepository;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Domain\Repository\UtmRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\StringUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Module\ExtbaseModule;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Routing\RouteResult;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

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
    protected ?CompanyRepository $companyRepository = null;
    protected ?WiredmindsRepository $wiredmindsRepository = null;
    protected ?RenderingTimeService $renderingTimeService = null;
    protected ?CacheLayer $cacheLayer = null;
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ModuleTemplate $moduleTemplate;

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
        CompanyRepository $companyRepository,
        WiredmindsRepository $wiredmindsRepository,
        RenderingTimeService $renderingTimeService,
        CacheLayer $cacheLayer,
        ModuleTemplateFactory $moduleTemplateFactory
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
        $this->companyRepository = $companyRepository;
        $this->wiredmindsRepository = $wiredmindsRepository;
        $this->renderingTimeService = $renderingTimeService;
        $this->cacheLayer = $cacheLayer;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * Pass some important variables to all views
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view (Todo: Param is only needed in TYPO3 11)
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView($view)
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

    public function initializeAction()
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    /**
     * Always set a default FilterDto even if there are no filter params. In addition, remove categoryScoring with 0 to
     * avoid propertymapping exceptions
     *
     * @param int $timePeriod if anything else then default (0) is given, $filter->isPeriodSet() will be true
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
            $filter = BackendUtility::getFilterArrayFromSession($this->getActionName(), $this->getControllerName());
            if ($filter === []) {
                $filter['timePeriodDefault'] = $timePeriod;
            }
        } else {
            $filter = (array)$this->request->getArgument('filter');
            BackendUtility::saveValueToSession('filter', $this->getActionName(), $this->getControllerName(), $filter);
        }

        if (isset($filter['identified']) && $filter['identified'] === '') {
            $filter['identified'] = FilterDto::IDENTIFIED_ALL;
        }
        $this->request = $this->request->withArgument('filter', $filter);
    }

    public function resetFilterAction(string $redirectAction): ResponseInterface
    {
        BackendUtility::saveValueToSession('filter', $redirectAction, $this->getControllerName(), []);
        return $this->redirect($redirectAction);
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
     * @return string like "lux_LuxWorkflow"
     */
    protected function getRoute(): string
    {
        // Todo: Can be removed when TYPO3 11 support is dropped
        if (ConfigurationUtility::isTypo3Version11()) {
            /** @var Route $route */
            $route = $this->request->getAttribute('route');
            return $route->getOption('moduleName');
        }

        /** @var RouteResult $routeResult */
        $routeResult = $this->request->getAttribute('routing');
        /** @var ExtbaseModule $extbaseModule */
        $extbaseModule = $routeResult->getRoute()->getOption('module');
        return $extbaseModule->getIdentifier();
    }

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

    protected function defaultRendering(): ResponseInterface
    {
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    protected function addDocumentHeader(array $configuration): void
    {
        $this->addNavigationButtons($configuration);
        $this->addShortcutButton();
    }

    protected function addNavigationButtons(array $configuration): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $navigationGroupButton = GeneralUtility::makeInstance(
            NavigationGroupButton::class,
            $this->request,
            $this->getActionName(),
            $this->getControllerName(),
            $configuration,
        );
        $buttonBar->addButton($navigationGroupButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }

    protected function addShortcutButton(): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $shortCutButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $shortCutButton
            ->setRouteIdentifier($this->getRoute())
            ->setDisplayName('Shortcut')
            ->setArguments(['action' => $this->getActionName(), 'controller' => $this->getControllerName()]);
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }
}
