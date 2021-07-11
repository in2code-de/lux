<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class PercentViewHelper
 */
class PercentViewHelper extends AbstractViewHelper
{
    /**
     * Show a percent value
     *      0.5 => "50%"
     *
     * @return string
     */
    public function render(): string
    {
        $number = $this->renderChildren();
        $number = number_format($number, 3);
        return ($number * 100) . '%';
    }
}
