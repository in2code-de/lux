<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * UpperViewHelper
 */
class UpperViewHelper extends AbstractViewHelper
{
    /**
     * @return string
     */
    public function render(): string
    {
        return ucfirst($this->renderChildren());
    }
}
