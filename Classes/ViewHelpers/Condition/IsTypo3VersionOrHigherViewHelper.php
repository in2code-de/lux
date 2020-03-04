<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class IsTypo3VersionOrHigherViewHelper
 * Todo: This is needed as long as TYPO3 9 is supported
 * @noinspection PhpUnused
 */
class IsTypo3VersionOrHigherViewHelper extends AbstractConditionViewHelper
{
    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('version', 'string', 'e.g. "10.0.0"', true);
    }

    /**
     * @param null $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return self::getCurrentVersion() >= VersionNumberUtility::convertVersionNumberToInteger($arguments['version']);
    }

    /**
     * Return current TYPO3 version as integer - e.g. 10003000 (10.3.0) or 9005014 (9.5.14)
     *
     * @return int
     */
    protected static function getCurrentVersion(): int
    {
        return VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
    }
}
