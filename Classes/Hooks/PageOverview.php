<?php

namespace In2code\Lux\Hooks;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
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

    public function __construct(
        VisitorRepository $visitorRepository,
        PagevisitRepository $pagevisitRepository,
        LinkclickRepository $linkclickRepository,
        DownloadRepository $downloadRepository,
        LogRepository $logRepository,
        RenderingTimeService $renderingTimeService
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->pagevisitRepository = $pagevisitRepository;
        $this->linkclickRepository = $linkclickRepository;
        $this->downloadRepository = $downloadRepository;
        $this->logRepository = $logRepository;
        $this->renderingTimeService = $renderingTimeService; // initialize renderingTimes
    }

    /**
     * @param ModifyPageLayoutContentEvent $event
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     * @throws DBALException
     */
    public function eventRegistration(ModifyPageLayoutContentEvent $event): void
    {
        $queryParams = $event->getRequest()->getQueryParams();
        $pageIdentifier = (int)($queryParams['id'] ?? 0);
        $event->addHeaderContent($this->renderContent($pageIdentifier));
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
     * @throws DBALException
     */
    protected function renderContent(int $pageIdentifier): string
    {
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
     */
    protected function getArguments(string $view, int $pageIdentifier, array $session): array
    {
        return [
            'pageIdentifier' => $pageIdentifier,
            'status' => $session['status'] ?? 'show',
            'view' => ucfirst($view),
            'visitors' => $this->visitorRepository->findByVisitedPageIdentifier($pageIdentifier),
        ];
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
