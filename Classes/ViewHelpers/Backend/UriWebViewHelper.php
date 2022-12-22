<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Service\Uri\WebRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UriWebViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('identifier', 'int', 'identifier', true);
    }

    public function render(): string
    {
        $editRecord = GeneralUtility::makeInstance(WebRecord::class, '');
        return $editRecord->get('', (int)$this->arguments['identifier']);
    }
}
