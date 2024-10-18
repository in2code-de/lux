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
use In2code\Lux\Utility\StringUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Module\ExtbaseModule;
use TYPO3\CMS\Backend\Routing\RouteResult;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

abstract class AbstractController extends ActionController
{
    protected ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly VisitorRepository $visitorRepository,
        protected readonly IpinformationRepository $ipinformationRepository,
        protected readonly LogRepository $logRepository,
        protected readonly PagevisitRepository $pagevisitsRepository,
        protected readonly PageRepository $pageRepository,
        protected readonly DownloadRepository $downloadRepository,
        protected readonly NewsvisitRepository $newsvisitRepository,
        protected readonly NewsRepository $newsRepository,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly LinkclickRepository $linkclickRepository,
        protected readonly LinklistenerRepository $linklistenerRepository,
        protected readonly FingerprintRepository $fingerprintRepository,
        protected readonly SearchRepository $searchRepository,
        protected readonly UtmRepository $utmRepository,
        protected readonly CompanyRepository $companyRepository,
        protected readonly WiredmindsRepository $wiredmindsRepository,
        protected readonly RenderingTimeService $renderingTimeService,
        protected readonly CacheLayer $cacheLayer,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory
    ) {}

    /**
     * Pass some important variables to all views
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView()
    {
        $this->moduleTemplate->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
                'actionUpper' => ucfirst($this->getActionName()),
            ],
            'extensionConfiguration' => GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('lux'),
        ]);
    }

    public function initializeAction(): void
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

    public function tableSortingAction(string $sortingField, string $sortingDirection, string $redirectAction): ResponseInterface
    {
        $filter = BackendUtility::getFilterArrayFromSession($redirectAction, $this->getControllerName());
        $filter['sortingField'] = $sortingField;
        $filter['sortingDirection'] = $sortingDirection;
        return $this->redirect($redirectAction, null, null, ['filter' => $filter]);
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
        return $this->moduleTemplate->renderResponse($this->getControllerName() . '/' . ucfirst($this->getActionName()));
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
