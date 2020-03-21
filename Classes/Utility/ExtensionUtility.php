<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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

    /**
     * @param string $version
     * @return bool
     */
    public static function isLuxletterVersionOrHigherAvailable(string $version): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger($version) >=
            VersionNumberUtility::convertVersionNumberToInteger(self::getLuxletterVersion());
    }
}
