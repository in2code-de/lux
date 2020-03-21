<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Charts;

use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetLastMonthNamesViewHelper
 */
class GetLastMonthNamesViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('months', 'int', 'Number of last months', true);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render(): string
    {
        $now = new \DateTime();
        $monthNames = [
            LocalizationUtility::translateByKey('datetime.month.' . $now->format('n'))
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
