<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\String;

use In2code\Lux\Utility\StringUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetRandomValueViewHelper
 * Used in luxenterprise save values to table workflow action
 */
class GetRandomValueViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('length', 'int', 'String length', false, 32);
        $this->registerArgument('upper', 'bool', 'Get also characters in uppercase', false, true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return StringUtility::getRandomString($this->arguments['length'], $this->arguments['upper']);
    }
}
