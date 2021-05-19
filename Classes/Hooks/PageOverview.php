<?php
namespace In2code\Lux\Hooks;

use In2code\Lux\Domain\Repository\VisitorRepository;
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
 * to show leads in the page module in backend
 */
class PageOverview
{
    /**
     * @var string
     */
    protected $templatePathAndFile = 'EXT:lux/Resources/Private/Templates/Backend/PageOverview.html';

    /**
     * @var null
     */
    protected $visitorRepository = null;

    /**
     * PageOverview constructor.
     * @param VisitorRepository|null $visitorRepository
     */
    public function __construct(VisitorRepository $visitorRepository = null)
    {
        $this->visitorRepository = $visitorRepository ?: GeneralUtility::makeInstance(VisitorRepository::class);
    }

    /**
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     * @throws Exception
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
            $visitors = $this->visitorRepository->findByVisitedPageIdentifier($pageIdentifier);
            $arguments = [
                'visitors' => $visitors,
                'pageIdentifier' => $pageIdentifier,
                'view' => ConfigurationUtility::getPageOverviewView(),
                'status' => $session['status'] ?: 'show'
            ];
            $arguments = $this->enrichArgumentsForLeadsView($arguments);
            $content = $this->getContent($arguments);
        }
        return $content;
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
        $standaloneView->assignMultiple($arguments);
        return $standaloneView->render();
    }

    /**
     * @param array $arguments
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function enrichArgumentsForLeadsView(array $arguments): array
    {
        if (ConfigurationUtility::getPageOverviewView() === 'analysis') {
            $arguments += [

            ];
        }
        return $arguments;
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
