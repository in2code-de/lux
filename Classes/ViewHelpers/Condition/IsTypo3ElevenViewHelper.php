<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Condition;

use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IsTypo3ElevenViewHelper
 * @noinspection PhpUnused
 * Todo: Can be removed when TYPO3 11 support is dropped
 */
class IsTypo3ElevenViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        unset($arguments);
        return ConfigurationUtility::isTypo3Version11();
    }
}
