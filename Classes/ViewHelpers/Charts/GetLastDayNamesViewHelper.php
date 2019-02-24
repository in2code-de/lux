<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetLastDayNamesViewHelper
 */
class GetLastDayNamesViewHelper extends AbstractViewHelper
{

    /**
     * Return a label string for the visitor charts
     *
     * @return string
     */
    public function render(): string
    {
        $weekdayNames = [];
        $locallangPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:datetime.weekday.';
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . 'now');
        $yesterday = new \DateTime('yesterday');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($yesterday->format('D')));
        $day3 = new \DateTime('2 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day3->format('D')));
        $day4 = new \DateTime('3 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day4->format('D')));
        $day5 = new \DateTime('4 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day5->format('D')));
        $day6 = new \DateTime('5 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day6->format('D')));
        $day7 = new \DateTime('6 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day7->format('D')));
        $day8 = new \DateTime('7 days ago');
        $weekdayNames[] = LocalizationUtility::translate($locallangPrefix . strtolower($day8->format('D')));
        $weekdayNames = array_reverse($weekdayNames);
        return implode(',', $weekdayNames);
    }
}
