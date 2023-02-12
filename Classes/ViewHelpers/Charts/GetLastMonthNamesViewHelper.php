<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use DateTime;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetLastMonthNamesViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('months', 'int', 'Number of last months', true);
    }

    public function render(): string
    {
        $now = new DateTime();
        $monthNames = [
            LocalizationUtility::translateByKey('datetime.month.' . $now->format('n')),
        ];
        for ($i = 1; $i < $this->arguments['months']; $i++) {
            $month = clone $now;
            $month->modify('-' . $i . ' months');
            $monthNames[] = LocalizationUtility::translateByKey('datetime.month.' . $month->format('n'));
        }
        $monthNames = array_reverse($monthNames);
        return implode(',', $monthNames);
    }
}
