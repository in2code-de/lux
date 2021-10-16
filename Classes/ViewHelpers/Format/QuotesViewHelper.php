<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class QuotesViewHelper
 */
class QuotesViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return string
     */
    public function render(): string
    {
        return '"' . str_replace('"', '\"', (string)$this->renderChildren()) . '"';
    }
}
