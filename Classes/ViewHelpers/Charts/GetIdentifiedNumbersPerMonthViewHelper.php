<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetIdentifiedNumbersPerMonthViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('logs', 'array', '{identifiedPerMonth} variable', true);
    }

    public function render(): string
    {
        $amount = [];
        foreach ($this->arguments['logs'] as $logs) {
            $amount[] = count($logs);
        }
        return implode(',', $amount);
    }
}
