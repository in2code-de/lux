<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Be;

use In2code\Lux\Utility\FrontendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetControllerNameViewHelper
 */
class GetControllerNameViewHelper extends AbstractViewHelper
{

    /**
     * @return string
     */
    public function render(): string
    {
        return FrontendUtility::getModuleName();
    }
}
