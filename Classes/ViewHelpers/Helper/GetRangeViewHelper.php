<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Helper;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetRangeViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('from', 'int', 'start', false, 0);
        $this->registerArgument('to', 'int', 'end', true);
    }

    public function render(): array
    {
        return range($this->arguments['from'], $this->arguments['to']);
    }
}
