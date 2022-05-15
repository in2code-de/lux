<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Helper;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetRangeViewHelper
 */
class GetRangeViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('from', 'int', 'start', false, 0);
        $this->registerArgument('to', 'int', 'end', true);
    }

    /**
     * @return array
     */
    public function render(): array
    {
        return range($this->arguments['from'], $this->arguments['to']);
    }
}
