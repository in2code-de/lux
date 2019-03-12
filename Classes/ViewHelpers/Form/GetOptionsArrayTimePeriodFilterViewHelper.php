<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Form;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetOptionsArrayTimePeriodFilterViewHelper
 */
class GetOptionsArrayTimePeriodFilterViewHelper extends AbstractViewHelper
{

    /**
     * @return array
     */
    public function render(): array
    {
        return [
            FilterDto::PERIOD_THISYEAR
                => LocalizationUtility::translateByKey('module.analysis.filter.timePeriod.0'),
            FilterDto::PERIOD_THISMONTH
                => LocalizationUtility::translateByKey('module.analysis.filter.timePeriod.1'),
            FilterDto::PERIOD_LASTMONTH
                => LocalizationUtility::translateByKey('module.analysis.filter.timePeriod.2')
        ];
    }
}
