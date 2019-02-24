<?php
namespace In2code\Lux\Hooks;

use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class PageLayoutHeader
 */
class PageLayoutHeader
{

    /**
     * @var string
     */
    protected $templatePathAndFile = 'EXT:lux/Resources/Private/Templates/Backend/PageOverview.html';

    /**
     * @param array $parameters
     * @param PageLayoutController $plController
     * @return string
     */
    public function render(array $parameters, PageLayoutController $plController): string
    {
        unset($parameters);
        $content = '';
        if ($this->isPageEnabled($plController)) {
            $pageIdentifier = $plController->id;
            $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
            $visitors = $visitorRepository->findByVisitedPageIdentifier($pageIdentifier);
            $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
            $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->templatePathAndFile));
            $standaloneView->assignMultiple([
                'visitors' => $visitors,
                'pageIdentifier' => $pageIdentifier
            ]);
            $content = $standaloneView->render();
        }
        return $content;
    }

    /**
     * @param PageLayoutController $plController
     * @return bool
     */
    protected function isPageEnabled(PageLayoutController $plController): bool
    {
        $row = BackendUtility::getRecord('pages', $plController->id, 'hidden');
        return $row['hidden'] !== 1;
    }
}
