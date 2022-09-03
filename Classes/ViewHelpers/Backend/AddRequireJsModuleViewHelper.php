<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AddRequireJsModuleViewHelper
 */
class AddRequireJsModuleViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('moduleName', 'string', 'require JS module name', true);
    }

    /**
     * @return void
     */
    public function render(): void
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule($this->arguments['moduleName']);
    }
}
