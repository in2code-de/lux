<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use In2code\Lux\Utility\FileUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetFilenameFromPathAndFilenameViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pathAndFilename', 'string', 'Like "fileadmin/whitepaper.pdf"', false);
    }

    public function render(): string
    {
        return FileUtility::getFilenameFromPathAndFilename($this->getPathAndFilename());
    }

    protected function getPathAndFilename(): string
    {
        $pathAndFilename = $this->renderChildren();
        if (!empty($this->arguments['pathAndFilename'])) {
            $pathAndFilename = $this->arguments['pathAndFilename'];
        }
        return (string)$pathAndFilename;
    }
}
