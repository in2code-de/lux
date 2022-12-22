<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetLastWeekNamesViewHelper extends AbstractViewHelper
{
    /**
     * Return a label string for the visitor charts on detail view
     *
     * @return string
     */
    public function render(): string
    {
        $weekNames = [];
        foreach (range(0, 6) as $week) {
            $weekNames[] = LocalizationUtility::translateByKey('datetime.week.' . $week);
        }
        $weekNames = array_reverse($weekNames);
        return implode(',', $weekNames);
    }
}
