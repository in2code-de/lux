<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Rendering;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ContentElementViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'tt_content.uid', true);
    }

    public function render(): string
    {
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $configuration = [
            'tables' => 'tt_content',
            'source' => (int)$this->arguments['uid'],
            'dontCheckPid' => 1,
        ];
        return $contentObject->cObjGetSingle('RECORDS', $configuration);
    }
}
