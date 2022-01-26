<?php
namespace In2code\Lux\Hooks;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
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
     * @var RenderingTimeService
     */
    protected $renderingTimeService = null;

    /**
     * @var CacheLayer
     */
    protected $cacheLayer = null;

    /**
     * PageOverview constructor.
     */
    public function __construct()
    {
        $this->renderingTimeService = GeneralUtility::makeInstance(RenderingTimeService::class);
        $this->cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
    }

    /**
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
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
     * @throws ExceptionDbal
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
     */
    protected function getArguments(string $view, int $pageIdentifier, array $session): array
    {
        $arguments = $this->cacheLayer->getArguments(__CLASS__, __FUNCTION__, (string)$pageIdentifier);
        $arguments['view'] = $view;
        $arguments['status'] = $session['status'] ?? 'show';
        return $arguments;
    }

    /**
     * @param array $arguments
     * @return string
     */
    protected function getContent(array $arguments): string
    {
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
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
