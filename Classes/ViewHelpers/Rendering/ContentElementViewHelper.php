<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Rendering;

use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ContentElementViewHelper
 */
class ContentElementViewHelper extends AbstractViewHelper
{

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'tt_content.uid', true);
    }

    /**
     * Parse a content element
     *
     * @return string
     */
    public function render()
    {
        $contentObject = ObjectUtility::getObjectManager()->get(ContentObjectRenderer::class);
        $configuration = [
            'tables' => 'tt_content',
            'source' => (int)$this->arguments['uid'],
            'dontCheckPid' => 1
        ];
        return $contentObject->cObjGetSingle('RECORDS', $configuration);
    }
}
