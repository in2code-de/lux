<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsLuxenterpriseExtensionActivatedViewHelper extends AbstractConditionViewHelper
{
    protected static function evaluateCondition($arguments = null): bool
    {
        return ExtensionManagementUtility::isLoaded('luxenterprise');
    }
}
