<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetIdentifiedNumbersPerMonthViewHelper
 */
class GetIdentifiedNumbersPerMonthViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('logs', 'array', '{identifiedPerMonth} variable', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $amount = [];
        foreach ($this->arguments['logs'] as $logs) {
            $amount[] = count($logs);
        }
        return implode(',', $amount);
    }
}
