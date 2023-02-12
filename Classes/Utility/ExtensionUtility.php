<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use Throwable;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ExtensionUtility
{
    public static function getLuxVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('lux');
        } catch (Throwable $exception) {
            return '';
        }
    }

    public static function getLuxenterpriseVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('luxenterprise');
        } catch (Throwable $exception) {
            return '';
        }
    }

    public static function isLuxenterpriseVersionOrHigherAvailable(string $version): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger($version) <=
            VersionNumberUtility::convertVersionNumberToInteger(self::getLuxenterpriseVersion());
    }

    public static function getLuxletterVersion(): string
    {
        try {
            return ExtensionManagementUtility::getExtensionVersion('luxletter');
        } catch (\Exception $exception) {
            return '';
        }
    }

    public static function isLuxletterVersionOrHigherAvailable(string $version): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger($version) <=
            VersionNumberUtility::convertVersionNumberToInteger(self::getLuxletterVersion());
    }
}
