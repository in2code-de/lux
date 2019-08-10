<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class ExtensionUtility
 */
class ExtensionUtility
{

    /**
     * @return string
     */
    public static function getLuxVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('lux');
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * @return string
     */
    public static function getLuxenterpriseVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('luxenterprise');
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * @return string
     */
    public static function getLuxletterVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('luxletter');
        } catch (\Exception $exception) {
            return '';
        }
    }
}
