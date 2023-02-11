<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Service\Uri\EditRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UriEditViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('tableName', 'string', 'tableName', true);
        $this->registerArgument('identifier', 'int', 'identifier', true);
        $this->registerArgument('moduleName', 'string', 'module name for return url', true);
        $this->registerArgument('addReturnUrl', 'bool', 'addReturnUrl', false, true);
    }

    public function render(): string
    {
        $editRecord = GeneralUtility::makeInstance(EditRecord::class, $this->renderingContext);
        return $editRecord->get(
            $this->arguments['tableName'],
            (int)$this->arguments['identifier'],
            (bool)$this->arguments['addReturnUrl']
        );
    }
}
